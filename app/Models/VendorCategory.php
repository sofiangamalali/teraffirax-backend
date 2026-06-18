<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'external_id',
        'external_name',
        'status',
        'commission',
        'parent_vendor_category_id',
    ];

    protected $casts = [
        'commission' => 'float',
    ];

    /**
     * Get the vendor associated with this vendor category.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the category associated with this vendor category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the parent vendor category.
     */
    public function parent()
    {
        return $this->belongsTo(VendorCategory::class, 'parent_vendor_category_id');
    }

    /**
     * Get the child vendor categories.
     */
    public function children()
    {
        return $this->hasMany(VendorCategory::class, 'parent_vendor_category_id');
    }

    /**
     * Get the products associated with this vendor category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
