<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ProductDTO
{
    public function __construct(
        public readonly int|string|null $vendor_id = null,
        public readonly ?string $name = null,
        public readonly ?float $price_before = null,
        public readonly ?float $price_after = null,
        public readonly ?string $discount = null,
        public readonly ?string $product_url = null,
    ) {}

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            vendor_id: $request->input('vendor_id'),
            name: $request->input('name'),
            price_before: $request->input('price_before'),
            price_after: $request->input('price_after'),
            discount: $request->input('discount'),
            product_url: $request->input('product_url'),
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'vendor_id'   => $this->vendor_id,
            'name'        => $this->name,
            'price_before' => $this->price_before,
            'price_after'  => $this->price_after,
            'discount'    => $this->discount,
            'product_url' => $this->product_url,
        ], fn ($value) => $value !== null);
    }
}
