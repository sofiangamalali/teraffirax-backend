<?php

namespace App\Repositories\Eloquent;

use App\Models\ReconciliationAlert;
use App\Repositories\Interfaces\ReconciliationAlertRepositoryInterface;

class ReconciliationAlertRepository implements ReconciliationAlertRepositoryInterface
{
    public function __construct(
        protected ReconciliationAlert $model,
    ) {}

    public function create(array $data): ReconciliationAlert
    {
        return $this->model->create($data);
    }

    public function getPaginated(array $filters, int $perPage = 15)
    {
        $query = $this->model->with('creator');

        if (!empty($filters['creator_id'])) {
            $query->where('creator_id', $filters['creator_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->latest('id')->paginate($perPage);
    }
}
