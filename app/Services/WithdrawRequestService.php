<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\CreatorWithdrawMethod;
use App\Models\WithdrawRequest;
use App\Repositories\Interfaces\WithdrawRequestRepositoryInterface;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\CreatorWithdrawMethodRepositoryInterface;
use App\DTOs\WithdrawRequestDTO;
use App\Enums\WithdrawRequestStatusEnum;
use App\Exceptions\WithdrawalException;
use Illuminate\Support\Facades\DB;
use Exception;

class WithdrawRequestService
{
    public function __construct(
        protected WithdrawRequestRepositoryInterface $withdrawRequestRepository,
        protected CreatorRepositoryInterface $creatorRepository,
        protected CreatorWithdrawMethodRepositoryInterface $creatorWithdrawMethodRepository,
    ) {
    }

    public function withdraw(WithdrawRequestDTO $dto): WithdrawRequest
    {
        $creatorId = $dto->creator_id;
        $withdrawMethodId = $dto->creator_withdraw_method_id;

        // 1. Validate withdrawal method ownership
        $method = $this->creatorWithdrawMethodRepository->findForCreator($withdrawMethodId, $creatorId);

        if (!$method) {
            throw new WithdrawalException(__('Method not found or not associated with this account.'));
        }

        // 2. Run in transaction with Pessimistic Locking
        return DB::transaction(function () use ($creatorId, $withdrawMethodId) {
            // Lock the creator row for update
            $creator = $this->creatorRepository->findWithLock($creatorId);

            if (!$creator) {
                throw new WithdrawalException(__('Creator not found.'));
            }

            $amount = $creator->available_balance;
            $minAmount = setting('withdrawal.min_amount', 500);

            // 3. Validate balance
            if ($amount < $minAmount) {
                throw new WithdrawalException(__('Minimum withdrawal amount is :amount', ['amount' => $minAmount]));
            }

            // 4. Create request
            $request = $this->withdrawRequestRepository->create([
                'creator_id' => $creatorId,
                'creator_withdraw_method_id' => $withdrawMethodId,
                'amount' => $amount,
                'status' => 'pending',
            ]);

            // 5. Deduct balance
            $creator->available_balance = 0; // Deduct the full amount
            $creator->save();

            return $request;
        });
    }

    public function getPaginatedForCreator(int $creatorId, array $filters, int $perPage = 15)
    {
        return $this->withdrawRequestRepository->getPaginatedForCreator($creatorId, $filters, $perPage);
    }

    public function getOptionsForCreator(int $creatorId): array
    {
        $statuses = array_map(fn($case) => [
            'value' => $case->value,
            'label' => ucfirst($case->value)
        ], WithdrawRequestStatusEnum::cases());

        $methods = $this->creatorWithdrawMethodRepository->getByCreator($creatorId)
            ->map(fn($method) => [
                'value' => $method->id,
                'label' => $method->method_name ?? "Method #{$method->id}"
            ])->toArray();

        return [
            'statuses' => $statuses,
            'withdraw_methods' => $methods,
        ];
    }
}
