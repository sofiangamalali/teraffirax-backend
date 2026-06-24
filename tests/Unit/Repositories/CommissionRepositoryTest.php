<?php

namespace Tests\Unit\Repositories;

use App\Models\Commission;
use App\Models\Creator;
use App\Models\Vendor;
use App\Repositories\Eloquent\CommissionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CommissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CommissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = \Illuminate\Support\Facades\DB::connection();
        if ($connection->getDriverName() === 'sqlite') {
            $pdo = $connection->getPdo();
            $pdo->sqliteCreateFunction('NOW', function () {
                return date('Y-m-d H:i:s');
            });
            $pdo->sqliteCreateFunction('DATEDIFF', function ($date1, $date2) {
                if (!$date1 || !$date2) {
                    return 0;
                }
                $d1 = new \DateTime($date1);
                $d2 = new \DateTime($date2);
                $interval = $d2->diff($d1);
                return (int) $interval->format('%r%a');
            });
        }

        $this->repository = new CommissionRepository(new Commission());
    }

    public function test_get_unmatured_commissions_by_creator_uses_fallback_of_90_days_when_vendor_or_days_null(): void
    {
        $creator = Creator::factory()->create();

        // 1. Commission earned 91 days ago (should be unmatured and ready to mature)
        Commission::factory()->create([
            'creator_id' => $creator->id,
            'vendor_id' => null,
            'earned_at' => Carbon::now()->subDays(91)->toDateString(),
            'calculated_at' => Carbon::now(),
            'matured_at' => null,
            'commission_amount' => 100.00,
        ]);

        // 2. Commission earned 89 days ago (should NOT be unmatured yet)
        Commission::factory()->create([
            'creator_id' => $creator->id,
            'vendor_id' => null,
            'earned_at' => Carbon::now()->subDays(89)->toDateString(),
            'calculated_at' => Carbon::now(),
            'matured_at' => null,
            'commission_amount' => 50.00,
        ]);

        $results = $this->repository->getUnmaturedCommissionsByCreator();

        $this->assertCount(1, $results);
        $this->assertArrayHasKey($creator->id, $results);
        $commissions = $results[$creator->id];
        $this->assertCount(1, $commissions);
        $this->assertEquals(100.00, (float) $commissions->first()->commission_amount);
    }

    public function test_get_unmatured_commissions_by_creator_uses_dynamic_vendor_payout_days(): void
    {
        $creator = Creator::factory()->create();

        // Vendor A has 45 days payout policy
        $vendorA = Vendor::factory()->create([
            'commission_payout_days' => 45,
        ]);

        // Vendor B has 120 days payout policy
        $vendorB = Vendor::factory()->create([
            'commission_payout_days' => 120,
        ]);

        // 1. Commission for Vendor A, earned 50 days ago (should mature because 50 > 45)
        $c1 = Commission::factory()->create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendorA->id,
            'earned_at' => Carbon::now()->subDays(50)->toDateString(),
            'calculated_at' => Carbon::now(),
            'matured_at' => null,
            'commission_amount' => 10.00,
        ]);

        // 2. Commission for Vendor A, earned 40 days ago (should NOT mature because 40 < 45)
        $c2 = Commission::factory()->create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendorA->id,
            'earned_at' => Carbon::now()->subDays(40)->toDateString(),
            'calculated_at' => Carbon::now(),
            'matured_at' => null,
            'commission_amount' => 20.00,
        ]);

        // 3. Commission for Vendor B, earned 100 days ago (should NOT mature because 100 < 120)
        $c3 = Commission::factory()->create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendorB->id,
            'earned_at' => Carbon::now()->subDays(100)->toDateString(),
            'calculated_at' => Carbon::now(),
            'matured_at' => null,
            'commission_amount' => 30.00,
        ]);

        // 4. Commission for Vendor B, earned 125 days ago (should mature because 125 > 120)
        $c4 = Commission::factory()->create([
            'creator_id' => $creator->id,
            'vendor_id' => $vendorB->id,
            'earned_at' => Carbon::now()->subDays(125)->toDateString(),
            'calculated_at' => Carbon::now(),
            'matured_at' => null,
            'commission_amount' => 40.00,
        ]);

        $results = $this->repository->getUnmaturedCommissionsByCreator();

        $this->assertCount(1, $results);
        $commissions = $results[$creator->id];
        $this->assertCount(2, $commissions); // Should only be $c1 (10.00) and $c4 (40.00)
        
        $amounts = array_map('floatval', $commissions->pluck('commission_amount')->toArray());
        $this->assertContains(10.00, $amounts);
        $this->assertContains(40.00, $amounts);
        $this->assertNotContains(20.00, $amounts);
        $this->assertNotContains(30.00, $amounts);
    }
}
