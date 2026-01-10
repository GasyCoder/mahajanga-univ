<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action', 100);          // ex: newsletter.campaign.send
            $table->string('entity_type', 150)->nullable(); // ex: NewsletterCampaign
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->json('meta')->nullable();

            $table->string('ip', 64)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['action']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
