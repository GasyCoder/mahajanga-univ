<?php

namespace Database\Factories;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsletterSubscriberFactory extends Factory
{
    protected $model = NewsletterSubscriber::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->name(),
            'status' => 'active',
            'token' => Str::random(64),
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ];
    }

    public function unsubscribed(): self
    {
        return $this->state(fn() => [
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }
}