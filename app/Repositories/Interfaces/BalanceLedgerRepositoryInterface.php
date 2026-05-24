<?php

namespace App\Repositories\Interfaces;

use App\Models\BalanceLedger;

interface BalanceLedgerRepositoryInterface
{
    public function create(array $data): BalanceLedger;
}
