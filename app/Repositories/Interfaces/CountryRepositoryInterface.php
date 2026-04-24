<?php

namespace App\Repositories\Interfaces;

use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CountryRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Country;

    public function findOrFail(int $id): Country;

    public function create(array $data): Country;

    public function update(int $id, array $data): Country;

    public function delete(int $id): bool;
}
