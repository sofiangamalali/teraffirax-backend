<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasLogo;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, HasLogo;

    protected $fillable = [
        'name',
        'country_id',
        'domain',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the products for the vendor.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the tracking IDs for the creators.
     */
    public function creatorTrackingIds(): HasMany
    {
        return $this->hasMany(CreatorVendorTrackingId::class);
    }

    /**
     * Get the resolved logo URL.
     *
     * @return string|null
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->resolveLogoUrl($this->logo, 'vendors');
    }
}
