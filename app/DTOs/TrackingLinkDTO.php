<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class TrackingLinkDTO
{
    public function __construct(
        public readonly int|string|null $creator_id = null,
        public readonly int|string|null $product_id = null,
        public readonly ?string $redirect_url = null,
        public readonly ?string $slug = null,
    ) {}

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            creator_id: $request->input('creator_id'),
            product_id: $request->input('product_id'),
            redirect_url: $request->input('redirect_url'),
            slug: $request->input('slug'),
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'creator_id'   => $this->creator_id,
            'product_id'   => $this->product_id,
            'redirect_url' => $this->redirect_url,
            'slug'         => $this->slug,
        ], fn ($value) => $value !== null);
    }
}
