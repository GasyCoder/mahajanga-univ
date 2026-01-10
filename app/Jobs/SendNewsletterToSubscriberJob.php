<?php

namespace App\Jobs;

use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterSend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterToSubscriberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $sendId) {}

    public function handle(): void
    {
        $send = NewsletterSend::with(['campaign', 'subscriber'])->find($this->sendId);

        if (!$send) return;

        // Si déjà traité, on sort (idempotence)
        if ($send->status !== 'queued') return;

        $campaign = $send->campaign;
        $subscriber = $send->subscriber;

        // Si subscriber n'est plus actif, on marque failed (ou on supprime)
        if (!$subscriber || $subscriber->status !== 'active') {
            $send->update([
                'status' => 'failed',
                'error' => 'Subscriber not active or missing.',
            ]);
            return;
        }

        try {
            Mail::to($subscriber->email)->send(new NewsletterCampaignMail($campaign, $subscriber));

            $send->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error' => null,
            ]);

            // Option: si tout envoyé, marquer campaign sent via une tâche séparée (voir note plus bas)

        } catch (\Throwable $e) {
            $send->update([
                'status' => 'failed',
                'error' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            // relancer si vous voulez
            throw $e;
        }
    }
}