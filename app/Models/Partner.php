<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','type','website_url','country','description','is_featured','logo_id'
    ];

    protected $casts = ['is_featured' => 'boolean'];

    public function logo() { return $this->belongsTo(Media::class, 'logo_id'); }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}