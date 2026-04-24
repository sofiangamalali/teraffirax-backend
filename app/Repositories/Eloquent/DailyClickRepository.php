<?php

namespace App\Repositories\Eloquent;

use App\Models\DailyClick;
use App\Repositories\Interfaces\DailyClickRepositoryInterface;

class DailyClickRepository implements DailyClickRepositoryInterface
{
    protected DailyClick $model;

    public function __construct(DailyClick $model)
    {
        $this->model = $model;
    }

    public function create(array $data): DailyClick
    {
        return $this->model->create($data);
    }

    public function getTotalClicksForCreator(int $creatorId): int
    {
        return $this->model->where('creator_id', $creatorId)->sum('clicks_count');
    }

    public function getLatestRecordBeforeDate(int $creatorId, string $date): ?DailyClick
    {
        return $this->model->where('creator_id', $creatorId)
            ->where('date', '<', $date)
            ->orderByDesc('date')
            ->first();
    }

    public function updateOrCreate(array $criteria, array $data): DailyClick
    {
        return $this->model->updateOrCreate($criteria, $data);
    }
}
