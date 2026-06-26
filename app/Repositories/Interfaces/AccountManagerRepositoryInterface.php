<?php

namespace App\Repositories\Interfaces;

use App\Models\AccountManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AccountManagerRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function findOrFail(int $id): AccountManager;
    public function create(array $data): AccountManager;
    public function update(int $id, array $data): AccountManager;
    public function delete(int $id): bool;
}
