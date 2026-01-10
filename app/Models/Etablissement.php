<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Etablissement extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'acronym',
        'description',
        'director_name',
        'director_title',
        'address',
        'phone',
        'email',
        'website',
        'facebook',
        'twitter',
        'linkedin',
        'logo_id',
        'cover_image_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_id');
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_image_id');
    }
}
