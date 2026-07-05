<?php

namespace Tests\Feature;

use App\Models\ProductBlockedKeyword;
use Database\Seeders\ProductBlockedKeywordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBlockedKeywordTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_seed_blocked_keywords(): void
    {
        $this->seed(ProductBlockedKeywordSeeder::class);

        $this->assertDatabaseHas('product_blocked_keywords', [
            'keyword' => 'replica',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('product_blocked_keywords', [
            'keyword' => 'fake',
            'is_active' => true,
        ]);
    }

    public function test_can_create_and_retrieve_blocked_keyword(): void
    {
        $keyword = ProductBlockedKeyword::create([
            'keyword' => 'unauthorized_item',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('product_blocked_keywords', [
            'keyword' => 'unauthorized_item',
            'is_active' => false,
        ]);

        $retrieved = ProductBlockedKeyword::where('keyword', 'unauthorized_item')->first();
        $this->assertNotNull($retrieved);
        $this->assertFalse($retrieved->is_active);
    }
}
