<?php

namespace App\Repositories\Eloquent;

use App\Models\FailedDailyClickJob;
use App\Repositories\Interfaces\FailedDailyClickJobRepositoryInterface;

class FailedDailyClickJobRepository implements FailedDailyClickJobRepositoryInterface
{
    protected FailedDailyClickJob $model;

    public function __construct(FailedDailyClickJob $model)
    {
        $this->model = $model;
    }

    public function create(array $data): FailedDailyClickJob
    {
        return $this->model->create($data);
    }
}
