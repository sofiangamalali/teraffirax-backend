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

        Log::channel('commission')->info("Processing uncalculated commissions. Found " . count($uncalculatedByCreator) . " creators with pending commissions.");

        foreach ($uncalculatedByCreator as $creatorId => $commissions) {
            try {
                $totalAmount = $commissions->sum('commission_amount');
                
                Log::channel('commission')->info("Calculating commissions for Creator ID: {$creatorId}. Commission Count: " . count($commissions) . ", Total Amount: {$totalAmount}");

                DB::transaction(function () use ($creatorId, $commissions, $totalAmount, &$processedCount) {
                    if ($totalAmount <= 0) {
                        Log::channel('commission')->info("Skipping Creator ID: {$creatorId} because total amount is zero or negative.");
                        return;
                    }

                    // 1. SELECT FOR UPDATE on creator row
                    $creator = $this->creatorRepository->findWithLock($creatorId);
                    
                    if (!$creator) {
                        Log::channel('commission')->warning("Creator ID: {$creatorId} not found during commission calculation lock.");
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

                    Log::channel('commission')->info("Successfully calculated commissions for Creator ID: {$creatorId}. Balance changed: {$balanceBefore} -> {$balanceAfter}");
                });
            } catch (\Exception $e) {
                Log::channel('commission')->error("Failed to calculate commissions for Creator ID: {$creatorId}. Error: " . $e->getMessage(), ['exception' => $e]);
            }
        }

        return $processedCount;
    }
}
