<?php

namespace App\Services;

use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\BalanceLedgerRepositoryInterface;
use App\Mail\DailyDigestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class DailyDigestService
{
    public function __construct(
        protected CreatorRepositoryInterface $creatorRepository,
        protected BalanceLedgerRepositoryInterface $balanceLedgerRepository
    ) {}

    /**
     * Run the daily digest notification flow.
     */
    public function execute(): int
    {
        $since = Carbon::now('Africa/Cairo')->subHours(24)->toDateTimeString();
        $summaries = $this->balanceLedgerRepository->getDailyActivitySummaries($since);
        $sentCount = 0;

        $dateStr = Carbon::now('Africa/Cairo')->format('Y-m-d');

        foreach ($summaries as $summary) {
            $maturedSum = (float) $summary->matured_sum;
            $newCommissionsSum = (float) $summary->new_commissions_sum;

            // Only proceed if new_commissions_sum > 0 OR matured_sum > 0
            if ($maturedSum <= 0 && $newCommissionsSum <= 0) {
                continue;
            }

            $creator = $this->creatorRepository->find($summary->creator_id);
            if (!$creator || !$creator->is_active) {
                continue;
            }

            // Check Redis key for daily digest idempotency
            $redisKey = "digest_sent:{$creator->id}:{$dateStr}";
            $alreadySent = Redis::get($redisKey);
            if ($alreadySent) {
                continue;
            }

            // Determine the variant:
            // Variant A: Only matured_sum > 0 (money became available — prioritize this framing whenever matured_sum > 0)
            // Variant B: Only new_commissions_sum > 0
            // Variant C: Both > 0
            $variant = 'C';
            if ($maturedSum > 0 && $newCommissionsSum <= 0) {
                $variant = 'A';
            } elseif ($newCommissionsSum > 0 && $maturedSum <= 0) {
                $variant = 'B';
            } elseif ($maturedSum > 0 && $newCommissionsSum > 0) {
                $variant = 'C';
            }

            // Send daily digest email (automatically queued via ShouldQueue)
            Mail::to($creator->email)->send(new DailyDigestMail(
                $creator,
                $variant,
                $maturedSum,
                $newCommissionsSum,
                (float) $creator->available_balance,
                (float) $creator->pending_balance
            ));

            // Mark as sent in Redis with 48 hours TTL
            Redis::set($redisKey, '1', 'EX', 172800);
            $sentCount++;
        }

        return $sentCount;
    }
}
