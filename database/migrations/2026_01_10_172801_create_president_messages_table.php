<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('president_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('intro')->nullable();
            $table->longText('content');
            
            // President info
            $table->string('president_name');
            $table->string('president_title')->default('Président');
            $table->string('mandate_period')->nullable(); // e.g. "2020-2025"
            
            // Photo
            $table->foreignId('photo_id')->nullable()->constrained('media')->nullOnDelete();
            
            // Only one can be active at a time
            $table->boolean('is_active')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('president_messages');
    }
};
