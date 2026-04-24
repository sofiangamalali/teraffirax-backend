<?php

namespace App\Repositories\Interfaces;

use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Collection;

interface WithdrawalRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): Withdrawal;
    public function find(int $id): ?Withdrawal;
    public function updateStatus(int $id, string $status): bool;
    public function getByCreator(int $creatorId): Collection;
}
