<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class ProductSelectionJob implements ShouldQueue
{
    use Queueable;

    public $queue = 'telegram-selection';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $socialIntegrationId,
        public string $externalId,
        public int $creatorId,
        public int $retryCount = 0,
        public ?int $forcedCategoryId = null,
        public bool $lockAlreadyAcquired = false,
        public array $excludedProductIds = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lockKey = "channel_lock:" . $this->socialIntegrationId;
        $postingInterval = env('POSTING_INTERVAL_SECONDS', 300);

        if (!$this->lockAlreadyAcquired) {
            $acquired = Redis::set($lockKey, '1', 'EX', $postingInterval, 'NX');
            if (!$acquired) {
                return;
            }
        }

        try {
            if ($this->forcedCategoryId) {
                $categoryId = $this->forcedCategoryId;
            } else {
                $category = DB::table('category_creator')
                    ->where('creator_id', $this->creatorId)
                    ->orderBy('last_posted_at', 'asc')
                    ->select('category_id', 'last_posted_at')
                    ->first();

                if (!$category) {
                    if (!$this->lockAlreadyAcquired) {
                        Redis::del($lockKey);
                    }
                    Log::channel('posting')->info("No categories for creator " . $this->creatorId);
                    return;
                }

                $categoryId = $category->category_id;
            }

            $product = $this->selectProductFromCategory($categoryId);

            if (!$product) {
                // Update last_posted_at for the initial category
                DB::table('category_creator')
                    ->where('creator_id', $this->creatorId)
                    ->where('category_id', $categoryId)
                    ->update(['last_posted_at' => now()]);

                $allCategories = DB::table('category_creator')
                    ->where('creator_id', $this->creatorId)
                    ->where('category_id', '!=', $categoryId)
                    ->orderBy('last_posted_at', 'asc')
                    ->select('category_id')
                    ->get();

                foreach ($allCategories as $cat) {
                    $product = $this->selectProductFromCategory($cat->category_id);
                    if ($product) {
                        break;
                    }

                    // Update last_posted_at for checked category even if empty
                    DB::table('category_creator')
                        ->where('creator_id', $this->creatorId)
                        ->where('category_id', $cat->category_id)
                        ->update(['last_posted_at' => now()]);
                }

                if (!$product) {
                    Redis::del($lockKey);
                    Log::channel('posting')->info('No eligible products across all categories', [
                        'social_integration_id' => $this->socialIntegrationId,
                    ]);
                    return;
                }
            }

            $vendorAccount = DB::table('creator_vendor_tracking as cvt')
                ->join('vendors as v', 'v.id', '=', 'cvt.vendor_id')
                ->where('cvt.creator_id', $this->creatorId)
                ->where('cvt.vendor_id', $product->vendor_id)
                ->where('cvt.is_active', true)
                ->where('v.is_active', true)
                ->select('cvt.track_id', 'v.slug')
                ->first();

            if (!$vendorAccount) {
                Redis::del($lockKey);
                Log::channel('posting')->info("No active vendor account for creator {$this->creatorId} vendor {$product->vendor_id}");
                return;
            }

            // Generate a stable idempotency key for this dispatch attempt.
            // Scoped to: channel + product + hour-window.
            // If the job retries within the same hour, the key is identical →
            // the guard in SendMessageToTelegramJob will catch it and skip the send.
            $idempotencyKey = hash('sha256', implode('_', [
                $this->socialIntegrationId,
                $product->id,
                now()->format('Y-m-d-H'),
            ]));

            // Flask health check before dispatching ValidateProductDataJob
            if (!\App\Services\FlaskHealthCheck::isAlive()) {
                Redis::del($lockKey);
                Log::channel('posting')->warning('Flask service is down, skipping cycle', [
                    'social_integration_id' => $this->socialIntegrationId,
                ]);
                return;
            }

            ValidateProductDataJob::dispatch(
                socialIntegrationId: $this->socialIntegrationId,
                externalId: $this->externalId,
                creatorId: $this->creatorId,
                product: Product::findOrFail($product->id),
                categoryId: $product->category_id,
                trackId: $vendorAccount->track_id,
                vendorSlug: $vendorAccount->slug,
                lockKey: $lockKey,
                idempotencyKey: $idempotencyKey,
                retryCount: $this->retryCount,
                excludedProductIds: $this->excludedProductIds
            );
        } catch (\Exception $e) {
            Redis::del($lockKey);
            throw $e;
        }
    }

    /**
     * Select a product from a specific category with deduplication constraints.
     */
    private function selectProductFromCategory(int $categoryId): ?object
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $interval24 = $isSqlite ? "datetime('now', '-24 hours')" : "NOW() - INTERVAL 24 HOUR";
        $interval3 = $isSqlite ? "datetime('now', '-3 hours')" : "NOW() - INTERVAL 3 HOUR";

        return DB::table('products as p')
            ->join('creator_vendor_tracking as cva', function ($join) {
                $join->on('cva.vendor_id', '=', 'p.vendor_id')
                    ->where('cva.creator_id', '=', $this->creatorId)
                    ->where('cva.is_active', '=', true);
            })
            ->join('vendors as v', function ($join) {
                $join->on('v.id', '=', 'p.vendor_id')
                    ->where('v.is_active', '=', true);
            })
            ->where('p.category_id', $categoryId)
            ->where('p.status', 'active')
            ->whereNotIn('p.id', $this->excludedProductIds)
            ->whereNotIn('p.id', function ($query) use ($interval24) {
                $query->select('product_id')
                    ->from('telegram_product_posts')
                    ->where('social_integration_id', $this->socialIntegrationId)
                    ->where('post_status', 'posted')
                    ->whereRaw("posted_at > {$interval24}");
            })
            ->whereNotIn('p.id', function ($query) use ($interval3) {
                $query->select('product_id')
                    ->from('telegram_product_posts')
                    ->where('post_status', 'posted')
                    ->whereRaw("posted_at > {$interval3}");
            })
            ->orderBy('p.score', 'desc')
            ->select('p.*')
            ->first();
    }
}
