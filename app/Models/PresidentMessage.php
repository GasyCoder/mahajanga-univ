<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresidentMessage extends Model
{
    protected $fillable = [
        'title',
        'intro',
        'content',
        'president_name',
        'president_title',
        'mandate_period',
        'photo_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    /**
     * Get the currently active message
     */
    public static function active(): ?self
    {
        return static::with('photo')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set this message as active (deactivate others)
     */
    public function activate(): void
    {
        static::where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}
