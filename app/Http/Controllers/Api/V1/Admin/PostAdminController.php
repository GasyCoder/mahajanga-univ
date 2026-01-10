<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\Slugger;
use Illuminate\Http\Request;
use App\Jobs\SendNewsletterToSubscriberJob;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\DB;

class PostAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Post::class);

        $q = Post::query()->with(['coverImage','categories','tags','author']);

        if ($request->filled('status')) $q->where('status', $request->string('status'));
        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $q->where(fn($w) => $w->where('title','like',"%$term%")->orWhere('excerpt','like',"%$term%"));
        }

        $per = min((int) $request->get('per_page', 15), 50);

        return PostResource::collection($q->orderByDesc('id')->paginate($per));
    }

    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        $post = Post::create([
            'title' => $data['title'],
            'slug' => Slugger::uniqueSlug(Post::class, $data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content_html' => $data['content_html'],
            'status' => $data['status'] ?? 'draft',
            'author_id' => $request->user()->id,
            'cover_image_id' => $data['cover_image_id'] ?? null,
            'is_featured' => (bool)($data['is_featured'] ?? false),
            'is_pinned' => (bool)($data['is_pinned'] ?? false),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
        ]);

        $post->categories()->sync($data['category_ids'] ?? []);
        $post->tags()->sync($data['tag_ids'] ?? []);

        if (!empty($data['gallery'])) {
            $sync = [];
            foreach ($data['gallery'] as $item) {
                $sync[$item['media_id']] = [
                    'position' => $item['position'] ?? 0,
                    'caption' => $item['caption'] ?? null,
                ];
            }
            $post->gallery()->sync($sync);
        }

        if (($data['notify_subscribers'] ?? false) && $post->status === 'published') {
            $this->sendNewsletterForPost($post, $request->user());
        }

        return new PostResource($post->load(['coverImage','categories','tags','gallery','author']));
    }

    public function show(int $id)
    {
        $this->authorize('viewAny', Post::class);

        $post = Post::with(['coverImage','categories','tags','gallery','author'])->findOrFail($id);
        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, int $id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('update', $post);

        $data = $request->validated();

        $post->fill([
            'title' => $data['title'] ?? $post->title,
            'excerpt' => $data['excerpt'] ?? $post->excerpt,
            'content_html' => $data['content_html'] ?? $post->content_html,
            'cover_image_id' => $data['cover_image_id'] ?? $post->cover_image_id,
            'is_featured' => (bool)($data['is_featured'] ?? $post->is_featured),
            'is_pinned' => (bool)($data['is_pinned'] ?? $post->is_pinned),
            'seo_title' => $data['seo_title'] ?? $post->seo_title,
            'seo_description' => $data['seo_description'] ?? $post->seo_description,
        ])->save();

        if (array_key_exists('category_ids', $data)) $post->categories()->sync($data['category_ids'] ?? []);
        if (array_key_exists('tag_ids', $data)) $post->tags()->sync($data['tag_ids'] ?? []);

        if (array_key_exists('gallery', $data)) {
            $sync = [];
            foreach (($data['gallery'] ?? []) as $item) {
                $sync[$item['media_id']] = [
                    'position' => $item['position'] ?? 0,
                    'caption' => $item['caption'] ?? null,
                ];
            }
            $post->gallery()->sync($sync);
        }

        return new PostResource($post->load(['coverImage','categories','tags','gallery','author']));
    }

    public function destroy(int $id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('delete', $post);

        $post->delete();
        return response()->json(['data' => true]);
    }

    public function submit(int $id, Request $request)
    {
        $post = Post::findOrFail($id);
        $this->authorize('submit', $post);

        $post->update(['status' => 'pending']);
        return new PostResource($post);
    }

    public function approve(int $id, Request $request)
    {
        $post = Post::findOrFail($id);
        $this->authorize('approve', $post);

        $post->update([
            'status' => 'published',
            'published_at' => now(),
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
        ]);

        return new PostResource($post);
    }

    public function reject(int $id, Request $request)
    {
        $post = Post::findOrFail($id);
        $this->authorize('reject', $post);

        $post->update([
            'status' => 'draft',
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
        ]);

        return new PostResource($post);
    }

    public function archive(int $id, Request $request)
    {
        $post = Post::findOrFail($id);
        $this->authorize('archive', $post);

        $post->update(['status' => 'archived']);
        return new PostResource($post);
    }

    private function sendNewsletterForPost(Post $post, $user)
    {
        $campaign = NewsletterCampaign::create([
            'subject' => $post->title,
            'content_html' => $post->content_html,
            'status' => 'draft',
            'post_id' => $post->id,
            'created_by' => $user->id,
        ]);

        $campaign->update(['status' => 'sending']);

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

        NewsletterSend::query()
            ->where('newsletter_campaign_id', $campaign->id)
            ->where('status', 'queued')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(500, function ($sends) {
                foreach ($sends as $send) {
                    SendNewsletterToSubscriberJob::dispatch($send->id);
                }
            });
    }
}
