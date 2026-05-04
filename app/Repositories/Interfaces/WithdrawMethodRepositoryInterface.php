<?php

namespace App\Repositories\Interfaces;

use App\Models\WithdrawMethod;
use Illuminate\Database\Eloquent\Collection;

interface WithdrawMethodRepositoryInterface
{
    public function all(?int $countryId = null): Collection;
    public function create(array $data): WithdrawMethod;
    public function find(int $id): ?WithdrawMethod;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getActive(): Collection;
}
