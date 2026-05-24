<?php

namespace App\Repositories\Interfaces;

use App\Models\CronRunLog;

interface CronRunLogRepositoryInterface
{
    public function create(array $data): CronRunLog;
    public function update(int $id, array $data): CronRunLog;
    public function findLatestByCronName(string $cronName): ?CronRunLog;
}
