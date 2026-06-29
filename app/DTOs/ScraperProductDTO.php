<?php

namespace App\DTOs;

class ScraperProductDTO
{
    public function __construct(
        public readonly string $external_id,
        public readonly string $name,
        public readonly string $url,
        public readonly ?float $price_before,
        public readonly float $price_after,
        public readonly ?float $discount_percentage,
        public readonly ?int $rank_in_vendor_category = null
    ) {}
}
