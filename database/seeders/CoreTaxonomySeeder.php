<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Tag;

class CoreTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Communiqués', 'slug' => 'communiques', 'type' => 'posts'],
            ['name' => 'Vie universitaire', 'slug' => 'vie-universitaire', 'type' => 'posts'],
            ['name' => 'Recherche', 'slug' => 'recherche', 'type' => 'posts'],
            ['name' => 'Événements', 'slug' => 'evenements', 'type' => 'posts'],
        ];

        foreach ($categories as $c) {
            Category::firstOrCreate(['slug' => $c['slug']], $c);
        }

        $tags = [
            ['name' => 'Admission', 'slug' => 'admission', 'type' => 'posts'],
            ['name' => 'Concours', 'slug' => 'concours', 'type' => 'posts'],
            ['name' => 'Partenariat', 'slug' => 'partenariat', 'type' => 'posts'],
        ];

        foreach ($tags as $t) {
            Tag::firstOrCreate(['slug' => $t['slug']], $t);
        }
    }
}
