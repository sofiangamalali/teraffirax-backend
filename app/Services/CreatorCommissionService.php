<?php

namespace App\Services;

use App\DTOs\CreatorCommissionFilterDTO;
use App\Repositories\Interfaces\CommissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CreatorCommissionService
{
    public function __construct(
        protected CommissionRepositoryInterface $commissionRepository,
    ) {}

    public function getCreatorCommissions(int $creatorId, CreatorCommissionFilterDTO $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->commissionRepository->getForCreator($creatorId, $filters, $perPage);
    }
}
