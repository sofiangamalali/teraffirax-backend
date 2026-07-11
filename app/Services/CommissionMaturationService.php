<?php

namespace App\Services;

use App\Repositories\Interfaces\CommissionRepositoryInterface;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\BalanceLedgerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\MilestoneAlertMail;

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

        Log::channel('commission')->info("Processing commission maturation. Found " . count($unmaturedByCreator) . " creators with unmatured commissions.");

        foreach ($unmaturedByCreator as $creatorId => $commissions) {
            try {
                $totalAmount = $commissions->sum('commission_amount');
                
                Log::channel('commission')->info("Maturing commissions for Creator ID: {$creatorId}. Commission Count: " . count($commissions) . ", Total Amount: {$totalAmount}");

                $shouldNotifyCreator = false;
                $minimumWithdrawAmount = 100.00;
                $newAvailableBalance = 0.00;

                DB::transaction(function () use ($creatorId, $commissions, $totalAmount, &$processedCount, &$shouldNotifyCreator, &$minimumWithdrawAmount, &$newAvailableBalance) {
                    if ($totalAmount <= 0) {
                        Log::channel('commission')->info("Skipping Creator ID: {$creatorId} because total amount is zero or negative.");
                        return;
                    }

                    // 1. SELECT FOR UPDATE on creator row
                    $creator = $this->creatorRepository->findWithLock($creatorId);
                    
                    if (!$creator) {
                        Log::channel('commission')->warning("Creator ID: {$creatorId} not found during commission maturation lock.");
                        return;
                    }

                    // Sanity check
                    if ($creator->pending_balance < $totalAmount) {
                        Log::channel('commission')->error("Sanity check failed for creator {$creatorId}. Pending balance: {$creator->pending_balance}, Target Maturation: {$totalAmount}");
                        throw new \Exception("Sanity check failed for creator {$creatorId}");
                    }

                    $pendingBalanceBefore = $creator->pending_balance;
                    $availableBalanceBefore = $creator->available_balance;

                    $minimumWithdrawAmount = (float) setting('withdrawal.min_amount', 100.00);
                    $newAvailableBalance = $availableBalanceBefore + $totalAmount;

                    $updateData = [
                        'pending_balance' => max(0, $pendingBalanceBefore - $totalAmount),
                        'available_balance' => $newAvailableBalance
                    ];

                    // Check if available balance crossed the minimum withdraw threshold
                    if (is_null($creator->milestone_threshold_reached_at) && $availableBalanceBefore < $minimumWithdrawAmount && $newAvailableBalance >= $minimumWithdrawAmount) {
                        $updateData['milestone_threshold_reached_at'] = now();
                        $shouldNotifyCreator = true;
                    }

                    // 2. Adjust balances and update milestone timestamp
                    $this->creatorRepository->update($creatorId, $updateData);

                    $now = now();

                    // 3. Set matured_at = now() on each commission processed
                    foreach ($commissions as $commission) {
                        $commission->update(['matured_at' => $now]);
                        
                        // 4. Write ledger row
                        $this->balanceLedgerRepository->create([
                            'creator_id' => $creatorId,
                            'type' => 'commission_matured',
                            'amount' => $commission->commission_amount,
                            'balance_before' => $availableBalanceBefore,
                            'balance_after' => $availableBalanceBefore + $commission->commission_amount,
                            'reference_id' => (string) $commission->id,
                            'performed_by' => null,
                        ]);
                        
                        $availableBalanceBefore += $commission->commission_amount;
                        $processedCount++;
                    }

                    Log::channel('commission')->info("Successfully matured commissions for Creator ID: {$creatorId}. Balances adjusted.");
                });

                // Dispatch MilestoneAlertMail immediately after the transaction commits successfully
                if ($shouldNotifyCreator) {
                    $creator = $this->creatorRepository->find($creatorId);
                    if ($creator) {
                        Mail::to($creator->email)->send(new MilestoneAlertMail(
                            $creator,
                            $minimumWithdrawAmount,
                            $newAvailableBalance
                        ));
                    }
                }
            } catch (\Exception $e) {
                Log::channel('commission')->error("Failed to mature commissions for Creator ID: {$creatorId}. Error: " . $e->getMessage(), ['exception' => $e]);
            }
        }

        return $processedCount;
    }
}
