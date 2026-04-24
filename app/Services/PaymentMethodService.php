<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodService
{
    protected PaymentMethodRepositoryInterface $repository;

    public function __construct(PaymentMethodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllMethods(): Collection
    {
        return $this->repository->all();
    }

    public function getActiveMethods(): Collection
    {
        return $this->repository->getActive();
    }

    public function createMethod(array $data): PaymentMethod
    {
        return $this->repository->create($data);
    }

    public function getMethodById(int $id): ?PaymentMethod
    {
        return $this->repository->find($id);
    }

    public function updateMethod(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteMethod(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
