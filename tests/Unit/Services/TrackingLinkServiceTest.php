<?php

namespace Tests\Unit\Services;

use App\DTOs\TrackingLinkDTO;
use App\Models\Product;
use App\Models\TrackingLink;
use App\Models\Vendor;
use App\Models\CreatorVendorTrackingId;
use App\Repositories\Interfaces\CreatorVendorTrackingIdRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TrackingLinkRepositoryInterface;
use App\Services\AmazonTrackingService;
use App\Services\SlugService;
use App\Services\TrackingLinkService;
use Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class TrackingLinkServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $linkRepo;
    protected $slugService;
    protected $creatorVendorTrackingRepo;
    protected $productRepo;
    protected $amazonTrackingService;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->linkRepo = Mockery::mock(TrackingLinkRepositoryInterface::class);
        $this->slugService = Mockery::mock(SlugService::class);
        $this->creatorVendorTrackingRepo = Mockery::mock(CreatorVendorTrackingIdRepositoryInterface::class);
        $this->productRepo = Mockery::mock(ProductRepositoryInterface::class);
        $this->amazonTrackingService = Mockery::mock(AmazonTrackingService::class);

        $this->service = new TrackingLinkService(
            $this->linkRepo,
            $this->slugService,
            $this->creatorVendorTrackingRepo,
            $this->productRepo,
            $this->amazonTrackingService
        );
    }

    public function test_create_tracking_link_success(): void
    {
        $dto = new TrackingLinkDTO(creator_id: 1, product_id: 1);

        $vendor = new Vendor();
        $vendor->id = 1;
        $vendor->domain = "https://www.amazon.eg";

        $product = new Product();
        $product->id = 1;
        $product->vendor_id = 1;
        $product->product_url = "https://www.amazon.eg/dp/B0FK2QMC7V";
        $product->setRelation('vendor', $vendor);

        $trackingRecord = new CreatorVendorTrackingId();
        $trackingRecord->tracking_id = "dealsegypt21-21";

        $this->linkRepo->shouldReceive('findByCreatorAndProduct')
            ->with(1, 1)
            ->andReturn(null);

        $this->productRepo->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($product);

        $this->creatorVendorTrackingRepo->shouldReceive('findByCreatorAndVendor')
            ->with(1, 1)
            ->andReturn($trackingRecord);

        $this->amazonTrackingService->shouldReceive('appendTrackingId')
            ->with("https://www.amazon.eg/dp/B0FK2QMC7V", "dealsegypt21-21")
            ->andReturn("https://www.amazon.eg/dp/B0FK2QMC7V?tag=dealsegypt21-21");

        $mockLink = Mockery::mock(TrackingLink::class);
        $mockLink->shouldReceive('getAttribute')->with('id')->andReturn(10);

        $this->linkRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockLink);

        $this->slugService->shouldReceive('generateSlug')
            ->with(10)
            ->andReturn('slug123');

        $this->linkRepo->shouldReceive('update')
            ->with(10, ['slug' => 'slug123'])
            ->andReturn($mockLink);

        // We need to use database transactions or mock DB facade if we use it.
        // In the service we used DB::transaction. Mockery cannot easily mock DB::transaction unless we mock the facade.
        // Let's assume we can run it or we might need to mock DB facade.
        // To keep it simple, I'll mock the DB facade.
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->service->createTrackingLink($dto);

        $this->assertInstanceOf(TrackingLink::class, $result);
    }
}
