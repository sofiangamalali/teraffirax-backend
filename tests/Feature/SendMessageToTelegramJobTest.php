<?php

namespace Tests\Feature;

use App\Models\{Creator, Product, SocialIntegration, Vendor, CreatorVendorTracking};
use App\Jobs\SendMessageToTelegramJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use RuntimeException;
use App\Enums\SocialIntegrationStatusEnum;

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

    public function test_deactivates_integration_on_bot_kicked_rejection()
    {
        Redis::shouldReceive('set')
            ->withArgs(function ($key, $val, $ex, $time, $nx) {
                return str_contains($key, 'telegram_post_idempotency:mock-idempotency-key');
            })
            ->once()
            ->andReturn(true);

        Redis::shouldReceive('del')
            ->with('mock-lock-key')
            ->once();

        Redis::shouldReceive('del')
            ->with('telegram_post_idempotency:mock-idempotency-key')
            ->once();

        Http::fake([
            '*' => Http::response([
                'success' => false,
                'data' => null,
                'error' => 'Telegram API Error (sendPhoto): Forbidden: bot was kicked from the channel chat'
            ], 500)
        ]);

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

        $this->assertEquals(SocialIntegrationStatusEnum::ACTIVE, $this->integration->fresh()->status);

        $job->handle(
            app(\App\Services\TrackingLinkService::class),
            app(\App\Services\TemplateRendererService::class),
            app(\App\Services\TelegramPublishingService::class)
        );

        $this->assertEquals(SocialIntegrationStatusEnum::INACTIVE, $this->integration->fresh()->status);
    }

    public function test_deactivates_integration_on_chat_not_found_rejection()
    {
        Redis::shouldReceive('set')
            ->withArgs(function ($key, $val, $ex, $time, $nx) {
                return str_contains($key, 'telegram_post_idempotency:mock-idempotency-key');
            })
            ->once()
            ->andReturn(true);

        Redis::shouldReceive('del')
            ->with('mock-lock-key')
            ->once();

        Redis::shouldReceive('del')
            ->with('telegram_post_idempotency:mock-idempotency-key')
            ->once();

        Http::fake([
            '*' => Http::response([
                'success' => false,
                'data' => null,
                'error' => 'Telegram API Error (sendPhoto): Bad Request: chat not found'
            ], 500)
        ]);

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

        $this->assertEquals(SocialIntegrationStatusEnum::ACTIVE, $this->integration->fresh()->status);

        $job->handle(
            app(\App\Services\TrackingLinkService::class),
            app(\App\Services\TemplateRendererService::class),
            app(\App\Services\TelegramPublishingService::class)
        );

        $this->assertEquals(SocialIntegrationStatusEnum::INACTIVE, $this->integration->fresh()->status);
    }

    public function test_fails_permanently_without_retry_on_missing_photo_error()
    {
        Redis::shouldReceive('set')
            ->withArgs(function ($key, $val, $ex, $time, $nx) {
                return str_contains($key, 'telegram_post_idempotency:mock-idempotency-key');
            })
            ->once()
            ->andReturn(true);

        Redis::shouldReceive('del')
            ->with('mock-lock-key')
            ->once();

        Redis::shouldReceive('del')
            ->with('telegram_post_idempotency:mock-idempotency-key')
            ->once();

        Http::fake([
            '*' => Http::response([
                'success' => false,
                'data' => null,
                'error' => 'Photo file not found: /www/wwwroot/some_photo.jpg'
            ], 500)
        ]);

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

        $this->assertEquals(SocialIntegrationStatusEnum::ACTIVE, $this->integration->fresh()->status);

        $job->handle(
            app(\App\Services\TrackingLinkService::class),
            app(\App\Services\TemplateRendererService::class),
            app(\App\Services\TelegramPublishingService::class)
        );

        // Integration should NOT be deactivated (it's a photo issue, not bot kick)
        $this->assertEquals(SocialIntegrationStatusEnum::ACTIVE, $this->integration->fresh()->status);
    }

    public function test_escapes_html_entities_in_product_name_for_telegram_message()
    {
        Redis::shouldReceive('set')->andReturn(true);
        Redis::shouldReceive('del')->andReturn(true);

        // Update product name to include HTML tags and special characters
        $this->product->update([
            'name' => '<Brand> "Adapter" & Cable 16"'
        ]);

        // Setup a template with double curly braces
        DB::table('content_templates')
            ->where('creator_id', $this->creator->id)
            ->update([
                'content' => 'Check this out: {{product_name}} at {{product_price}} EGP. Link: {{link}}'
            ]);

        // Fake successful response from Flask
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['message_id' => 12345],
                'error' => null
            ], 200)
        ]);

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

        Http::assertSent(function ($request) {
            // Check that the product name was escaped correctly (quotes should NOT be escaped, but tags and ampersands must be)
            $expectedCaption = 'Check this out: &lt;Brand&gt; "Adapter" &amp; Cable 16"';
            return str_contains($request['caption'], $expectedCaption);
        });
    }
}


