<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public'); // public/private
            $table->string('path');                    // ex: uploads/2026/01/file.jpg
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->string('alt')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['disk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

