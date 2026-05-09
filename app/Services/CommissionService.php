<?php

namespace App\Services;

use App\DTOs\CommissionFilterDTO;
use App\Repositories\Interfaces\CommissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommissionService
{
    public function __construct(
        protected CommissionRepositoryInterface $commissionRepository,
    ) {}

    public function getCreatorCommissions(int $creatorId, CommissionFilterDTO $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->commissionRepository->getForCreator($creatorId, $filters, $perPage);
    }
}
