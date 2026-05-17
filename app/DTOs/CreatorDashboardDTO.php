<?php

namespace App\DTOs;

use App\Http\Requests\Creator\CreatorDashboardRequest;
use Carbon\Carbon;

class CreatorDashboardDTO
{
    public function __construct(
        public readonly ?int $days = null,
        public readonly ?string $date_from = null,
        public readonly ?string $date_to = null,
        public readonly ?int $vendor_id = null,
    ) {
    }

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(CreatorDashboardRequest $request): self
    {
        return new self(
            days: $request->input('days') ? (int) $request->input('days') : null,
            date_from: $request->input('date_from'),
            date_to: $request->input('date_to'),
            vendor_id: $request->input('vendor_id') ? (int) $request->input('vendor_id') : null,
        );
    }

    /**
     * Get the computed start date.
     */
    public function getStartDate(): string
    {
        if ($this->days !== null) {
            return Carbon::today()->subDays($this->days)->format('Y-m-d');
        }

        if ($this->date_from !== null) {
            return $this->date_from;
        }

        // Default to last 30 days
        return Carbon::today()->subDays(30)->format('Y-m-d');
    }

    /**
     * Get the computed end date.
     */
    public function getEndDate(): string
    {
        if ($this->days !== null) {
            return Carbon::today()->format('Y-m-d');
        }

        if ($this->date_to !== null) {
            return $this->date_to;
        }

        return Carbon::today()->format('Y-m-d');
    }

    /**
     * Determine if the date range exceeds 3 months (90 days) for monthly grouping.
     */
    public function exceedsThreeMonths(): bool
    {
        $start = Carbon::parse($this->getStartDate());
        $end = Carbon::parse($this->getEndDate());
        
        return $start->diffInDays($end) > 90;
    }
}
