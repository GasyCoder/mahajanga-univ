<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email','name','status','token','subscribed_at','unsubscribed_at'
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}