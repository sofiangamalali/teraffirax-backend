<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CategoryDTO
{
    public function __construct(
        public readonly ?string $name = null,
    ) {}

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
        ], fn ($value) => $value !== null);
    }
}
