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
use App\DTOs\TrackingLinkDTO;

class SendMessageToTelegramJob implements ShouldQueue
{
    use Queueable;

    public string $queue = 'telegram-publishing';

    public int $tries = 3;
    public int $timeout = 30;

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
        string $idempotencyKey,
        public ?string $screenshotUrl = null
    ) {
        //
    }

    public function handle(
        TrackingLinkService $trackingLinkService,
        TemplateRendererService $templateRenderer
    ): void {

        $shouldSkip = false;
        DB::transaction(function () use (&$shouldSkip) {
            $exists = DB::table('telegram_product_posts')
                ->where('product_id', $this->product['id'])
                ->where('post_status', 'posted')
                ->where('posted_at', '>', now()->subHours(3))
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                Redis::del($this->lockKey);
                $shouldSkip = true;
                return;
            }
        });

        if ($shouldSkip) {
            return;
        }

        $trackingDto = new TrackingLinkDTO(
            creator_id: $this->creatorId,
            product_id: $this->product->id,
            redirect_url: $this->product->url
        );
        $trackingLink = $trackingLinkService->createTrackingLink($this->creatorId, $trackingDto);
        $affiliateLink = route('redirect', ['slug' => $trackingLink->slug]);

        $template = DB::table('content_templates')
            ->where('creator_id', $this->creatorId)
            ->where(function ($q) {
                $q->where('social_integration_id', $this->socialIntegrationId)
                    ->orWhereNull('social_integration_id');
            })
            ->where('is_default', true)
            ->first();

        if (!$template) {
            Log::channel('posting')->error("No default content template found for creator {$this->creatorId} on channel {$this->socialIntegrationId}.");
            Redis::del($this->lockKey);
            $this->fail();
            return;
        }

        $caption = $templateRenderer->render($template->content, [
            'product_name'  => $this->product->name,
            'product_price' => $this->product->price_after,
            'discount'      => $this->product->discount_percentage ?? 0,
            'link'          => $affiliateLink,
        ]);

        $flaskUrl = config('services.flask.url') . '/send-telegram';
        $botToken = config('services.telegram.posting_bot_token');

        if (!$botToken) {
            Log::channel('posting')->error("TELEGRAM_POSTING_BOT_TOKEN is missing.");
            Redis::del($this->lockKey);
            $this->fail();
            return;
        }

        $response = Http::timeout(30)->post($flaskUrl, [
            'chat_id'   => $this->externalId,
            'caption'   => $caption,
            'image_url' => $this->screenshotUrl,
            'bot_token' => $botToken,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Flask API returned error status: " . $response->status());
        }

        if (($response['status'] ?? '') === 'rate_limited') {
            $this->release($response['retry_after'] ?? 60);
            return;
        }

        if (($response['status'] ?? '') === 'rejected') {
            DB::table('social_integrations')
                ->where('id', $this->socialIntegrationId)
                ->update(['status' => 'inactive']);
            Redis::del($this->lockKey);
            $this->fail(new \RuntimeException($response['reason'] ?? 'Rejected by Telegram'));
            return;
        }

        if (($response['status'] ?? '') === 'error') {
            throw new \RuntimeException($response['reason'] ?? 'Unknown Flask scraper error');
        }

        DB::table('telegram_product_posts')->insert([
            'social_integration_id' => $this->socialIntegrationId,
            'product_id'            => $this->product['id'],
            'post_status'           => 'posted',
            'posted_at'             => now(),
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        Log::channel('posting')->info("Successfully posted product to Telegram channel.", [
            'social_integration_id' => $this->socialIntegrationId,
            'product_id'            => $this->product['id'],
            'external_id'           => $this->externalId,
        ]);

        DB::table('category_creator')
            ->where('creator_id', $this->creatorId)
            ->where('category_id', $this->categoryId)
            ->update(['last_posted_at' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        Redis::del($this->lockKey);
        Log::channel('posting')->error('SendMessageToTelegramJob permanently failed', [
            'social_integration_id' => $this->socialIntegrationId,
            'product_id'            => $this->product['id'],
            'error'                 => $exception->getMessage(),
        ]);
    }
}
