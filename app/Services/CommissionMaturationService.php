<?php

namespace App\Services;

use App\Repositories\Interfaces\CommissionRepositoryInterface;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\BalanceLedgerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionMaturationService
{
    public function __construct(
        protected CommissionRepositoryInterface $commissionRepository,
        protected CreatorRepositoryInterface $creatorRepository,
        protected BalanceLedgerRepositoryInterface $balanceLedgerRepository
    ) {}

    /**
     * Process all unmatured commissions older than 90 days.
     * Target: commissions WHERE matured_at IS NULL AND earned_at <= now() - 90 days AND calculated_at IS NOT NULL
     */
    public function execute(): int
    {
        $unmaturedByCreator = $this->commissionRepository->getUnmaturedCommissionsByCreator(90);
        $processedCount = 0;

        foreach ($unmaturedByCreator as $creatorId => $commissions) {
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

                    // Sanity check
                    if ($creator->pending_balance < $totalAmount) {
                        Log::error("Sanity check failed for creator {$creatorId}. Pending balance: {$creator->pending_balance}, Target Maturation: {$totalAmount}");
                        // We still proceed? The methodology says "sanity check — should always pass".
                        // If it fails, something is seriously wrong with the ledger, maybe we throw an exception to rollback?
                        throw new \Exception("Sanity check failed for creator {$creatorId}");
                    }

                    $pendingBalanceBefore = $creator->pending_balance;
                    $availableBalanceBefore = $creator->available_balance;

                    // 2. Adjust balances
                    $this->creatorRepository->update($creatorId, [
                        'pending_balance' => max(0, $pendingBalanceBefore - $totalAmount),
                        'available_balance' => $availableBalanceBefore + $totalAmount
                    ]);

                    $now = now();

                    // 3. Set matured_at = now() on each commission processed
                    foreach ($commissions as $commission) {
                        $commission->update(['matured_at' => $now]);
                        
                        // 4. Write ledger row
                        $this->balanceLedgerRepository->create([
                            'creator_id' => $creatorId,
                            'type' => 'commission_matured',
                            'amount' => $commission->commission_amount,
                            'balance_before' => $availableBalanceBefore, // Ledger usually tracks available balance for these
                            'balance_after' => $availableBalanceBefore + $commission->commission_amount,
                            'reference_id' => (string) $commission->id,
                            'performed_by' => null, // Cron job
                        ]);
                        
                        $availableBalanceBefore += $commission->commission_amount;
                        $processedCount++;
                    }
                });
            } catch (\Exception $e) {
                Log::error("Failed to mature commissions for creator {$creatorId}: " . $e->getMessage());
            }
        }

        return $processedCount;
    }
}
