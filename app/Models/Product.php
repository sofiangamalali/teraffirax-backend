<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\ProductStatusEnum;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'vendor_category_id',
        'external_id',
        'name',
        'url',
        'price_before',
        'price_after',
        'discount_percentage',
        'rank_in_vendor_category',
        'commission',
        'score',
        'status',
        'is_blocked',
        'flagged_at',
        'last_validated_at',
    ];

    /**
     * Get the vendor that owns the product.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the vendor category that owns the product.
     */
    public function vendorCategory(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_before' => 'decimal:2',
            'price_after'  => 'decimal:2',
            'discount_percentage' => 'float',
            'rank_in_vendor_category' => 'integer',
            'commission' => 'float',
            'score' => 'integer',
            'status' => ProductStatusEnum::class,
            'is_blocked' => 'boolean',
            'flagged_at' => 'datetime',
            'last_validated_at' => 'datetime',
        ];
    }
}
