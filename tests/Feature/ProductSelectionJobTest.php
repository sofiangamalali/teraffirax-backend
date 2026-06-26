<?php

namespace Tests\Feature;

use App\Models\{Category, Creator, Product, SocialIntegration, CreatorVendorTracking, Vendor, TelegramProductPost};
use App\Jobs\{ProductSelectionJob, ValidateProductDataJob};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductSelectionJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Http::fake([
            '*health' => \Illuminate\Support\Facades\Http::response(['status' => 'ok'], 200),
        ]);
    }

    public function test_can_select_product_and_dispatch_validation()
    {
        Queue::fake();
        Redis::shouldReceive('set')->once()->andReturn(true);

        // 1. Create a Creator
        $creator = Creator::factory()->create();

        // 2. Create Category and map to Creator
        $category = Category::factory()->create();
        $creator->categories()->attach($category->id, ['last_posted_at' => now()->subHours(5)]);

        // 3. Create Vendor and map to Creator with tracking ID
        $vendor = Vendor::factory()->create(['slug' => 'amazon', 'is_active' => true]);
        CreatorVendorTracking::create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendor->id,
            'track_id' => 'amzn-tag-21',
            'is_active' => true,
        ]);

        // 4. Create Product
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'status' => 'active',
            'score' => 100,
        ]);

        // 5. Create Social Integration
        $integration = SocialIntegration::create([
            'creator_id' => $creator->id,
            'platform' => 'telegram',
            'type' => 'channel',
            'status' => 'active',
            'external_id' => '-100123456789',
            'name' => 'My Telegram Channel',
        ]);

        // 6. Dispatch the Job
        $job = new ProductSelectionJob(
            socialIntegrationId: $integration->id,
            externalId: $integration->external_id,
            creatorId: $creator->id
        );
        app()->call([$job, 'handle']);

        // 7. Assert ValidateProductDataJob was pushed with correct parameters
        Queue::assertPushed(ValidateProductDataJob::class, function ($job) use ($product) {
            return $job->product->id === $product->id;
        });
    }

    public function test_excludes_recently_posted_products()
    {
        Queue::fake();
        Redis::shouldReceive('set')->once()->andReturn(true);

        $creator = Creator::factory()->create();
        $category = Category::factory()->create();
        $creator->categories()->attach($category->id, ['last_posted_at' => now()->subHours(5)]);

        $vendor = Vendor::factory()->create(['slug' => 'amazon', 'is_active' => true]);
        CreatorVendorTracking::create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendor->id,
            'track_id' => 'amzn-tag-21',
            'is_active' => true,
        ]);

        // Product 1 (higher score but recently posted to this channel)
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'status' => 'active',
            'score' => 200,
        ]);

        // Product 2 (lower score, not posted)
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'status' => 'active',
            'score' => 100,
        ]);

        $integration = SocialIntegration::create([
            'creator_id' => $creator->id,
            'platform' => 'telegram',
            'type' => 'channel',
            'status' => 'active',
            'external_id' => '-100123456789',
            'name' => 'My Telegram Channel',
        ]);

        // Mark product1 as posted to this channel within last 24h
        TelegramProductPost::create([
            'social_integration_id' => $integration->id,
            'product_id' => $product1->id,
            'post_status' => 'posted',
            'posted_at' => now()->subHours(1),
        ]);

        $job = new ProductSelectionJob(
            socialIntegrationId: $integration->id,
            externalId: $integration->external_id,
            creatorId: $creator->id
        );
        app()->call([$job, 'handle']);

        // Product 2 should be selected because Product 1 was excluded
        Queue::assertPushed(ValidateProductDataJob::class, function ($job) use ($product2) {
            return $job->product->id === $product2->id;
        });
    }

    public function test_falls_back_to_other_categories_if_empty()
    {
        Queue::fake();
        Redis::shouldReceive('set')->once()->andReturn(true);

        $creator = Creator::factory()->create();

        // Category 1 (empty of products, oldest last_posted_at)
        $category1 = Category::factory()->create();
        $creator->categories()->attach($category1->id, ['last_posted_at' => now()->subHours(10)]);

        // Category 2 (has active product, newer last_posted_at)
        $category2 = Category::factory()->create();
        $creator->categories()->attach($category2->id, ['last_posted_at' => now()->subHours(5)]);

        $vendor = Vendor::factory()->create(['slug' => 'amazon', 'is_active' => true]);
        CreatorVendorTracking::create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendor->id,
            'track_id' => 'amzn-tag-21',
            'is_active' => true,
        ]);

        // Product is in Category 2
        $product = Product::factory()->create([
            'category_id' => $category2->id,
            'vendor_id' => $vendor->id,
            'status' => 'active',
            'score' => 100,
        ]);

        $integration = SocialIntegration::create([
            'creator_id' => $creator->id,
            'platform' => 'telegram',
            'type' => 'channel',
            'status' => 'active',
            'external_id' => '-100123456789',
            'name' => 'My Telegram Channel',
        ]);

        $job = new ProductSelectionJob(
            socialIntegrationId: $integration->id,
            externalId: $integration->external_id,
            creatorId: $creator->id
        );
        app()->call([$job, 'handle']);

        // Product should be successfully selected from Category 2 even though Category 1 was rotated first
        Queue::assertPushed(ValidateProductDataJob::class, function ($job) use ($product) {
            return $job->product->id === $product->id;
        });
    }
}
