<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CreatorCommissionFilterDTO;
use App\Models\Commission;
use App\Repositories\Interfaces\CommissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommissionRepository implements CommissionRepositoryInterface
{
    public function __construct(
        protected Commission $model,
    ) {}

    public function getForCreator(int $creatorId, CreatorCommissionFilterDTO $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('creator_id', $creatorId);

        if ($filters->start_date) {
            $query->where('date', '>=', $filters->start_date);
        }

        if ($filters->end_date) {
            $query->where('date', '<=', $filters->end_date);
        }

        if ($filters->vendor_id) {
            $query->where('vendor_id', $filters->vendor_id);
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }
}
