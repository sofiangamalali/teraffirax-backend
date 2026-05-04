<?php

namespace App\Repositories\Interfaces;

use App\Models\CreatorWithdrawMethod;
use Illuminate\Database\Eloquent\Collection;

interface CreatorWithdrawMethodRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): CreatorWithdrawMethod;
    public function find(int $id): ?CreatorWithdrawMethod;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByCreator(int $creatorId): Collection;
    public function unsetOtherDefaults(int $id, int $creatorId): void;
}
