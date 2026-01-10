<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content')->nullable(); // HTML content
            
            // Page type: historique, organisation, textes, organigramme
            $table->string('page_type');
            
            // Display
            $table->integer('order')->default(0);
            $table->boolean('is_published')->default(false);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Optional featured image
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_pages');
    }
};
