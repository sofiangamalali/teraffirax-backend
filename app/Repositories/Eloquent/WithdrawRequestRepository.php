<?php

namespace App\Repositories\Eloquent;

use App\Models\WithdrawRequest;
use App\Repositories\Interfaces\WithdrawRequestRepositoryInterface;

class WithdrawRequestRepository implements WithdrawRequestRepositoryInterface
{
    public function create(array $data): WithdrawRequest
    {
        return WithdrawRequest::create($data);
    }

    public function update(string $id, array $data): WithdrawRequest
    {
        $withdrawRequest = $this->findById($id);
        if ($withdrawRequest) {
            $withdrawRequest->update($data);
            return $withdrawRequest->fresh();
        }
        throw new \Exception("WithdrawRequest not found");
    }

    public function findById(string $id): ?WithdrawRequest
    {
        return WithdrawRequest::find($id);
    }

    public function hasPendingOrApprovedForCreator(int $creatorId): bool
    {
        return WithdrawRequest::where('creator_id', $creatorId)
            ->whereIn('status', [
                \App\Enums\WithdrawRequestStatusEnum::PENDING,
                \App\Enums\WithdrawRequestStatusEnum::APPROVED,
            ])
            ->exists();
    }

    public function getPaginatedForCreator(int $creatorId, array $filters, int $perPage = 15)
    {
        $query = WithdrawRequest::with(['withdrawMethod.withdrawMethod', 'withdrawMethod.values'])->where('creator_id', $creatorId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['creator_withdraw_method_id'])) {
            $query->where('creator_withdraw_method_id', $filters['creator_withdraw_method_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->latest()->paginate($perPage);
    }
}
