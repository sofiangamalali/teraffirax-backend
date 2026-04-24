<?php

namespace App\Services;

use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;

class WithdrawalService
{
    protected WithdrawalRepositoryInterface $repository;

    public function __construct(WithdrawalRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllWithdrawals(): Collection
    {
        return $this->repository->all();
    }

    public function getCreatorWithdrawals(int $creatorId): Collection
    {
        return $this->repository->getByCreator($creatorId);
    }

    public function createWithdrawal(array $data): Withdrawal
    {
        $data['status'] = 'pending';
        return $this->repository->create($data);
    }

    public function getWithdrawalById(int $id): ?Withdrawal
    {
        return $this->repository->find($id);
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['pending', 'done'])) {
            return false;
        }
        return $this->repository->updateStatus($id, $status);
    }
}
