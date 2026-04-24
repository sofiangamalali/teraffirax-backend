<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CountryDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $iso2 = null,
        public readonly ?string $phone_code = null,
        public readonly ?int $phone_min_length = null,
        public readonly ?int $phone_max_length = null,
        public readonly ?bool $is_active = null,
    ) {}

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            iso2: $request->input('iso2'),
            phone_code: $request->input('phone_code'),
            phone_min_length: $request->input('phone_min_length') ? (int) $request->input('phone_min_length') : null,
            phone_max_length: $request->input('phone_max_length') ? (int) $request->input('phone_max_length') : null,
            is_active: $request->has('is_active') ? (bool) $request->input('is_active') : null,
        );
    }

    /**
     * Convert DTO to array, filtering out null values.
     */
    public function toArray(): array
    {
        return array_filter([
            'name'             => $this->name,
            'iso2'             => $this->iso2,
            'phone_code'       => $this->phone_code,
            'phone_min_length' => $this->phone_min_length,
            'phone_max_length' => $this->phone_max_length,
            'is_active'        => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
