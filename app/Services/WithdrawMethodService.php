<?php

namespace App\Services;

use App\Models\WithdrawMethod;
use App\Repositories\Interfaces\WithdrawMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WithdrawMethodService
{
    protected WithdrawMethodRepositoryInterface $repository;

    public function __construct(WithdrawMethodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllMethods(?int $countryId = null): Collection
    {
        return $this->repository->all($countryId);
    }

    public function getActiveMethods(): Collection
    {
        return $this->repository->getActive();
    }

    public function createMethod(array $data): WithdrawMethod
    {
        return $this->repository->create($data);
    }

    public function getMethodById(int $id): ?WithdrawMethod
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
