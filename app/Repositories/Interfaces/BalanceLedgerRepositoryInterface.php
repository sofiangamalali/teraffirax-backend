<?php

namespace App\Repositories\Interfaces;

use App\Models\BalanceLedger;

interface BalanceLedgerRepositoryInterface
{
    public function create(array $data): BalanceLedger;

    public function getDailyActivitySummaries(string $sinceDateTime): \Illuminate\Support\Collection;

    public function getCreatorsLastActivity(): \Illuminate\Support\Collection;
}
