<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'page_type',
        'order',
        'is_published',
        'meta_title',
        'meta_description',
        'featured_image_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'order' => 'integer',
    ];

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    // Scope for published pages
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // Scope by page type
    public function scopeOfType($query, string $type)
    {
        return $query->where('page_type', $type);
    }
}
