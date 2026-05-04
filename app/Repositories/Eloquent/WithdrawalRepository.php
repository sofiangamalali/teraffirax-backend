<?php

namespace App\Repositories\Eloquent;

use App\Models\Withdrawal;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WithdrawalRepository implements WithdrawalRepositoryInterface
{
    protected Withdrawal $model;

    public function __construct(Withdrawal $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['creator', 'withdrawMethod.withdrawMethod'])->latest()->get();
    }

    public function create(array $data): Withdrawal
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?Withdrawal
    {
        return $this->model->with(['creator', 'withdrawMethod.withdrawMethod'])->find($id);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $withdrawal = $this->model->find($id);
        return $withdrawal ? $withdrawal->update(['status' => $status]) : false;
    }

    public function getByCreator(int $creatorId): Collection
    {
        return $this->model->where('creator_id', $creatorId)
            ->with(['withdrawMethod.withdrawMethod'])
            ->latest()
            ->get();
    }
}
