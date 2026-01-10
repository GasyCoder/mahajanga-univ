<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsletterSubscriberRequest;
use App\Http\Resources\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterSubscriberAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureRole($request);

        $q = NewsletterSubscriber::query()->orderByDesc('id');

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $q->where(fn($w) => $w
                ->where('email', 'like', "%$term%")
                ->orWhere('name', 'like', "%$term%")
            );
        }

        $per = min((int)$request->get('per_page', 50), 200);

        return NewsletterSubscriberResource::collection($q->paginate($per));
    }

    public function store(StoreNewsletterSubscriberRequest $request)
    {
        $data = $request->validated();

        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $data['email']]);

        $subscriber->name = $data['name'] ?? $subscriber->name;
        $subscriber->status = $data['status'] ?? 'active';
        $subscriber->token = $subscriber->token ?: Str::random(64);

        if ($subscriber->status === 'active') {
            $subscriber->subscribed_at = $subscriber->subscribed_at ?: now();
            $subscriber->unsubscribed_at = null;
        }

        if ($subscriber->status === 'unsubscribed') {
            $subscriber->unsubscribed_at = $subscriber->unsubscribed_at ?: now();
        }

        $subscriber->save();

        Audit::log($request, 'newsletter.subscriber.upsert', 'NewsletterSubscriber', $subscriber->id, [
            'email' => $subscriber->email,
            'status' => $subscriber->status,
        ]);

        return new NewsletterSubscriberResource($subscriber);
    }

    public function update(Request $request, int $id)
    {
        $this->ensureRole($request);

        $subscriber = NewsletterSubscriber::findOrFail($id);

        $data = $request->validate([
            'name' => ['nullable','string','max:255'],
            'status' => ['required','in:active,unsubscribed,pending'],
        ]);

        $subscriber->name = $data['name'] ?? $subscriber->name;
        $subscriber->status = $data['status'];

        if ($data['status'] === 'active') {
            $subscriber->subscribed_at = $subscriber->subscribed_at ?: now();
            $subscriber->unsubscribed_at = null;
        }

        if ($data['status'] === 'unsubscribed') {
            $subscriber->unsubscribed_at = now();
        }

        $subscriber->save();

        Audit::log($request, 'newsletter.subscriber.update', 'NewsletterSubscriber', $subscriber->id, [
            'email' => $subscriber->email,
            'status' => $subscriber->status,
        ]);

        return new NewsletterSubscriberResource($subscriber);
    }

    public function destroy(Request $request, int $id)
    {
        $this->ensureRole($request);

        $subscriber = NewsletterSubscriber::findOrFail($id);

        Audit::log($request, 'newsletter.subscriber.delete', 'NewsletterSubscriber', $subscriber->id, [
            'email' => $subscriber->email,
        ]);

        $subscriber->delete();

        return response()->json(['data' => true]);
    }

    private function ensureRole(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur']), 403);
    }
}
