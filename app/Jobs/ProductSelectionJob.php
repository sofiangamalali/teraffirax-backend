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
        $this->onQueue('telegram-selection');
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\TelegramPublishingService $publishingService): void
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
            $selection = $publishingService->selectProductForCreator(
                $this->creatorId,
                $this->socialIntegrationId,
                $this->excludedProductIds,
                $this->forcedCategoryId
            );

            if (!$selection) {
                Redis::del($lockKey);
                Log::channel('posting')->info("No eligible products across all categories for creator " . $this->creatorId, [
                    'social_integration_id' => $this->socialIntegrationId,
                ]);
                return;
            }

            $product = $selection['product'];
            $categoryId = $selection['category_id'];
            $vendorAccount = $selection['vendor_account'];

            // Generate a stable idempotency key for this dispatch attempt.
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
                product: $product,
                categoryId: $categoryId,
                trackId: $vendorAccount->track_id,
                vendorSlug: $vendorAccount->vendor->slug,
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
}
