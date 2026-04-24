<?php

namespace App\Repositories\Interfaces;

use App\Models\FailedDailyClickJob;

interface FailedDailyClickJobRepositoryInterface
{
    public function create(array $data): FailedDailyClickJob;
}
