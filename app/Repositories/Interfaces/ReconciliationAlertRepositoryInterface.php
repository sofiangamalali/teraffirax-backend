<?php

namespace App\Repositories\Interfaces;

use App\Models\ReconciliationAlert;

interface ReconciliationAlertRepositoryInterface
{
    public function create(array $data): ReconciliationAlert;
}
