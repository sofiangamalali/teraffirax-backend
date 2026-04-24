<?php

namespace App\Services;

use App\Repositories\Interfaces\DailyClickRepositoryInterface;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Models\Creator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyClickService
{
    protected DailyClickRepositoryInterface $dailyClickRepository;
    protected CreatorRepositoryInterface $creatorRepository;

    public function __construct(
        DailyClickRepositoryInterface $dailyClickRepository,
        CreatorRepositoryInterface $creatorRepository
    ) {
        $this->dailyClickRepository = $dailyClickRepository;
        $this->creatorRepository = $creatorRepository;
    }

    public function calculateAndRecordDailyClicks(Creator $creator, Carbon $targetDate): void
    {
        // 1. Get the latest record date for this creator
        $latestRecord = $this->dailyClickRepository->getLatestRecordBeforeDate($creator->id, $targetDate->copy()->addDay()->toDateString());
        
        // 2. Determine start date (last record + 1 day)
        // If no records, we start from the creator's creation date.
        $startDate = $latestRecord 
            ? Carbon::parse($latestRecord->date)->addDay() 
            : $creator->created_at->startOfDay();

        // 3. Sequential Backfill: Process every day from startDate to targetDate
        $currentDate = $startDate->copy();
        while ($currentDate->lte($targetDate)) {
            $this->processSnapshotForDate($creator, $currentDate, $targetDate);
            $currentDate->addDay();
        }
    }

    protected function processSnapshotForDate(Creator $creator, Carbon $date, Carbon $targetDate): void
    {
        DB::transaction(function () use ($creator, $date, $targetDate) {
            // Snapshot Algorithm: O(1)
            $latestBefore = $this->dailyClickRepository->getLatestRecordBeforeDate($creator->id, $date->toDateString());
            $baseline = $latestBefore ? $latestBefore->total_snapshot : 0;
            
            $currentTotal = (int) $creator->trackingLinks()->sum('clicks_count');

            // Logic: intermediate dates in backfill get 0 clicks.
            // ONLY the final targetDate gets the delta (currentTotal - baseline).
            if ($date->lt($targetDate)) {
                $todayClicks = 0;
                $snapshot = $baseline; 
            } else {
                $todayClicks = max(0, $currentTotal - $baseline);
                $snapshot = $currentTotal;
            }

            $this->dailyClickRepository->updateOrCreate(
                ['creator_id' => $creator->id, 'date' => $date->toDateString()],
                [
                    'clicks_count' => $todayClicks,
                    'total_snapshot' => $snapshot
                ]
            );
        });
    }
}
