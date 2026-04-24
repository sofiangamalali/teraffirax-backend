<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class VendorDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?int $country_id = null,
    ) {}

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            country_id: $request->input('country_id') ? (int) $request->input('country_id') : null,
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'country_id' => $this->country_id,
        ], fn ($value) => $value !== null);
    }
}
