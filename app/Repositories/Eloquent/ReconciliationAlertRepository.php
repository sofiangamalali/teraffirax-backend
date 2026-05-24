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
}
