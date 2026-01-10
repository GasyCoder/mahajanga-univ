<?php

namespace App\Console\Commands;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSend;
use Illuminate\Console\Command;

class FinalizeNewsletterCampaignsCommand extends Command
{
    protected $signature = 'newsletter:finalize';
    protected $description = 'Finalize newsletter campaigns when all sends are processed';

    public function handle(): int
    {
        $campaigns = NewsletterCampaign::query()
            ->where('status', 'sending')
            ->orderBy('id')
            ->get();

        $finalized = 0;

        foreach ($campaigns as $c) {
            $queued = NewsletterSend::query()
                ->where('newsletter_campaign_id', $c->id)
                ->where('status', 'queued')
                ->count();

            if ($queued === 0) {
                $c->update(['status' => 'sent', 'sent_at' => now()]);
                $finalized++;
            }
        }

        $this->info("Finalized: {$finalized}");
        return self::SUCCESS;
    }
}