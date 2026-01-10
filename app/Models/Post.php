<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title','slug','excerpt','content_html','status','published_at',
        'author_id','validated_by','validated_at','cover_image_id',
        'is_featured','is_pinned','seo_title','seo_description'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'validated_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function validator() { return $this->belongsTo(User::class, 'validated_by'); }

    public function coverImage() { return $this->belongsTo(Media::class, 'cover_image_id'); }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_post');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function gallery()
    {
        return $this->belongsToMany(Media::class, 'post_media')
            ->withPivot(['position','caption'])
            ->orderBy('post_media.position');
    }
}
