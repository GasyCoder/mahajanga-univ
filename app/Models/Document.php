<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','slug','description','status','published_at',
        'document_category_id','file_id','download_count',
        'created_by','validated_by','validated_at',
        'is_public'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'validated_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    public function category() { return $this->belongsTo(DocumentCategory::class, 'document_category_id'); }
    public function file() { return $this->belongsTo(Media::class, 'file_id'); }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function validator() { return $this->belongsTo(User::class, 'validated_by'); }
}
