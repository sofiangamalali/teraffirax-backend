<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ScraperBulkUpsertDTO
{
    /**
     * @param ScraperProductDTO[] $products
     */
    public function __construct(
        public readonly int $vendor_id,
        public readonly int $category_id,
        public readonly int $vendor_category_id,
        public readonly array $products
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $products = [];
        foreach ($request->input('products', []) as $productData) {
            $products[] = new ScraperProductDTO(
                external_id: $productData['external_id'],
                name: $productData['name'],
                url: $productData['url'],
                price_before: isset($productData['price_before']) ? (float) $productData['price_before'] : null,
                price_after: (float) $productData['price_after'],
                discount_percentage: isset($productData['discount_percentage']) ? (float) $productData['discount_percentage'] : null,
                rank_in_vendor_category: isset($productData['rank_in_vendor_category']) ? (int) $productData['rank_in_vendor_category'] : null
            );
        }

        return new self(
            vendor_id: (int) $request->input('vendor_id'),
            category_id: (int) $request->input('category_id'),
            vendor_category_id: (int) $request->input('vendor_category_id'),
            products: $products
        );
    }
}
