<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CommissionFilterDTO
{
    public function __construct(
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly ?int $vendor_id = null,
    ) {
    }

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            start_date: $request->input('start_date'),
            end_date: $request->input('end_date'),
            vendor_id: $request->input('vendor_id') ? (int) $request->input('vendor_id') : null,
        );
    }

    /**
     * Convert DTO to array, filtering out null values.
     */
    public function toArray(): array
    {
        return array_filter([
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'vendor_id' => $this->vendor_id,
        ], fn($value) => $value !== null);
    }
}
