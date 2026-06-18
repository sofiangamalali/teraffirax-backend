<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
    ];

    /**
     * Get the creators for the category.
     */
    public function creators(): BelongsToMany
    {
        return $this->belongsToMany(Creator::class)->withPivot('last_posted_at')->withTimestamps();
    }

    /**
     * Get the vendor categories associated with this category.
     */
    public function vendorCategories(): HasMany
    {
        return $this->hasMany(VendorCategory::class);
    }

    /**
     * Get the products associated with this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the vendors associated with this category through vendor_categories.
     */
    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'vendor_categories')
            ->withPivot('external_id', 'external_name', 'status', 'commission', 'parent_vendor_category_id')
            ->withTimestamps();
    }
}
