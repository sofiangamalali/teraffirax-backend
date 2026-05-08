<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'slug',
        'redirect_url',
        'clicks_count',
    ];

    /**
     * Get the creator that owns the tracking link.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    /**
     * Increment the clicks count for this link.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }
}
