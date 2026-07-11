<?php

namespace App\Repositories\Eloquent;

use App\Models\BalanceLedger;
use App\Repositories\Interfaces\BalanceLedgerRepositoryInterface;

class BalanceLedgerRepository implements BalanceLedgerRepositoryInterface
{
    public function __construct(
        protected BalanceLedger $model,
    ) {}

    public function create(array $data): BalanceLedger
    {
        return $this->model->create($data);
    }

    public function getDailyActivitySummaries(string $sinceDateTime): \Illuminate\Support\Collection
    {
        return $this->model->newQuery()
            ->select('creator_id')
            ->selectRaw("SUM(CASE WHEN type = 'commission_calculated' THEN amount ELSE 0 END) as new_commissions_sum")
            ->selectRaw("SUM(CASE WHEN type = 'commission_matured' THEN amount ELSE 0 END) as matured_sum")
            ->where('created_at', '>=', $sinceDateTime)
            ->groupBy('creator_id')
            ->get();
    }

    public function getCreatorsLastActivity(): \Illuminate\Support\Collection
    {
        return $this->model->newQuery()
            ->select('creator_id')
            ->selectRaw("MAX(created_at) as last_activity_at")
            ->groupBy('creator_id')
            ->get();
    }
}
