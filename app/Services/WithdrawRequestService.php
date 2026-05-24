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
use App\Repositories\Interfaces\BalanceLedgerRepositoryInterface;

class WithdrawRequestService
{
    public function __construct(
        protected WithdrawRequestRepositoryInterface $withdrawRequestRepository,
        protected CreatorRepositoryInterface $creatorRepository,
        protected CreatorWithdrawMethodRepositoryInterface $creatorWithdrawMethodRepository,
        protected BalanceLedgerRepositoryInterface $balanceLedgerRepository,
    ) {}

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

            // Verify no other pending or approved request exists
            if ($this->withdrawRequestRepository->hasPendingOrApprovedForCreator($creatorId)) {
                throw new WithdrawalException(__('You already have a pending or approved withdrawal request.'));
            }

            // 4. Create request
            $request = $this->withdrawRequestRepository->create([
                'creator_id' => $creatorId,
                'creator_withdraw_method_id' => $withdrawMethodId,
                'amount' => $amount, // The requested amount (entire available_balance)
                'status' => WithdrawRequestStatusEnum::PENDING,
                // 'requested_at' is handled by DB default
            ]);

            // 5. Deduct balance
            $balanceBefore = $creator->available_balance;
            $creator->update(['available_balance' => 0]);

            // 6. Write ledger row
            $this->balanceLedgerRepository->create([
                'creator_id' => $creatorId,
                'type' => 'withdrawal_requested',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => 0,
                'reference_id' => $request->id,
                'performed_by' => null, // User action
            ]);

            return $request;
        });
    }

    public function approveRequest(string $requestId, int $adminId): WithdrawRequest
    {
        $request = $this->withdrawRequestRepository->findById($requestId);
        
        if (!$request || $request->status !== WithdrawRequestStatusEnum::PENDING) {
            throw new WithdrawalException("Request is not pending or not found.");
        }

        return $this->withdrawRequestRepository->update($requestId, [
            'status' => WithdrawRequestStatusEnum::APPROVED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
    }

    public function completeRequest(string $requestId, string $transferReference, string $receiptUrl, int $adminId): WithdrawRequest
    {
        $request = $this->withdrawRequestRepository->findById($requestId);
        
        if (!$request || $request->status !== WithdrawRequestStatusEnum::APPROVED) {
            throw new WithdrawalException("Request is not approved or not found.");
        }

        return DB::transaction(function () use ($request, $transferReference, $receiptUrl, $adminId) {
            $creator = $this->creatorRepository->findWithLock($request->creator_id);

            // Mark complete
            $request = $this->withdrawRequestRepository->update($request->id, [
                'status' => WithdrawRequestStatusEnum::COMPLETED,
                'transfer_reference' => $transferReference,
                'receipt_url' => $receiptUrl,
                'completed_at' => now(),
            ]);

            $withdrawnBefore = $creator->withdrawn_balance;
            
            // Increment withdrawn balance
            $creator->update([
                'withdrawn_balance' => $withdrawnBefore + $request->amount
            ]);

            // Write ledger row
            $this->balanceLedgerRepository->create([
                'creator_id' => $creator->id,
                'type' => 'withdrawal_completed',
                'amount' => $request->amount,
                'balance_before' => $creator->available_balance, // Available balance didn't change
                'balance_after' => $creator->available_balance,
                'reference_id' => $request->id,
                'performed_by' => $adminId,
            ]);

            return $request;
        });
    }

    public function rejectRequest(string $requestId, string $reason, int $adminId): WithdrawRequest
    {
        $request = $this->withdrawRequestRepository->findById($requestId);
        
        if (!$request || !in_array($request->status, [WithdrawRequestStatusEnum::PENDING, WithdrawRequestStatusEnum::APPROVED])) {
            throw new WithdrawalException("Request cannot be rejected.");
        }

        return DB::transaction(function () use ($request, $reason, $adminId) {
            $creator = $this->creatorRepository->findWithLock($request->creator_id);

            $balanceBefore = $creator->available_balance;

            // Restore balance
            $creator->update([
                'available_balance' => $balanceBefore + $request->amount
            ]);

            // Mark rejected
            $request = $this->withdrawRequestRepository->update($request->id, [
                'status' => WithdrawRequestStatusEnum::REJECTED,
                'rejection_reason' => $reason,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(), // Assuming reviewed_at also applies to rejection
            ]);

            // Write ledger row
            $this->balanceLedgerRepository->create([
                'creator_id' => $creator->id,
                'type' => 'withdrawal_rejected',
                'amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $request->amount,
                'reference_id' => $request->id,
                'performed_by' => $adminId,
            ]);

            // TODO: Notify creator (events/notifications)

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
            ->load(['withdrawMethod', 'values'])
            ->map(fn($method) => [
                'value' => $method->id,
                'label' => $method->withdrawMethod?->name,
                'is_default' => $method->is_default,
                'method' => [
                    'id' => $method->withdrawMethod?->id,
                    'name' => $method->withdrawMethod?->name,
                    'slug' => $method->withdrawMethod?->slug,
                    'logo' => $method->withdrawMethod?->logo_url,
                ],
                'values' => $method->values->pluck('field_value', 'field_name')->toArray(),
            ])->toArray();

        return [
            'statuses' => $statuses,
            'withdraw_methods' => $methods,
        ];
    }
}
