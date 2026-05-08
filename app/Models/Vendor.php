<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'country_id',
        'domain',
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
}
