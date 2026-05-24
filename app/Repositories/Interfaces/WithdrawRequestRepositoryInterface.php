<?php

namespace App\Repositories\Interfaces;

use App\Models\WithdrawRequest;
use Illuminate\Support\Collection;

interface WithdrawRequestRepositoryInterface
{
    public function create(array $data): WithdrawRequest;
    public function update(string $id, array $data): WithdrawRequest;
    public function findById(string $id): ?WithdrawRequest;
    public function hasPendingOrApprovedForCreator(int $creatorId): bool;
    public function getPaginatedForCreator(int $creatorId, array $filters, int $perPage = 15);
}
