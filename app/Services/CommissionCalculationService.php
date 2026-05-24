<?php

namespace App\Services;

use App\Repositories\Interfaces\CommissionRepositoryInterface;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\BalanceLedgerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionCalculationService
{
    public function __construct(
        protected CommissionRepositoryInterface $commissionRepository,
        protected CreatorRepositoryInterface $creatorRepository,
        protected BalanceLedgerRepositoryInterface $balanceLedgerRepository
    ) {}

    /**
     * Process all uncalculated commissions.
     * Target: commissions WHERE calculated_at IS NULL
     */
    public function execute(): int
    {
        $uncalculatedByCreator = $this->commissionRepository->getUncalculatedCommissionsByCreator();
        $processedCount = 0;

        foreach ($uncalculatedByCreator as $creatorId => $commissions) {
            try {
                DB::transaction(function () use ($creatorId, $commissions, &$processedCount) {
                    $totalAmount = $commissions->sum('commission_amount');
                    
                    if ($totalAmount <= 0) {
                        return;
                    }

                    // 1. SELECT FOR UPDATE on creator row
                    $creator = $this->creatorRepository->findWithLock($creatorId);
                    
                    if (!$creator) {
                        return;
                    }

                    $balanceBefore = $creator->pending_balance;
                    $balanceAfter = $balanceBefore + $totalAmount;

                    // 2. Add to pending_balance
                    $this->creatorRepository->update($creatorId, [
                        'pending_balance' => $balanceAfter
                    ]);

                    $now = now();

                    // 3. Set calculated_at = now() on each commission processed
                    foreach ($commissions as $commission) {
                        $commission->update(['calculated_at' => $now]);
                        
                        // 4. Write ledger row
                        $this->balanceLedgerRepository->create([
                            'creator_id' => $creatorId,
                            'type' => 'commission_calculated',
                            'amount' => $commission->commission_amount,
                            'balance_before' => $balanceBefore,
                            'balance_after' => $balanceBefore + $commission->commission_amount, // Incremental for accurate ledger
                            'reference_id' => (string) $commission->id,
                            'performed_by' => null, // Cron job
                        ]);
                        
                        $balanceBefore += $commission->commission_amount;
                        $processedCount++;
                    }
                });
            } catch (\Exception $e) {
                Log::error("Failed to calculate commissions for creator {$creatorId}: " . $e->getMessage());
            }
        }

        return $processedCount;
    }
}
