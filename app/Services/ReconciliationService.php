<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\Commission;
use App\Models\WithdrawRequest;
use App\Models\BalanceLedger;
use App\Repositories\Interfaces\ReconciliationAlertRepositoryInterface;
use App\Enums\WithdrawRequestStatusEnum;

class ReconciliationService
{
    public function __construct(
        protected ReconciliationAlertRepositoryInterface $alertRepository
    ) {}

    /**
     * Recompute balances from raw tables and compare with cache.
     */
    public function execute(): int
    {
        $creators = Creator::all();
        $alertsGenerated = 0;

        foreach ($creators as $creator) {
            // 1. Recompute pending_balance
            $recomputedPending = Commission::where('creator_id', $creator->id)
                ->whereNull('matured_at')
                ->whereNotNull('calculated_at')
                ->sum('commission_amount');

            // 2. Recompute available_balance
            $totalMatured = Commission::where('creator_id', $creator->id)
                ->whereNotNull('matured_at')
                ->sum('commission_amount');
            
            $totalWithdrawnOrPending = WithdrawRequest::where('creator_id', $creator->id)
                ->whereIn('status', [
                    WithdrawRequestStatusEnum::PENDING,
                    WithdrawRequestStatusEnum::APPROVED,
                    WithdrawRequestStatusEnum::COMPLETED,
                ])
                ->sum('amount');
            
            $recomputedAvailable = $totalMatured - $totalWithdrawnOrPending;

            // 3. Replay ledger (simplified check)
            $ledgerSumCalculated = BalanceLedger::where('creator_id', $creator->id)
                ->where('type', 'commission_calculated')
                ->sum('amount');
            
            $ledgerSumMatured = BalanceLedger::where('creator_id', $creator->id)
                ->where('type', 'commission_matured')
                ->sum('amount');

            // 4. Compare
            $mismatches = [];
            
            if (abs($creator->pending_balance - $recomputedPending) > 0.01) {
                $mismatches[] = "Pending balance mismatch. Cache: {$creator->pending_balance}, Recomputed: {$recomputedPending}";
            }
            
            if (abs($creator->available_balance - $recomputedAvailable) > 0.01) {
                $mismatches[] = "Available balance mismatch. Cache: {$creator->available_balance}, Recomputed: {$recomputedAvailable}";
            }

            if (!empty($mismatches)) {
                $this->alertRepository->create([
                    'creator_id' => $creator->id,
                    'message' => implode(' | ', $mismatches),
                ]);
                $alertsGenerated++;
            }
        }

        return $alertsGenerated;
    }
}
