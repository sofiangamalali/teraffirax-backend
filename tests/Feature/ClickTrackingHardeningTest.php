<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Models\Creator;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\TrackingLink;
use App\Models\DailyClick;
use Carbon\Carbon;
use Tests\TestCase;

class ClickTrackingHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_backfills_missing_days_sequentially()
    {
        $creator = Creator::factory()->create(['created_at' => Carbon::parse('2026-04-01')]);
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        
        // Target date April 5th. Gap: 4-01, 4-02, 4-03, 4-04, 4-05.
        $targetDate = Carbon::parse('2026-04-05');
        
        TrackingLink::factory()->create([
            'creator_id' => $creator->id,
            'product_id' => $product->id,
            'clicks_count' => 100
        ]);

        Artisan::call('calculate:daily-clicks', ['date' => $targetDate->toDateString()]);

        // Expect records for 1st, 2nd, 3rd, 4th (0 clicks) and 5th (100 clicks)
        $this->assertDatabaseHas('daily_clicks', ['creator_id' => $creator->id, 'date' => '2026-04-01', 'clicks_count' => 0]);
        $this->assertDatabaseHas('daily_clicks', ['creator_id' => $creator->id, 'date' => '2026-04-05', 'clicks_count' => 100]);
        
        $count = DailyClick::where('creator_id', $creator->id)->count();
        $this->assertEquals(6, $count); // 3-31 (baseline) + 4-01 to 4-05
    }

    public function test_it_prevents_concurrent_global_job_runs()
    {
        Cache::lock("daily-clicks-job", 600)->get();

        $output = Artisan::call('calculate:daily-clicks');
        
        $this->assertEquals(0, $output); // Command exits
    }

    public function test_it_enforces_idempotency_via_unique_constraint()
    {
        $creator = Creator::factory()->create();
        
        DailyClick::create([
            'creator_id' => $creator->id,
            'date' => '2026-04-12',
            'clicks_count' => 5,
            'total_snapshot' => 5
        ]);

        Artisan::call('calculate:daily-clicks', ['date' => '2026-04-12']);

        $count = DailyClick::where('creator_id', $creator->id)->where('date', '2026-04-12')->count();
        $this->assertEquals(1, $count); 
    }

    public function test_it_logs_failures_to_database()
    {
        $creator = Creator::factory()->create();
        
        $this->mock(\App\Services\DailyClickService::class, function ($mock) {
            $mock->shouldReceive('calculateAndRecordDailyClicks')->andThrow(new \Exception("Test Error"));
        });

        Artisan::call('calculate:daily-clicks', ['date' => '2026-04-12']);

        $this->assertDatabaseHas('failed_daily_click_jobs', [
            'creator_id' => $creator->id,
            'error_message' => 'Test Error'
        ]);
    }
}
