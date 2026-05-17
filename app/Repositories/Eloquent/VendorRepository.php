<?php

namespace App\Repositories\Eloquent;

use App\Models\Vendor;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VendorRepository implements VendorRepositoryInterface
{
    public function __construct(
        protected Vendor $model,
    ) {}

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function getActive(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function find(int $id): ?Vendor
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Vendor
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Vendor
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Vendor
    {
        $vendor = $this->findOrFail($id);
        $vendor->update($data);

        return $vendor->fresh();
    }

    public function delete(int $id): bool
    {
        $vendor = $this->findOrFail($id);

        return $vendor->delete();
    }

    public function paginateByCountry(int $countryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('country_id', $countryId)
            ->with('country')
            ->paginate($perPage);
    }
}
