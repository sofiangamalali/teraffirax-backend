<?php

namespace App\Repositories\Interfaces;

use App\DTOs\CreatorCommissionFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommissionRepositoryInterface
{
    /**
     * Get commissions for a creator with filters.
     */
    public function getForCreator(int $creatorId, CreatorCommissionFilterDTO $filters, int $perPage = 15): LengthAwarePaginator;
}
