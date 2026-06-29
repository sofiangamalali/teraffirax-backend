<?php

namespace App\Repositories\Interfaces;

use App\Models\{VendorCategory, Product};
use Illuminate\Database\Eloquent\Collection;

interface ScraperRepositoryInterface
{
    /**
     * Get active vendor categories by the vendor slug.
     */
    public function getVendorCategoriesBySlug(string $slug): Collection;

    /**
     * Find a vendor category by ID.
     */
    public function findVendorCategory(int $id): ?VendorCategory;

    /**
     * Upsert a product based on vendor_id and external_id.
     */
    public function upsertProduct(array $attributes, array $values): Product;
}

