<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\Product;

class ValidateProductDataJob implements ShouldQueue
{
    use Queueable;

    public string $queue = 'telegram-validation';

    public function __construct(
        public int $socialIntegrationId,
        public string $externalId,
        public int $creatorId,
        public Product $product,
        public int $categoryId,
        public string $trackId,
        public string $vendorSlug,
        public string $lockKey,
        public string $idempotencyKey,
        public int $retryCount = 0,
        public array $excludedProductIds = []
    ) {

    }

    public function handle(): void
    {
        $flaskUrl = env('PYTHON_PRODUCT_SCRAP_URL', 'http://127.0.0.1:5000') . '/validate';

        try {
            $response = Http::timeout(30)->post($flaskUrl, [
                'url' => $this->product->url,
                'vendor_slug' => $this->vendorSlug,
                'stored_price' => (float) $this->product->price_after,
                'stored_discount' => (float) $this->product->discount_percentage,
                'stored_commission' => (float) $this->product->commission
            ]);

            if ($response->failed()) {
                $this->handleFlaskError();
                return;
            }

            $data = $response->json();
            $status = $data['status'] ?? 'error';

            if ($status === 'same') {
                $this->handleSame($data);
            } elseif ($status === 'higher') {
                $this->handleHigher($data);
            } elseif ($status === 'lower') {
                $this->handleLower($data);
            } elseif ($status === 'out_of_stock') {
                $this->handleOutOfStock();
            } elseif ($status === 'unavailable') {
                $this->handleUnavailable();
            } else {
                $this->handleFlaskError();
            }

        } catch (ConnectionException $e) {
            $this->handleFlaskError();
        } catch (\Exception $e) {
            $this->handleFlaskError();
        }
    }

    protected function handleSame(array $response): void
    {
        DB::table('products')->where('id', $this->product->id)->update([
            'last_validated_at' => now(),
        ]);

        $this->dispatchSendMessage($response['screenshot_url'] ?? null);
    }

    protected function handleHigher(array $response): void
    {
        $newScore = $this->recalculateScore(
            $response['current_discount'] ?? 0,
            $this->product->commission,
            $this->product->rank_in_vendor_category
        );

        DB::table('products')->where('id', $this->product->id)->update([
            'discount_percentage' => $response['current_discount'],
            'price_after' => $response['current_price'],
            'score' => $newScore,
            'last_validated_at' => now(),
        ]);

        $this->product->discount_percentage = $response['current_discount'];
        $this->product->price_after = $response['current_price'];
        $this->product->score = $newScore;

        $this->dispatchSendMessage($response['screenshot_url'] ?? null);
    }

    protected function handleLower(array $response): void
    {
        $newScore = $this->recalculateScore(
            $response['current_discount'] ?? 0,
            $this->product->commission,
            $this->product->rank_in_vendor_category
        );

        DB::table('products')->where('id', $this->product->id)->update([
            'discount_percentage' => $response['current_discount'],
            'price_after' => $response['current_price'],
            'score' => $newScore,
            'last_validated_at' => now(),
        ]);

        $this->product->discount_percentage = $response['current_discount'];
        $this->product->price_after = $response['current_price'];
        $this->product->score = $newScore;

        $this->dispatchSendMessage($response['screenshot_url'] ?? null);
    }

    protected function handleOutOfStock(): void
    {
        DB::table('products')->where('id', $this->product->id)->update([
            'status' => 'out_of_stock',
            'score' => 0,
            'flagged_at' => now(),
            'last_validated_at' => now(),
        ]);

        $this->handleRetry();
    }

    protected function handleUnavailable(): void
    {
        DB::table('products')->where('id', $this->product->id)->update([
            'status' => 'unavailable',
            'score' => 0,
            'flagged_at' => now(),
            'last_validated_at' => now(),
        ]);

        $this->handleRetry();
    }

    protected function handleRetry(): void
    {
        if ($this->retryCount >= 3) {
            Redis::del($this->lockKey);
            Log::info("Max re-selection attempts reached for channel {$this->socialIntegrationId}");
            return;
        }

        ProductSelectionJob::dispatch(
            socialIntegrationId: $this->socialIntegrationId,
            externalId: $this->externalId,
            creatorId: $this->creatorId,
            retryCount: $this->retryCount + 1,
            forcedCategoryId: $this->categoryId,
            lockAlreadyAcquired: true,
            excludedProductIds: [...$this->excludedProductIds, $this->product['id']]
        );
    }

    protected function handleFlaskError(): void
    {
        $currentAttempts = $this->attempts();

        if ($currentAttempts < 3) {
            $this->release(pow(2, $currentAttempts) * 10);
        } else {
            Redis::del($this->lockKey);
            $this->fail();
            Log::error("Flask /validate unreachable for channel {$this->socialIntegrationId}");
        }
    }

    protected function recalculateScore($discount, $commission, $rank): int
    {
        $maxCommissionQuery = DB::table('products')
            ->where('category_id', $this->categoryId)
            ->where('status', 'active')
            ->max(DB::raw('price_after * (commission / 100)'));

        $maxDiscountQuery = DB::table('products')
            ->where('category_id', $this->categoryId)
            ->where('status', 'active')
            ->max('discount_percentage');

        $maxRankQuery = DB::table('products')
            ->where('category_id', $this->categoryId)
            ->where('status', 'active')
            ->max('rank_in_vendor_category');

        $maxCommission = $maxCommissionQuery > 0 ? (float) $maxCommissionQuery : 1;
        $maxDiscount = $maxDiscountQuery > 0 ? (float) $maxDiscountQuery : 1;
        $maxRank = $maxRankQuery > 0 ? (float) $maxRankQuery : 1;

        $rank = $rank ?: $maxRank;

        $currentCommissionValue = $this->product->price_after * ($commission / 100);

        $scoreFloat = (
            (($currentCommissionValue) / $maxCommission) * 0.33 +
            ($discount / $maxDiscount) * 0.33 +
            (1 - ($rank - 1) / $maxRank) * 0.34
        );

        return (int) round($scoreFloat * 1000);
    }

    protected function dispatchSendMessage(?string $screenshotUrl): void
    {
        SendMessageToTelegramJob::dispatch(
            socialIntegrationId: $this->socialIntegrationId,
            externalId: $this->externalId,
            creatorId: $this->creatorId,
            product: $this->product,
            categoryId: $this->categoryId,
            trackId: $this->trackId,
            vendorSlug: $this->vendorSlug,
            lockKey: $this->lockKey,
            idempotencyKey: $this->idempotencyKey,
            screenshotUrl: $screenshotUrl
        );
    }
}
