<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface CreatorDashboardRepositoryInterface
{
    /**
     * Get raw commission data for creator in the specified date range.
     */
    public function getCommissionData(int $creatorId, string $startDate, string $endDate, ?int $vendorId): Collection;
}
