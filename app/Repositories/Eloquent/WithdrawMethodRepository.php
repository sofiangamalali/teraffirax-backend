<?php

namespace App\Repositories\Eloquent;

use App\Models\WithdrawMethod;
use App\Repositories\Interfaces\WithdrawMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WithdrawMethodRepository implements WithdrawMethodRepositoryInterface
{
    protected WithdrawMethod $model;

    public function __construct(WithdrawMethod $model)
    {
        $this->model = $model;
    }

    public function all(?int $countryId = null): Collection
    {
        $query = $this->model->query();
        if ($countryId) {
            $query->where('country_id', $countryId);
        }
        return $query->get();
    }

    public function create(array $data): WithdrawMethod
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?WithdrawMethod
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $method = $this->find($id);
        return $method ? $method->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $method = $this->find($id);
        return $method ? $method->delete() : false;
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }
}
