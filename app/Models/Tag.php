<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type', 'color'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
