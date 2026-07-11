<?php

namespace App\Services;

use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\BalanceLedgerRepositoryInterface;
use App\Mail\WeeklyFallbackMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class WeeklyFallbackService
{
    public function __construct(
        protected CreatorRepositoryInterface $creatorRepository,
        protected BalanceLedgerRepositoryInterface $balanceLedgerRepository
    ) {}

    /**
     * Run the weekly inactivity fallback flow and 30-day manual re-engagement flag check.
     */
    public function execute(): int
    {
        $lastActivities = $this->balanceLedgerRepository->getCreatorsLastActivity();
        $sentCount = 0;

        foreach ($lastActivities as $activity) {
            $creator = $this->creatorRepository->find($activity->creator_id);
            if (!$creator || !$creator->is_active) {
                continue;
            }

            // Exclude creators already flagged for manual re-engagement
            if ($creator->needs_manual_reengagement) {
                continue;
            }

            // Calculate days_since_last_activity comparing start of day in Cairo timezone
            $lastActivityAt = Carbon::parse($activity->last_activity_at)->timezone('Africa/Cairo')->startOfDay();
            $currentDate = Carbon::now('Africa/Cairo')->startOfDay();
            $daysSinceLastActivity = (int) abs($currentDate->diffInDays($lastActivityAt));

            // Flag for 30+ days inactivity
            if ($daysSinceLastActivity > 30) {
                $this->creatorRepository->update($creator->id, [
                    'needs_manual_reengagement' => true,
                ]);
                continue;
            }

            // Send weekly nudge if exactly 7 days of inactivity
            if ($daysSinceLastActivity === 7) {
                // Redis week format: Y-W (e.g. 2026-28)
                $weekNumber = Carbon::now('Africa/Cairo')->format('o-W');
                $redisKey = "weekly_fallback_sent:{$creator->id}:{$weekNumber}";

                // Guard check: query Redis only if condition is true to optimize operations
                $alreadySent = Redis::get($redisKey);
                if ($alreadySent) {
                    continue;
                }

                // Send weekly fallback email (automatically queued)
                Mail::to($creator->email)->send(new WeeklyFallbackMail($creator));

                // Mark as sent in Redis with 10 days TTL
                Redis::set($redisKey, '1', 'EX', 864000);
                $sentCount++;
            }
        }

        return $sentCount;
    }
}
