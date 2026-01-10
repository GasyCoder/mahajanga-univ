<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('string'); // string, text, boolean, json, image
            $table->string('group')->default('general'); // general, seo, social, maintenance
            $table->timestamps();
        });

        // Insert default settings
        $defaults = [
            // General
            ['key' => 'site_name', 'value' => 'Université de Mahajanga', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Site officiel de l\'Université de Mahajanga', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'contact@umahajanga.mg', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_address', 'value' => 'Mahajanga, Madagascar', 'type' => 'text', 'group' => 'general'],
            ['key' => 'logo_id', 'value' => null, 'type' => 'image', 'group' => 'general'],
            ['key' => 'favicon_id', 'value' => null, 'type' => 'image', 'group' => 'general'],
            
            // SEO
            ['key' => 'meta_title', 'value' => 'Université de Mahajanga - Madagascar', 'type' => 'string', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Site officiel de l\'Université de Mahajanga', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'meta_keywords', 'value' => 'université, mahajanga, madagascar, enseignement supérieur', 'type' => 'text', 'group' => 'seo'],
            
            // Social
            ['key' => 'facebook_url', 'value' => '', 'type' => 'string', 'group' => 'social'],
            ['key' => 'twitter_url', 'value' => '', 'type' => 'string', 'group' => 'social'],
            ['key' => 'linkedin_url', 'value' => '', 'type' => 'string', 'group' => 'social'],
            ['key' => 'youtube_url', 'value' => '', 'type' => 'string', 'group' => 'social'],
            
            // Maintenance
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'maintenance'],
            ['key' => 'maintenance_message', 'value' => 'Le site est en maintenance. Veuillez réessayer plus tard.', 'type' => 'text', 'group' => 'maintenance'],
        ];

        foreach ($defaults as $setting) {
            \DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
