<?php

namespace App\Repositories\Interfaces;

use App\Models\NotificationLog;

interface NotificationLogRepositoryInterface
{
    public function create(array $data): NotificationLog;
}
