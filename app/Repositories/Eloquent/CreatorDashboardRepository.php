<?php

namespace App\Repositories\Eloquent;

use App\Models\Commission;
use App\Repositories\Interfaces\CreatorDashboardRepositoryInterface;
use Illuminate\Support\Collection;

class CreatorDashboardRepository implements CreatorDashboardRepositoryInterface
{
    public function __construct(
        protected Commission $model
    ) {
    }

    /**
     * Get raw commission data for creator in the specified date range.
     */
    public function getCommissionData(int $creatorId, string $startDate, string $endDate, ?int $vendorId): Collection
    {
        $query = $this->model->newQuery()
            ->where('creator_id', $creatorId)
            ->whereDate('earned_at', '>=', $startDate)
            ->whereDate('earned_at', '<=', $endDate);

        if ($vendorId !== null) {
            $query->where('vendor_id', $vendorId);
        }

        return $query->orderBy('earned_at', 'asc')->get();
    }
}
