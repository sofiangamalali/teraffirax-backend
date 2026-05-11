<?php

namespace App\Services;

use App\DTOs\CreatorCommissionFilterDTO;
use App\Repositories\Interfaces\CommissionRepositoryInterface;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CreatorCommissionService
{
    public function __construct(
        protected CommissionRepositoryInterface $commissionRepository,
        protected VendorRepositoryInterface $vendorRepository,
    ) {}

    public function getCreatorCommissions(int $creatorId, CreatorCommissionFilterDTO $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->commissionRepository->getForCreator($creatorId, $filters, $perPage);
    }

    public function getFilterOptions(): array
    {
        return [
            'vendors' => $this->vendorRepository->getActive(),
        ];
    }
}
