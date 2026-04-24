<?php

namespace App\Repositories\Eloquent;

use App\Models\PaymentMethod;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    protected PaymentMethod $model;

    public function __construct(PaymentMethod $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): PaymentMethod
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?PaymentMethod
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
