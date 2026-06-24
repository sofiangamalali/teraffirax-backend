<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasLogo;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, HasLogo;

    protected $fillable = [
        'name',
        'slug',
        'country_id',
        'domain',
        'logo',
        'commission',
        'commission_payout_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commission' => 'float',
        'commission_payout_days' => 'integer',
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
        return $this->hasMany(CreatorVendorTracking::class);
    }

    /**
     * Get the vendor categories associated with the vendor.
     */
    public function vendorCategories(): HasMany
    {
        return $this->hasMany(VendorCategory::class);
    }

    /**
     * Get the categories associated with the vendor through vendor_categories.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'vendor_categories')
            ->withPivot('external_id', 'external_name', 'status', 'commission', 'parent_vendor_category_id')
            ->withTimestamps();
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
