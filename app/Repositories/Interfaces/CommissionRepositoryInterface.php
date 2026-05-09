<?php

namespace App\Repositories\Interfaces;

use App\DTOs\CommissionFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommissionRepositoryInterface
{
    /**
     * Get commissions for a creator with filters.
     */
    public function getForCreator(int $creatorId, CommissionFilterDTO $filters, int $perPage = 15): LengthAwarePaginator;
}
