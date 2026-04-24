<?php

namespace App\Repositories\Eloquent;

use App\Models\Country;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CountryRepository implements CountryRepositoryInterface
{
    public function __construct(
        protected Country $model,
    ) {}

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function find(int $id): ?Country
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Country
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Country
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Country
    {
        $country = $this->findOrFail($id);
        $country->update($data);

        return $country->fresh();
    }

    public function delete(int $id): bool
    {
        $country = $this->findOrFail($id);

        return $country->delete();
    }
}
