<?php

namespace App\Repositories\Interfaces;

use App\Models\DailyClick;
use Illuminate\Support\Collection;

interface DailyClickRepositoryInterface
{
    public function create(array $data): DailyClick;
    public function getTotalClicksForCreator(int $creatorId): int;
    public function getLatestRecordBeforeDate(int $creatorId, string $date): ?DailyClick;
    public function updateOrCreate(array $criteria, array $data): DailyClick;
}
