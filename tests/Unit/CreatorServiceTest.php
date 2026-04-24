<?php

namespace Tests\Unit;

use App\DTOs\CreatorDTO;
use App\Models\Creator;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Services\CreatorService;
use Mockery;
use Tests\TestCase;

class CreatorServiceTest extends TestCase
{
    protected $repository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->service = new CreatorService($this->repository);
    }

    public function test_can_create_creator()
    {
        $dtoData = [
            'name' => 'John Doe',
            'phone' => '123456789',
            'email' => 'john@example.com',
            'password' => 'password',
            'gender' => 'male'
        ];
        $dto = new CreatorDTO(...$dtoData);
        
        $creatorModel = new Creator($dtoData);
        
        $this->repository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($dtoData) {
                return $data['name'] === $dtoData['name'] && $data['email'] === $dtoData['email'];
            }))
            ->andReturn($creatorModel);

        $result = $this->service->createCreator($dto);

        $this->assertInstanceOf(Creator::class, $result);
        $this->assertEquals('John Doe', $result->name);
    }

    public function test_can_get_creator_by_id()
    {
        $creator = new Creator(['name' => 'John Doe']);
        $creator->id = 1;
        
        $this->repository->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($creator);

        $result = $this->service->getCreatorById(1);

        $this->assertEquals(1, $result->id);
        $this->assertEquals('John Doe', $result->name);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
