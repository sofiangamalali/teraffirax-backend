<?php

namespace App\Services;

use App\DTOs\CreatorDashboardDTO;
use App\Models\Vendor;
use App\Repositories\Interfaces\{CreatorRepositoryInterface, CreatorDashboardRepositoryInterface};
use Carbon\Carbon;

class CreatorDashboardService
{
    public function __construct(
        protected CreatorRepositoryInterface $creatorRepository,
        protected CreatorDashboardRepositoryInterface $dashboardRepository
    ) {
    }

    /**
     * Get only the wallet balances for the creator's dashboard.
     */
    public function getWalletData(int $creatorId): array
    {
        $creator = $this->creatorRepository->findOrFail($creatorId);
        $creator->load('country');

        return [
            'pending_balance' => (double) $creator->pending_balance,
            'available_balance' => (double) $creator->available_balance,
            'withdrawn_balance' => (double) $creator->withdrawn_balance,
            'currency' => $creator->country?->currency ?? 'EGP',
            'min_withdrawal_amount' => (double) setting('withdrawal.min_amount', 500),
        ];
    }

    /**
     * Get graph and summary data for the creator's dashboard based on filters.
     */
    public function getGraphData(int $creatorId, CreatorDashboardDTO $dto): array
    {
        // 1. Fetch Creator and their Currency
        $creator = $this->creatorRepository->findOrFail($creatorId);
        $creator->load('country');
        $currency = $creator->country?->currency ?? 'EGP';

        // 2. Fetch raw commission data
        $startDate = $dto->getStartDate();
        $endDate = $dto->getEndDate();
        $commissions = $this->dashboardRepository->getCommissionData($creatorId, $startDate, $endDate, $dto->vendor_id);

        // 3. Process Graph Data
        $exceedsThreeMonths = $dto->exceedsThreeMonths();

        $grouped = $commissions->groupBy(function ($item) use ($exceedsThreeMonths) {
            return $exceedsThreeMonths
                ? $item->earned_at->format('Y-m')
                : $item->earned_at->format('Y-m-d');
        });

        $graphData = [];
        foreach ($grouped as $key => $items) {
            $graphData[] = [
                'date' => $key,
                'commission' => (double) $items->sum('commission_amount'),
                'clicks' => (int) $items->sum('clicks_count'),
                'orders' => (int) $items->sum('items_count'),
                'currency' => $currency,
            ];
        }

        // 4. Process Footer Summary Data
        $totalClicks = (int) $commissions->sum('clicks_count');
        $totalOrders = (int) $commissions->sum('items_count');
        $totalCommission = (double) $commissions->sum('commission_amount');
        $shippedItems = (int) $commissions->sum('items_count');
        $returnedItems = (int) $commissions->sum('returned_count');

        $conversionRate = 0.0;
        if ($totalClicks > 0) {
            $conversionRate = round(($totalOrders / $totalClicks) * 100, 2);
        }

        $summary = [
            'clicks' => $totalClicks,
            'orders' => $totalOrders,
            'conversion_rate' => $conversionRate,
            'shipped_items' => $shippedItems,
            'returned_items' => $returnedItems,
            'commission' => $totalCommission,
            'currency' => $currency,
        ];

        return [
            'graph' => $graphData,
            'summary' => $summary,
        ];
    }

    /**
     * Get paginated dashboard videos for the creator based on their country.
     */
    public function getPaginatedVideos(int $creatorId, int $perPage = 15)
    {
        $creator = $this->creatorRepository->findOrFail($creatorId);

        return \App\Models\CreatorDashboardVideo::where('country_id', $creator->country_id)
            ->with('country')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get the available filters (days presets, vendors targeted to creator's country).
     */
    public function getFiltersData(int $creatorId): array
    {
        $creator = $this->creatorRepository->findOrFail($creatorId);

        $vendors = Vendor::where('country_id', $creator->country_id)
            ->where('is_active', true)
            ->get();

        return [
            'days' => [30, 60, 90],
            'vendors' => $vendors->map(fn($vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'logo' => $vendor->logo_url,
            ])->toArray(),
        ];
    }
}
