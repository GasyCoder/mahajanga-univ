<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id','action','entity_type','entity_id','meta','ip','user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
        'actor_id' => 'integer',
        'entity_id' => 'integer',
    ];
}