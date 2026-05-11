<?php

namespace App\Repositories\Interfaces;

use App\Models\WithdrawRequest;
use Illuminate\Support\Collection;

interface WithdrawRequestRepositoryInterface
{
    public function create(array $data): WithdrawRequest;
    public function getPaginatedForCreator(int $creatorId, array $filters, int $perPage = 15);
}
