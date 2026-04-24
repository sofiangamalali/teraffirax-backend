<?php

namespace Tests\Unit;

use App\DTOs\VendorDTO;
use App\Models\Vendor;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use App\Services\VendorService;
use Mockery;
use Tests\TestCase;

class VendorServiceTest extends TestCase
{
    protected $repository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(VendorRepositoryInterface::class);
        $this->service = new VendorService($this->repository);
    }

    public function test_can_create_vendor()
    {
        $dto = new VendorDTO(name: 'Test Vendor');
        $vendorModel = new Vendor(['name' => 'Test Vendor']);
        
        $this->repository->shouldReceive('create')
            ->once()
            ->with(['name' => 'Test Vendor'])
            ->andReturn($vendorModel);

        $result = $this->service->createVendor($dto);

        $this->assertInstanceOf(Vendor::class, $result);
        $this->assertEquals('Test Vendor', $result->name);
    }

    public function test_can_get_vendor_by_id()
    {
        $vendor = new Vendor(['name' => 'Test Vendor']);
        $vendor->id = 1;
        
        $this->repository->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($vendor);

        $result = $this->service->getVendorById(1);

        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test Vendor', $result->name);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
