<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsletterCampaign extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_SENDING = 'sending';
    const STATUS_SENT = 'sent';

    protected $fillable = [
        'subject', 'content_html', 'content_text', 'status',
        'post_id', 'created_by', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'post_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function sends()
    {
        return $this->hasMany(NewsletterSend::class, 'newsletter_campaign_id');
    }
}
