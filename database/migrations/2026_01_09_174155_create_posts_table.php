<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content_html'); // contenu principal

            $table->string('status')->default('draft'); // draft|pending|published|archived
            $table->timestamp('published_at')->nullable();

            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            $table->foreignId('cover_image_id')->nullable()->constrained('media')->nullOnDelete();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();

            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
