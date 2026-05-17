<?php

namespace App\Repositories\Interfaces;

use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface VendorRepositoryInterface
{
    public function all(): Collection;

    public function getActive(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Vendor;

    public function findOrFail(int $id): Vendor;

    public function create(array $data): Vendor;

    public function update(int $id, array $data): Vendor;

    public function delete(int $id): bool;

    public function paginateByCountry(int $countryId, int $perPage = 15): LengthAwarePaginator;
}
