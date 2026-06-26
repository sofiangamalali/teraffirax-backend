<?php

namespace Tests\Feature;

use App\Models\{Creator, Product, SocialIntegration, Vendor, CreatorVendorTracking};
use App\Jobs\SendMessageToTelegramJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SendMessageToTelegramJobTest extends TestCase
{
    use RefreshDatabase;

    protected $creator;
    protected $product;
    protected $integration;
    protected $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = Creator::factory()->create();
        $this->vendor = Vendor::factory()->create([
            'slug' => 'amazon',
            'domain' => 'https://www.amazon.eg',
            'is_active' => true
        ]);
        
        CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $this->vendor->id,
            'track_id' => 'mytag-21',
            'is_active' => true,
        ]);

        $this->product = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
            'url' => 'https://www.amazon.eg/dp/B0FMR7H8FT',
        ]);

        $this->integration = SocialIntegration::create([
            'creator_id' => $this->creator->id,
            'platform' => 'telegram',
            'type' => 'channel',
            'status' => 'active',
            'external_id' => '-100123456789',
            'name' => 'My Telegram Channel',
        ]);

        DB::table('content_templates')->insert([
            'creator_id' => $this->creator->id,
            'name' => 'Default',
            'content' => 'Check this out: {product_name} at {product_price} EGP. Link: {link}',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_blocks_duplicate_executions_via_idempotency_key()
    {
        // Mock Redis: simulate that the idempotency key lock is ALREADY acquired (returns false)
        Redis::shouldReceive('set')
            ->withArgs(function ($key, $val, $ex, $time, $nx) {
                return str_contains($key, 'telegram_post_idempotency:mock-idempotency-key');
            })
            ->once()
            ->andReturn(false);

        // When duplicate blocked, lockKey should be deleted
        Redis::shouldReceive('del')
            ->with('mock-lock-key')
            ->once();

        Http::fake();

        $job = new SendMessageToTelegramJob(
            socialIntegrationId: $this->integration->id,
            externalId: $this->integration->external_id,
            creatorId: $this->creator->id,
            product: $this->product,
            categoryId: $this->product->category_id,
            trackId: 'mytag-21',
            vendorSlug: 'amazon',
            lockKey: 'mock-lock-key',
            idempotencyKey: 'mock-idempotency-key'
        );

        $job->handle(
            app(\App\Services\TrackingLinkService::class),
            app(\App\Services\TemplateRendererService::class),
            app(\App\Services\TelegramPublishingService::class)
        );

        // Assert that no message was sent to Flask
        Http::assertNothingSent();
    }

    public function test_releases_idempotency_key_on_normal_connection_failures()
    {
        // Mock Redis: simulate that the idempotency key lock is acquired (returns true)
        Redis::shouldReceive('set')
            ->withArgs(function ($key, $val, $ex, $time, $nx) {
                return str_contains($key, 'telegram_post_idempotency:mock-idempotency-key');
            })
            ->once()
            ->andReturn(true);

        // Assert that the idempotency key is deleted on normal failure (Connection refused)
        Redis::shouldReceive('del')
            ->with('telegram_post_idempotency:mock-idempotency-key')
            ->once();

        // Http client throws a connection error (not timeout)
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException("Connection refused");
        });

        $job = new SendMessageToTelegramJob(
            socialIntegrationId: $this->integration->id,
            externalId: $this->integration->external_id,
            creatorId: $this->creator->id,
            product: $this->product,
            categoryId: $this->product->category_id,
            trackId: 'mytag-21',
            vendorSlug: 'amazon',
            lockKey: 'mock-lock-key',
            idempotencyKey: 'mock-idempotency-key'
        );

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);

        $job->handle(
            app(\App\Services\TrackingLinkService::class),
            app(\App\Services\TemplateRendererService::class),
            app(\App\Services\TelegramPublishingService::class)
        );
    }

    public function test_retains_idempotency_key_on_request_timeouts()
    {
        // Mock Redis: simulate that the idempotency key lock is acquired (returns true)
        Redis::shouldReceive('set')
            ->withArgs(function ($key, $val, $ex, $time, $nx) {
                return str_contains($key, 'telegram_post_idempotency:mock-idempotency-key');
            })
            ->once()
            ->andReturn(true);

        // We expect Redis::del to NOT be called for the idempotency key, so it remains locked
        Redis::shouldReceive('del')
            ->with('telegram_post_idempotency:mock-idempotency-key')
            ->never();

        // Http client throws a connection timeout error
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException("Connection timed out after 30000 milliseconds");
        });

        $job = new SendMessageToTelegramJob(
            socialIntegrationId: $this->integration->id,
            externalId: $this->integration->external_id,
            creatorId: $this->creator->id,
            product: $this->product,
            categoryId: $this->product->category_id,
            trackId: 'mytag-21',
            vendorSlug: 'amazon',
            lockKey: 'mock-lock-key',
            idempotencyKey: 'mock-idempotency-key'
        );

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);

        $job->handle(
            app(\App\Services\TrackingLinkService::class),
            app(\App\Services\TemplateRendererService::class),
            app(\App\Services\TelegramPublishingService::class)
        );
    }
}
