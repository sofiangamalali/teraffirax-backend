<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorVendorTracking extends Model
{
    use HasFactory;

    protected $table = 'creator_vendor_tracking';

    protected $fillable = [
        'creator_id',
        'vendor_id',
        'track_id',
        'is_active',
        'platform_commission_percentage',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'platform_commission_percentage' => 'float',
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
