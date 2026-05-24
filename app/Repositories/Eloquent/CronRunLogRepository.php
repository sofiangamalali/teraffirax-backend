<?php

namespace App\Repositories\Eloquent;

use App\Models\CronRunLog;
use App\Repositories\Interfaces\CronRunLogRepositoryInterface;

class CronRunLogRepository implements CronRunLogRepositoryInterface
{
    public function __construct(
        protected CronRunLog $model,
    ) {}

    public function create(array $data): CronRunLog
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): CronRunLog
    {
        $log = $this->model->find($id);
        if ($log) {
            $log->update($data);
            return $log->fresh();
        }
        throw new \Exception("CronRunLog not found");
    }

    public function findLatestByCronName(string $cronName): ?CronRunLog
    {
        return $this->model->where('cron_name', $cronName)->latest('started_at')->first();
    }
}
