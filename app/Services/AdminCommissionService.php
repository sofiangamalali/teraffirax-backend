<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CreatorVendorTracking;
use App\Repositories\Interfaces\CommissionRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AdminCommissionService
{
    public function __construct(
        protected CommissionRepositoryInterface $commissionRepository,
    ) {
    }

    /**
     * Create a manual commission using track_id, validating uniqueness per vendor per day.
     *
     * @throws ValidationException
     */
    public function createManualCommission(array $data): Commission
    {
        $tracking = CreatorVendorTracking::where('track_id', $data['track_id'])
            ->where('vendor_id', $data['vendor_id'])
            ->first();

        if (!$tracking) {
            throw ValidationException::withMessages([
                'track_id' => ['The tracking code does not match the selected vendor.']
            ]);
        }

        $date = date('Y-m-d', strtotime($data['earned_at']));

        // Check if commission already exists for this creator, vendor, and date
        if ($this->commissionRepository->existsForDate($tracking->creator_id, $tracking->vendor_id, $date)) {
            throw ValidationException::withMessages([
                'earned_at' => ['A commission has already been added for this vendor and creator on this date.']
            ]);
        }

        // Calculate creator's commission amount based on platform_commission_percentage
        $platformPercentage = $tracking->platform_commission_percentage;
        $creatorPercentage = 100.00 - $platformPercentage;
        $commissionAmount = $data['total_commission'] * ($creatorPercentage / 100.00);

        return $this->commissionRepository->create([
            'creator_id' => $tracking->creator_id,
            'vendor_id' => $tracking->vendor_id,
            'earned_at' => $data['earned_at'],
            'clicks_count' => $data['clicks_count'] ?? 0,
            'items_count' => $data['items_count'] ?? 0,
            'returned_count' => $data['returned_count'] ?? 0,
            'revenue_amount' => $data['revenue_amount'],
            'commission_amount' => $commissionAmount,
            'calculated_at' => null,
            'matured_at' => null,
        ]);
    }
}
