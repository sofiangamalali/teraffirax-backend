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
}
