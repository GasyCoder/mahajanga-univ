<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign,
        public NewsletterSubscriber $subscriber
    ) {}

    public function build()
    {
        $unsubscribeUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL')), '/')
            . '/newsletter/unsubscribe?token=' . $this->subscriber->token;

        return $this->subject($this->campaign->subject)
            ->view('emails.newsletter.campaign')
            ->with([
                'campaign' => $this->campaign,
                'subscriber' => $this->subscriber,
                'unsubscribeUrl' => $unsubscribeUrl,
            ]);
    }
}