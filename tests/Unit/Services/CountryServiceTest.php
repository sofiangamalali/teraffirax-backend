<?php

namespace Tests\Unit\Services;

use App\DTOs\CountryDTO;
use App\Models\Country;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Services\CountryService;
use Mockery;
use Tests\TestCase;

class CountryServiceTest extends TestCase
{
    protected CountryService $service;
    protected $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(CountryRepositoryInterface::class);
        $this->service = new CountryService($this->repositoryMock);
    }

    public function test_can_create_country(): void
    {
        $dto = new CountryDTO(name: 'Egypt', iso2: 'EG', phone_code: '+20', phone_min_length: 10, phone_max_length: 10);
        $country = new Country(['name' => 'Egypt']);

        $this->repositoryMock->shouldReceive('create')
            ->once()
            ->with($dto->toArray())
            ->andReturn($country);

        $result = $this->service->createCountry($dto);

        $this->assertEquals($country, $result);
    }

    public function test_can_update_country(): void
    {
        $dto = new CountryDTO(name: 'Updated Name');
        $country = new Country(['name' => 'Updated Name']);

        $this->repositoryMock->shouldReceive('update')
            ->once()
            ->with(1, $dto->toArray())
            ->andReturn($country);

        $result = $this->service->updateCountry(1, $dto);

        $this->assertEquals($country, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
