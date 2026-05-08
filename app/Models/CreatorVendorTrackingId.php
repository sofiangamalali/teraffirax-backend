<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorVendorTrackingId extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'vendor_id',
        'tracking_id',
    ];

    /**
     * Get the creator that owns the tracking ID.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    /**
     * Get the vendor that owns the tracking ID.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
