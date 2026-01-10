<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsletterCampaignRequest;
use App\Http\Resources\NewsletterCampaignResource;
use App\Jobs\SendNewsletterToSubscriberJob;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use App\Models\NewsletterSubscriber;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsletterCampaignAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureRoleAny($request);

        $q = NewsletterCampaign::query()
            ->withCount('sends')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        } elseif (!$request->boolean('include_archived')) {
            // By default, exclude archived campaigns
            $q->where('status', '!=', 'archived');
        }

        $per = min((int)$request->get('per_page', 20), 100);

        return NewsletterCampaignResource::collection($q->paginate($per));
    }

    public function show(Request $request, int $id)
    {
        $this->ensureRoleAny($request);

        $campaign = NewsletterCampaign::findOrFail($id);

        // include content with ?include_content=1
        return new NewsletterCampaignResource($campaign);
    }

    public function store(StoreNewsletterCampaignRequest $request)
    {
        $data = $request->validated();

        $campaign = NewsletterCampaign::create([
            'subject' => $data['subject'],
            'content_html' => $data['content_html'],
            'content_text' => $data['content_text'] ?? null,
            'status' => 'draft',
            'post_id' => $data['post_id'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return new NewsletterCampaignResource($campaign);
    }

    /**
     * POST /v1/admin/newsletter/campaigns/{id}/send
     * - verrouille le statut (draft -> sending)
     * - crée newsletter_sends (queued)
     * - dispatch les jobs
     */
    public function send(Request $request, int $id)
    {
        $this->ensureRoleSend($request);

        $campaign = NewsletterCampaign::findOrFail($id);

        // Anti double-send strict
        $updated = NewsletterCampaign::query()
            ->where('id', $campaign->id)
            ->where('status', 'draft')
            ->update(['status' => 'sending']);

        if ($updated === 0) {
            return response()->json([
                'message' => 'Campaign must be draft to send.',
                'code' => 'CAMPAIGN_NOT_DRAFT',
            ], 409);
        }

        DB::transaction(function () use ($campaign) {

            NewsletterSubscriber::query()
                ->where('status', 'active')
                ->select(['id'])
                ->orderBy('id')
                ->chunkById(500, function ($subs) use ($campaign) {
                    $rows = [];
                    foreach ($subs as $s) {
                        $rows[] = [
                            'newsletter_campaign_id' => $campaign->id,
                            'newsletter_subscriber_id' => $s->id,
                            'status' => 'queued',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    NewsletterSend::query()->insertOrIgnore($rows);
                });
        });

        // Dispatch jobs
        NewsletterSend::query()
            ->where('newsletter_campaign_id', $campaign->id)
            ->where('status', 'queued')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(500, function ($sends) {
                foreach ($sends as $send) {
                    \App\Jobs\SendNewsletterToSubscriberJob::dispatch($send->id);
                }
            });

        Audit::log($request, 'newsletter.campaign.send', 'NewsletterCampaign', $campaign->id, [
            'campaign_id' => $campaign->id,
        ]);

        return response()->json(['data' => true]);
    }

    /**
     * GET /v1/admin/newsletter/campaigns/{id}/stats
     */
    public function stats(Request $request, int $id)
    {
        $this->ensureRoleAny($request);

        $campaign = NewsletterCampaign::findOrFail($id);

        $total = NewsletterSend::query()
            ->where('newsletter_campaign_id', $campaign->id)
            ->count();

        $byStatus = NewsletterSend::query()
            ->where('newsletter_campaign_id', $campaign->id)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $queued = (int) ($byStatus['queued'] ?? 0);
        $sent   = (int) ($byStatus['sent'] ?? 0);
        $failed = (int) ($byStatus['failed'] ?? 0);

        return response()->json([
            'data' => [
                'campaign' => [
                    'id' => $campaign->id,
                    'status' => $campaign->status,
                    'sent_at' => $campaign->sent_at?->toISOString(),
                ],
                'counts' => [
                    'total' => $total,
                    'queued' => $queued,
                    'sent' => $sent,
                    'failed' => $failed,
                ],
                'is_done' => ($total > 0 && $queued === 0),
            ],
        ]);
    }

    /**
     * POST /v1/admin/newsletter/campaigns/{id}/finalize
     * Marque status=sent si queued=0 et status=sending
     */
    public function finalize(Request $request, int $id)
    {
        $this->ensureRoleSend($request);

        $campaign = NewsletterCampaign::findOrFail($id);

        if ($campaign->status !== 'sending') {
            return response()->json([
                'message' => 'Campaign must be sending to finalize.',
                'code' => 'CAMPAIGN_NOT_SENDING',
            ], 409);
        }

        $queued = NewsletterSend::query()
            ->where('newsletter_campaign_id', $campaign->id)
            ->where('status', 'queued')
            ->count();

        if ($queued > 0) {
            return response()->json([
                'message' => 'Campaign still has queued sends.',
                'code' => 'CAMPAIGN_NOT_FINISHED',
                'data' => ['queued' => $queued],
            ], 409);
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Audit::log($request, 'newsletter.campaign.finalize', 'NewsletterCampaign', $campaign->id, [
            'campaign_id' => $campaign->id,
        ]);

        return response()->json(['data' => true]);
    }

    public function update(Request $request, int $id)
    {
        $this->ensureRoleAny($request);

        $campaign = NewsletterCampaign::findOrFail($id);

        // Only allow editing drafts
        if ($campaign->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft campaigns can be edited.',
                'code' => 'CAMPAIGN_NOT_EDITABLE',
            ], 409);
        }

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'content_html' => ['required', 'string'],
            'content_text' => ['nullable', 'string'],
        ]);

        $campaign->update($data);

        Audit::log($request, 'newsletter.campaign.update', 'NewsletterCampaign', $campaign->id, [
            'subject' => $campaign->subject,
        ]);

        return new NewsletterCampaignResource($campaign);
    }

    public function destroy(Request $request, int $id)
    {
        $this->ensureRoleSend($request);

        $campaign = NewsletterCampaign::findOrFail($id);

        // Only allow deleting drafts and archived
        if (!in_array($campaign->status, ['draft', 'archived'])) {
            return response()->json([
                'message' => 'Only draft or archived campaigns can be deleted.',
                'code' => 'CAMPAIGN_NOT_DELETABLE',
            ], 409);
        }

        Audit::log($request, 'newsletter.campaign.delete', 'NewsletterCampaign', $campaign->id, [
            'subject' => $campaign->subject,
        ]);

        $campaign->delete();

        return response()->json(['data' => true]);
    }

    /**
     * POST /v1/admin/newsletter/campaigns/{id}/archive
     * Archive a campaign (draft, sending, sent -> archived)
     */
    public function archive(Request $request, int $id)
    {
        $this->ensureRoleSend($request);

        $campaign = NewsletterCampaign::findOrFail($id);

        if ($campaign->status === 'archived') {
            return response()->json([
                'message' => 'Campaign is already archived.',
                'code' => 'ALREADY_ARCHIVED',
            ], 409);
        }

        // Store original status for potential restore
        $originalStatus = $campaign->status;
        
        $campaign->update([
            'status' => 'archived',
        ]);

        Audit::log($request, 'newsletter.campaign.archive', 'NewsletterCampaign', $campaign->id, [
            'subject' => $campaign->subject,
            'previous_status' => $originalStatus,
        ]);

        return new NewsletterCampaignResource($campaign);
    }

    /**
     * POST /v1/admin/newsletter/campaigns/{id}/restore
     * Restore an archived campaign back to draft
     */
    public function restore(Request $request, int $id)
    {
        $this->ensureRoleSend($request);

        $campaign = NewsletterCampaign::findOrFail($id);

        if ($campaign->status !== 'archived') {
            return response()->json([
                'message' => 'Only archived campaigns can be restored.',
                'code' => 'NOT_ARCHIVED',
            ], 409);
        }

        $campaign->update([
            'status' => 'draft',
        ]);

        Audit::log($request, 'newsletter.campaign.restore', 'NewsletterCampaign', $campaign->id, [
            'subject' => $campaign->subject,
        ]);

        return new NewsletterCampaignResource($campaign);
    }


    private function ensureRoleAny(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur','Redacteur']), 403);
    }

    private function ensureRoleSend(Request $request): void
    {
        // Envoi : uniquement Validateur / SuperAdmin
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur']), 403);
    }
}
