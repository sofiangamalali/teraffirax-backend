<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\Product;
use App\Services\TrackingLinkService;
use App\Services\TemplateRendererService;
use App\Services\TelegramPublishingService;
use App\DTOs\TrackingLinkDTO;

class SendMessageToTelegramJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 90;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

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
        public ?string $screenshotUrl = null
    ) {
        $this->onQueue('telegram-publishing');
    }

    public function handle(
        TrackingLinkService $trackingLinkService,
        TemplateRendererService $templateRenderer,
        TelegramPublishingService $publishingService
    ): void {
        $start = microtime(true);

        $idempotencyLockKey = "telegram_post_idempotency:" . $this->idempotencyKey;
        // Try to acquire a lock/flag for this idempotency key for 1 hour (3600 seconds)
        // $isUnique = Redis::set($idempotencyLockKey, '1', 'EX', 3600, 'NX');
        // if (!$isUnique) {
        //     Log::channel('posting')->warning("Duplicate post blocked by idempotency check", [
        //         'idempotency_key' => $this->idempotencyKey,
        //         'product_id' => $this->product->id
        //     ]);
        //     Redis::del($this->lockKey);
        //     return;
        // }

        Log::channel('posting')->info("DIAG STEP 1: passed idempotency check", [
            'product_id' => $this->product->id
        ]);

        // $shouldSkip = false;
        // DB::transaction(function () use (&$shouldSkip, $idempotencyLockKey, $publishingService) {
        //     $exists = $publishingService->existsRecentPostForProduct(
        //         $this->product->id,
        //         $this->socialIntegrationId,
        //         3
        //     );

        //     if ($exists) {
        //         Redis::del($this->lockKey);
        //         Redis::del($idempotencyLockKey);
        //         $shouldSkip = true;
        //         return;
        //     }
        // });

        // if ($shouldSkip) {
        //     return;
        // }

        Log::channel('posting')->info("DIAG STEP 2: passed DB duplicate check", [
            'product_id' => $this->product->id
        ]);

        $trackingDto = new TrackingLinkDTO(
            creator_id: $this->creatorId,
            product_id: $this->product->id,
            redirect_url: $this->product->url
        );
        $trackingLink = $trackingLinkService->createTrackingLink($this->creatorId, $trackingDto);

        Log::channel('posting')->info("DIAG STEP 3: tracking link created", [
            'product_id' => $this->product->id
        ]);

        $affiliateLink = route('redirect', ['slug' => $trackingLink->slug]);
        $affiliateLink = preg_replace('/^https?:\/\//i', '', $affiliateLink);

        $template = $publishingService->findDefaultTemplate($this->creatorId, $this->socialIntegrationId);

        if (!$template) {
            Log::channel('posting')->warning("DIAG STEP 4: template is null", [
                'product_id' => $this->product->id
            ]);
            $message = "No default content template found for creator {$this->creatorId} on channel {$this->socialIntegrationId}.";
            Log::channel('posting')->error($message);
            Redis::del($this->lockKey);
            $this->fail(new \RuntimeException($message));
            Log::channel('posting')->info('SendMessageToTelegramJob finished', [
                'duration_ms' => round((microtime(true) - $start) * 1000),
                'product_id' => $this->product->id,
            ]);
            return;
        }

        Log::channel('posting')->info("DIAG STEP 4: template found", [
            'template_id' => $template->id,
            'product_id' => $this->product->id
        ]);

        $caption = $templateRenderer->render($template->content, [
            'product_name' => $this->product->name,
            'product_price' => floor($this->product->price_after),
            'discount' => $this->product->discount_percentage ?? 0,
            'link' => $affiliateLink,
        ]);

        $flaskUrl = config('services.flask.url') . '/send-telegram';
        $botToken = config('services.telegram.posting_bot_token');

        if (!$botToken) {
            $message = "TELEGRAM_POSTING_BOT_TOKEN is missing.";
            Log::channel('posting')->error($message);
            Redis::del($this->lockKey);
            $this->fail(new \RuntimeException($message));
            Log::channel('posting')->info('SendMessageToTelegramJob finished', [
                'duration_ms' => round((microtime(true) - $start) * 1000),
                'product_id' => $this->product->id,
            ]);
            return;
        }

        try {
            $response = Http::withHeaders([
                'Connection' => 'keep-alive',
            ])->withOptions([
                'curl' => [
                    CURLOPT_TCP_KEEPALIVE => 1,
                ]
            ])->timeout(80)->post($flaskUrl, [
                'chat_id' => $this->externalId,
                'caption' => $caption,
                'image_url' => $this->screenshotUrl,
                'bot_token' => $botToken,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'timeout')) {
                Log::channel('posting')->error("Flask post request timed out. Keeping idempotency lock active to prevent duplicate posting.", [
                    'idempotency_key' => $this->idempotencyKey,
                    'error' => $e->getMessage()
                ]);
            } else {
                Redis::del($idempotencyLockKey);
            }
            throw $e;
        } catch (\Throwable $e) {
            Redis::del($idempotencyLockKey);
            throw $e;
        }

        Log::channel('posting')->info("DIAG STEP 5: Flask responded", [
            'product_id' => $this->product->id,
            'http_status' => $response->status(),
            'body' => substr($response->body(), 0, 500)
        ]);

        if ($response->failed()) {
            Redis::del($idempotencyLockKey);
            throw new \RuntimeException("Flask API returned error status: " . $response->status());
        }

        if (($response['status'] ?? '') === 'rate_limited') {
            Log::channel('posting')->info("DIAG STEP: rate_limited branch taken", [
                'product_id' => $this->product->id
            ]);
            Redis::del($idempotencyLockKey);
            $this->release($response['retry_after'] ?? 60);
            Log::channel('posting')->info('SendMessageToTelegramJob finished', [
                'duration_ms' => round((microtime(true) - $start) * 1000),
                'product_id' => $this->product->id,
            ]);
            return;
        }

        if (($response['status'] ?? '') === 'rejected') {
            Log::channel('posting')->info("DIAG STEP: rejected branch taken", [
                'product_id' => $this->product->id
            ]);
            $publishingService->deactivateIntegration($this->socialIntegrationId);
            Redis::del($this->lockKey);
            Redis::del($idempotencyLockKey);
            $this->fail(new \RuntimeException($response['reason'] ?? 'Rejected by Telegram'));
            Log::channel('posting')->info('SendMessageToTelegramJob finished', [
                'duration_ms' => round((microtime(true) - $start) * 1000),
                'product_id' => $this->product->id,
            ]);
            return;
        }

        if (($response['status'] ?? '') === 'error') {
            Log::channel('posting')->info("DIAG STEP: error branch taken", [
                'product_id' => $this->product->id
            ]);
            Redis::del($idempotencyLockKey);
            throw new \RuntimeException($response['reason'] ?? 'Unknown Flask scraper error');
        }

        Log::channel('posting')->info("DIAG STEP: success branch taken", [
            'product_id' => $this->product->id
        ]);

        $publishingService->recordProductPost($this->socialIntegrationId, $this->product->id);

        Log::channel('posting')->info("Successfully posted product to Telegram channel.", [
            'social_integration_id' => $this->socialIntegrationId,
            'product_id' => $this->product->id,
            'external_id' => $this->externalId,
        ]);

        $publishingService->updateCategoryLastPostedAt($this->creatorId, $this->categoryId);
        Redis::del($this->lockKey);

        Log::channel('posting')->info('SendMessageToTelegramJob finished', [
            'duration_ms' => round((microtime(true) - $start) * 1000),
            'product_id' => $this->product->id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Redis::del($this->lockKey);
        Redis::del("telegram_post_idempotency:" . $this->idempotencyKey);
        Log::channel('posting')->error('SendMessageToTelegramJob permanently failed', [
            'social_integration_id' => $this->socialIntegrationId,
            'product_id' => $this->product->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
