<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class Slugger
{
    /**
     * @param class-string<Model> $modelClass
     */
    public static function uniqueSlug(string $modelClass, string $title, string $column = 'slug'): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while ($modelClass::query()->where($column, $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    /**
     * Unique slug for update (ignore current record id).
     *
     * @param class-string<Model> $modelClass
     */
    public static function uniqueSlugForUpdate(string $modelClass, int $ignoreId, string $title, string $column = 'slug'): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while ($modelClass::query()->where($column, $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
