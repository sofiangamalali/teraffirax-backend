<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\Product;
use App\Services\TelegramPublishingService;
use Exception;
use RuntimeException;

class ValidateProductDataJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    protected TelegramPublishingService $publishingService;

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
        $this->onQueue('telegram-validation');
    }

    public function handle(TelegramPublishingService $publishingService): void
    {
        $start = microtime(true);
        $this->publishingService = $publishingService;
        $flaskUrl = env('PYTHON_PRODUCT_SCRAP_URL', 'http://127.0.0.1:5000') . '/validate';

        try {
            $response = Http::withHeaders([
                'Connection' => 'keep-alive',
            ])->withOptions([
                'curl' => [
                    CURLOPT_TCP_KEEPALIVE => 1,
                ]
            ])->timeout(90)->post($flaskUrl, [
                'url' => $this->product->url,
                'vendor_slug' => $this->vendorSlug,
                'stored_price' => (float) $this->product->price_after,
                'stored_discount' => (float) $this->product->discount_percentage,
                'stored_commission' => (float) $this->product->commission
            ]);

            if ($response->failed()) {
                $responseBody = $response->body();
                $errorDetails = json_decode($responseBody, true)['error'] ?? json_decode($responseBody, true)['message'] ?? substr($responseBody, 0, 200);
                $this->handleFlaskError(new RuntimeException("Flask validation failed with status " . $response->status() . " (Details: " . $errorDetails . ")"));
                Log::channel('posting')->info('ValidateProductDataJob finished', [
                    'duration_ms' => round((microtime(true) - $start) * 1000),
                    'product_id' => $this->product->id,
                ]);
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
            } elseif ($status === 'error') {
                $reason = $data['reason'] ?? 'unknown_reason';
                $this->handleFlaskError(new RuntimeException("Flask validation failed: " . $reason));
            } else {
                $this->handleFlaskError(new RuntimeException("Unknown status returned from Flask: " . $status));
            }

            Log::channel('posting')->info('ValidateProductDataJob finished', [
                'duration_ms' => round((microtime(true) - $start) * 1000),
                'product_id' => $this->product->id,
            ]);

        } catch (ConnectionException $e) {
            $this->handleFlaskError($e);
            Log::channel('posting')->info('ValidateProductDataJob finished', [
                'duration_ms' => round((microtime(true) - $start) * 1000),
                'product_id' => $this->product->id,
            ]);
        } catch (Exception $e) {
            $this->handleFlaskError($e);
            Log::channel('posting')->info('ValidateProductDataJob finished', [
                'duration_ms' => round((microtime(true) - $start) * 1000),
                'product_id' => $this->product->id,
            ]);
        }
    }

    protected function handleSame(array $response): void
    {
        Log::channel('posting')->info("Product price validated: SAME", ['product_id' => $this->product->id]);

        $this->publishingService->updateProductData($this->product->id, [
            'last_validated_at' => now(),
        ]);

        $this->dispatchSendMessage($response['screenshot_url'] ?? null);
    }

    protected function handleHigher(array $response): void
    {
        Log::channel('posting')->info("Product price validated: HIGHER", ['product_id' => $this->product->id, 'new_price' => $response['current_price']]);

        $commissionPercent = (float) ($this->product->vendorCategory?->commission ?? 0);
        $commissionValue = ((float) $response['current_price']) * ($commissionPercent / 100);

        $newScore = (int) round(\App\Services\ScoreCalculator::calculate(
            price: (float) $response['current_price'],
            discountPercentage: (float) ($response['current_discount'] ?? 0),
            commissionPercent: $commissionPercent,
            rank: (int) ($this->product->rank_in_vendor_category ?? 1)
        ) * 1000);

        $this->publishingService->updateProductData($this->product->id, [
            'discount_percentage' => $response['current_discount'],
            'price_after' => $response['current_price'],
            'commission' => $commissionValue,
            'score' => $newScore,
            'last_validated_at' => now(),
        ]);

        $this->product->discount_percentage = $response['current_discount'];
        $this->product->price_after = $response['current_price'];
        $this->product->commission = $commissionValue;
        $this->product->score = $newScore;

        $this->dispatchSendMessage($response['screenshot_url'] ?? null);
    }

    protected function handleLower(array $response): void
    {
        Log::channel('posting')->info("Product price validated: LOWER", ['product_id' => $this->product->id, 'new_price' => $response['current_price']]);

        $commissionPercent = (float) ($this->product->vendorCategory?->commission ?? 0);
        $commissionValue = ((float) $response['current_price']) * ($commissionPercent / 100);

        $newScore = (int) round(\App\Services\ScoreCalculator::calculate(
            price: (float) $response['current_price'],
            discountPercentage: (float) ($response['current_discount'] ?? 0),
            commissionPercent: $commissionPercent,
            rank: (int) ($this->product->rank_in_vendor_category ?? 1)
        ) * 1000);

        $this->publishingService->updateProductData($this->product->id, [
            'discount_percentage' => $response['current_discount'],
            'price_after' => $response['current_price'],
            'commission' => $commissionValue,
            'score' => $newScore,
            'last_validated_at' => now(),
        ]);

        $this->product->discount_percentage = $response['current_discount'];
        $this->product->price_after = $response['current_price'];
        $this->product->commission = $commissionValue;
        $this->product->score = $newScore;

        $this->dispatchSendMessage($response['screenshot_url'] ?? null);
    }

    protected function handleOutOfStock(): void
    {
        Log::channel('posting')->warning("Product price validated: OUT OF STOCK", ['product_id' => $this->product->id]);

        $this->publishingService->updateProductData($this->product->id, [
            'status' => 'out_of_stock',
            'score' => 0,
            'flagged_at' => now(),
            'last_validated_at' => now(),
        ]);

        $this->handleRetry();
    }

    protected function handleUnavailable(): void
    {
        Log::channel('posting')->warning("Product price validated: UNAVAILABLE", ['product_id' => $this->product->id]);

        $this->publishingService->updateProductData($this->product->id, [
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
            Log::channel('posting')->info("Max re-selection attempts reached for channel {$this->socialIntegrationId}");
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

    protected function handleFlaskError(?\Throwable $exception = null): void
    {
        $currentAttempts = $this->attempts();

        if ($currentAttempts < 3) {
            $this->release(pow(2, $currentAttempts) * 10);
        } else {
            Redis::del($this->lockKey);

            $message = "Flask /validate unreachable for channel {$this->socialIntegrationId}";
            if ($exception) {
                $message .= ": " . $exception->getMessage();
            }

            Log::channel('posting')->error($message);

            $isSoftFail = $exception && (
                str_contains($exception->getMessage(), 'scrape_failed') ||
                str_contains($exception->getMessage(), 'parse_error') ||
                str_contains($exception->getMessage(), 'Product title element not found')
            );

            if ($isSoftFail) {
                Log::channel('posting')->warning("ValidateProductDataJob soft failed due to scrape block/parse error: " . $exception->getMessage());
                return;
            }

            $this->fail($exception ?: new RuntimeException($message));
        }
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
