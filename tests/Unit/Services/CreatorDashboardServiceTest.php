<?php

namespace Tests\Unit\Services;

use App\DTOs\CreatorDashboardDTO;
use App\Models\{Creator, Country, Commission};
use App\Repositories\Interfaces\{CreatorRepositoryInterface, CreatorDashboardRepositoryInterface};
use App\Services\CreatorDashboardService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatorDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CreatorDashboardService $service;
    protected $creatorRepoMock;
    protected $dashboardRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creatorRepoMock = Mockery::mock(CreatorRepositoryInterface::class);
        $this->dashboardRepoMock = Mockery::mock(CreatorDashboardRepositoryInterface::class);
        $this->service = new CreatorDashboardService($this->creatorRepoMock, $this->dashboardRepoMock);
    }

    public function test_get_wallet_data(): void
    {
        $creatorId = 1;
        $country = new Country(['name' => 'Egypt', 'currency' => 'EGP']);
        $creator = new Creator([
            'id' => $creatorId,
            'pending_balance' => 100.50,
            'available_balance' => 200.00,
            'withdrawn_balance' => 50.00,
        ]);
        $creator->setRelation('country', $country);

        $this->creatorRepoMock->shouldReceive('findOrFail')
            ->once()
            ->with($creatorId)
            ->andReturn($creator);

        $result = $this->service->getWalletData($creatorId);

        $this->assertEquals(100.50, $result['pending_balance']);
        $this->assertEquals(200.00, $result['available_balance']);
        $this->assertEquals(50.00, $result['withdrawn_balance']);
        $this->assertEquals('EGP', $result['currency']);
    }

    public function test_get_graph_data_daily_grouping(): void
    {
        $creatorId = 1;
        $country = new Country(['name' => 'Egypt', 'currency' => 'EGP']);
        $creator = new Creator([
            'id' => $creatorId,
        ]);
        $creator->setRelation('country', $country);

        // Filter last 30 days
        $dto = new CreatorDashboardDTO(days: 30);
        $startDate = $dto->getStartDate();
        $endDate = $dto->getEndDate();

        $commissions = new Collection([
            new Commission([
                'earned_at' => Carbon::parse('2026-05-17'),
                'clicks_count' => 100,
                'items_count' => 10,
                'returned_count' => 1,
                'commission_amount' => 50.00,
            ]),
            new Commission([
                'earned_at' => Carbon::parse('2026-05-16'),
                'clicks_count' => 50,
                'items_count' => 5,
                'returned_count' => 0,
                'commission_amount' => 25.00,
            ]),
        ]);

        $this->creatorRepoMock->shouldReceive('findOrFail')
            ->once()
            ->with($creatorId)
            ->andReturn($creator);

        $this->dashboardRepoMock->shouldReceive('getCommissionData')
            ->once()
            ->with($creatorId, $startDate, $endDate, null)
            ->andReturn($commissions);

        $result = $this->service->getGraphData($creatorId, $dto);

        // Assert graph is grouped by days
        $this->assertCount(2, $result['graph']);
        $this->assertEquals('2026-05-17', $result['graph'][0]['date']);
        $this->assertEquals(50.00, $result['graph'][0]['commission']);
        $this->assertEquals(100, $result['graph'][0]['clicks']);
        $this->assertEquals(10, $result['graph'][0]['orders']);
        $this->assertEquals('EGP', $result['graph'][0]['currency']);

        // Assert summary footer
        $this->assertEquals(150, $result['summary']['clicks']);
        $this->assertEquals(15, $result['summary']['orders']);
        $this->assertEquals(10.0, $result['summary']['conversion_rate']); // (15/150)*100
        $this->assertEquals(15, $result['summary']['shipped_items']);
        $this->assertEquals(1, $result['summary']['returned_items']);
        $this->assertEquals(75.00, $result['summary']['commission']);
        $this->assertEquals('EGP', $result['summary']['currency']);
    }

    public function test_get_graph_data_monthly_grouping(): void
    {
        $creatorId = 1;
        $country = new Country(['name' => 'Egypt', 'currency' => 'EGP']);
        $creator = new Creator([
            'id' => $creatorId,
        ]);
        $creator->setRelation('country', $country);

        // Filter last 120 days (exceeds 3 months)
        $dto = new CreatorDashboardDTO(days: 120);
        $startDate = $dto->getStartDate();
        $endDate = $dto->getEndDate();

        $commissions = new Collection([
            new Commission([
                'earned_at' => Carbon::parse('2026-05-17'),
                'clicks_count' => 10,
                'items_count' => 1,
                'returned_count' => 0,
                'commission_amount' => 5.00,
            ]),
            new Commission([
                'earned_at' => Carbon::parse('2026-04-10'),
                'clicks_count' => 20,
                'items_count' => 2,
                'returned_count' => 0,
                'commission_amount' => 10.00,
            ]),
        ]);

        $this->creatorRepoMock->shouldReceive('findOrFail')
            ->once()
            ->with($creatorId)
            ->andReturn($creator);

        $this->dashboardRepoMock->shouldReceive('getCommissionData')
            ->once()
            ->with($creatorId, $startDate, $endDate, null)
            ->andReturn($commissions);

        $result = $this->service->getGraphData($creatorId, $dto);

        // Assert graph is grouped by months (Y-m)
        $this->assertCount(2, $result['graph']);
        $this->assertEquals('2026-05', $result['graph'][0]['date']);
        $this->assertEquals('2026-04', $result['graph'][1]['date']);
        $this->assertEquals(5.00, $result['graph'][0]['commission']);
        $this->assertEquals(10.00, $result['graph'][1]['commission']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
