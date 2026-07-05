<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Enums\VendorCategoryStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScraperProductUpsertTest extends TestCase
{
    use RefreshDatabase;

    protected Vendor $vendor;
    protected Category $category;
    protected VendorCategory $vendorCategory;
    protected string $token = 'test-scraper-token';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.scraper.token' => $this->token]);

        $this->vendor = Vendor::factory()->create([
            'slug' => 'amazon',
            'is_active' => true,
        ]);

        $this->category = Category::factory()->create();

        $this->vendorCategory = VendorCategory::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'external_id' => '12345',
            'external_name' => 'Test Vendor Category',
            'status' => VendorCategoryStatusEnum::ACTIVE,
            'commission' => 10,
        ]);
    }

    protected function withScraperAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function test_upsert_creates_new_product_with_is_blocked_false()
    {
        $payload = [
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vendor_category_id' => $this->vendorCategory->id,
            'products' => [
                [
                    'external_id' => 'prod-new',
                    'name' => 'New Product',
                    'url' => 'https://example.com/new',
                    'price_before' => 150,
                    'price_after' => 100,
                    'discount_percentage' => 33.3,
                    'rank_in_vendor_category' => 1,
                ]
            ]
        ];

        $response = $this->withScraperAuth()
            ->postJson('/api/scraper/products/upsert', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'external_id' => 'prod-new',
            'is_blocked' => false,
        ]);
    }

    public function test_upsert_preserves_blocked_status_for_existing_product()
    {
        // 1. Create a blocked product in the database
        $blockedProduct = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vendor_category_id' => $this->vendorCategory->id,
            'external_id' => 'prod-blocked',
            'name' => 'Blocked Product Original Name',
            'url' => 'https://example.com/blocked',
            'is_blocked' => true,
            'price_after' => 100,
            'status' => 'active',
        ]);

        $payload = [
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vendor_category_id' => $this->vendorCategory->id,
            'products' => [
                [
                    'external_id' => 'prod-blocked',
                    'name' => 'Blocked Product New Name',
                    'url' => 'https://example.com/blocked',
                    'price_before' => 120,
                    'price_after' => 80, // Price updated
                    'discount_percentage' => 33.3,
                    'rank_in_vendor_category' => 2,
                ]
            ]
        ];

        $response = $this->withScraperAuth()
            ->postJson('/api/scraper/products/upsert', $payload);

        $response->assertStatus(201);

        // 2. Assert that the product details are updated but the is_blocked status remains true
        $this->assertDatabaseHas('products', [
            'id' => $blockedProduct->id,
            'external_id' => 'prod-blocked',
            'name' => 'Blocked Product New Name',
            'price_after' => 80.00,
            'is_blocked' => true, // Still true
        ]);
    }
}
