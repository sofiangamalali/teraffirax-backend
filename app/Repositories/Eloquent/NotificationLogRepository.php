<?php

namespace App\Repositories\Eloquent;

use App\Models\NotificationLog;
use App\Repositories\Interfaces\NotificationLogRepositoryInterface;

class NotificationLogRepository implements NotificationLogRepositoryInterface
{
    public function __construct(
        protected NotificationLog $model,
    ) {}

    public function create(array $data): NotificationLog
    {
        return $this->model->create($data);
    }
}
