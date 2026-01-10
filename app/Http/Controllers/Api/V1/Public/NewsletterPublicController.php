<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\NewsletterSubscribeRequest;
use App\Http\Requests\Public\NewsletterUnsubscribeRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Str;

class NewsletterPublicController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request)
    {
        $data = $request->validated();

        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $data['email']]);
        $subscriber->name = $data['name'] ?? $subscriber->name;
        $subscriber->status = 'active';
        $subscriber->token = $subscriber->token ?: Str::random(64);
        $subscriber->subscribed_at = now();
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        return response()->json(['data' => true]);
    }

    public function unsubscribe(NewsletterUnsubscribeRequest $request)
    {
        $token = $request->validated()['token'];

        $subscriber = NewsletterSubscriber::query()->where('token', $token)->firstOrFail();
        $subscriber->status = 'unsubscribed';
        $subscriber->unsubscribed_at = now();
        $subscriber->save();

        return response()->json(['data' => true]);
    }
}
