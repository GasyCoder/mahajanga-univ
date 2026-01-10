<?php

namespace Database\Factories;

use App\Models\NewsletterCampaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterCampaignFactory extends Factory
{
    protected $model = NewsletterCampaign::class;

    public function definition(): array
    {
        return [
            'subject' => 'News ' . $this->faker->words(3, true),
            'content_html' => '<p>Test content</p>',
            'content_text' => 'Test content',
            'status' => 'draft',
            'post_id' => null,
            'created_by' => User::factory(),
            'sent_at' => null,
        ];
    }

    public function sending(): self
    {
        return $this->state(fn() => ['status' => 'sending']);
    }

    public function sent(): self
    {
        return $this->state(fn() => ['status' => 'sent', 'sent_at' => now()]);
    }
}