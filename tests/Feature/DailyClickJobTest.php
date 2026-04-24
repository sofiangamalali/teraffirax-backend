<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\Product;
use App\Models\TrackingLink;
use App\Models\DailyClick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class DailyClickJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_daily_clicks_logic()
    {
        // 1. Setup Creator and some tracking links with clicks
        $creator = Creator::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $link1 = TrackingLink::factory()->create([
            'creator_id' => $creator->id,
            'product_id' => $product1->id,
            'clicks_count' => 10
        ]);

        $link2 = TrackingLink::factory()->create([
            'creator_id' => $creator->id,
            'product_id' => $product2->id,
            'clicks_count' => 5
        ]);

        // Total lifetime clicks = 15

        // 2. Run command for the first time (e.g. at end of day 1)
        Artisan::call('calculate:daily-clicks', ['date' => Carbon::now()->toDateString()]);

        $this->assertDatabaseHas('daily_clicks', [
            'creator_id' => $creator->id,
            'clicks_count' => 15,
            'date' => Carbon::now()->toDateString()
        ]);

        // 3. Add more clicks on day 2
        $link1->increment('clicks_count', 5); // total now 20
        
        // 4. Run command for day 2
        Artisan::call('calculate:daily-clicks', ['date' => Carbon::now()->addDay()->toDateString()]);

        // Day 2 should record (20 total) - (15 already recorded) = 5 clicks
        $this->assertDatabaseHas('daily_clicks', [
            'creator_id' => $creator->id,
            'clicks_count' => 5,
            'date' => Carbon::now()->addDay()->toDateString()
        ]);
    }
}
