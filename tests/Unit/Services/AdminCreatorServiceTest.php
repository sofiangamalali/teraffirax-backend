<?php

namespace Tests\Unit\Services;

use App\DTOs\AdminSocialIntegrationDTO;
use App\Models\Creator;
use App\Models\SocialIntegration;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\SocialIntegrationRepositoryInterface;
use App\Repositories\Interfaces\CreatorVendorTrackingRepositoryInterface;
use App\Services\AdminCreatorService;
use App\Services\TelegramService;
use App\Enums\PlatformEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class AdminCreatorServiceTest extends TestCase
{
    protected $creatorRepository;
    protected $socialRepository;
    protected $trackingRepository;
    protected $telegramService;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->socialRepository = Mockery::mock(SocialIntegrationRepositoryInterface::class);
        $this->trackingRepository = Mockery::mock(CreatorVendorTrackingRepositoryInterface::class);
        $this->telegramService = Mockery::mock(TelegramService::class);
        $this->service = new AdminCreatorService(
            $this->creatorRepository,
            $this->socialRepository,
            $this->trackingRepository,
            $this->telegramService
        );
    }

    /**
     * Test paginated creators retrieval.
     */
    public function test_can_get_paginated_creators()
    {
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->creatorRepository->shouldReceive('paginate')
            ->once()
            ->with(15)
            ->andReturn($mockPaginator);

        $result = $this->service->getPaginatedCreators(15);

        $this->assertSame($mockPaginator, $result);
    }

    /**
     * Test active status toggle of a creator.
     */
    public function test_can_toggle_creator_active()
    {
        $creator = new Creator(['is_active' => false]);

        $this->creatorRepository->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($creator);

        $this->creatorRepository->shouldReceive('update')
            ->once()
            ->with(1, ['is_active' => true])
            ->andReturn($creator);

        $result = $this->service->toggleCreatorActive(1);

        $this->assertInstanceOf(Creator::class, $result);
    }

    /**
     * Test managing social integrations.
     */
    public function test_can_manage_creator_social_integrations()
    {
        $creator = new Creator();
        $dto = new AdminSocialIntegrationDTO(
            creator_id: 1,
            platform: 'telegram',
            type: 'channel',
            status: 'active',
            name: 'Channel',
            username: 'channel_user'
        );
        $integration = new SocialIntegration();

        // 1. Ensure creator exists
        $this->creatorRepository->shouldReceive('findOrFail')
            ->times(3)
            ->with(1)
            ->andReturn($creator);

        // 2. Create integration
        $this->socialRepository->shouldReceive('create')
            ->once()
            ->with($dto->toArray())
            ->andReturn($integration);

        $resultCreate = $this->service->createSocialIntegration($dto);
        $this->assertSame($integration, $resultCreate);

        // 3. Update integration
        $this->socialRepository->shouldReceive('find')
            ->once()
            ->with(2)
            ->andReturn($integration);
        $integration->creator_id = 1;
        $integration->platform = PlatformEnum::TELEGRAM;

        $this->telegramService->shouldReceive('resolveExternalIdFromUsername')
            ->once()
            ->with('channel_user')
            ->andReturn('-123456');

        $expectedData = array_merge($dto->toArray(), ['external_id' => '-123456']);

        $this->socialRepository->shouldReceive('update')
            ->once()
            ->with($integration, $expectedData)
            ->andReturn(true);

        $resultUpdate = $this->service->updateSocialIntegration(1, 2, $dto);
        $this->assertSame($integration, $resultUpdate);

        // 4. Delete integration
        $this->socialRepository->shouldReceive('find')
            ->once()
            ->with(2)
            ->andReturn($integration);

        $this->socialRepository->shouldReceive('delete')
            ->once()
            ->with($integration)
            ->andReturn(true);

        $resultDelete = $this->service->deleteSocialIntegration(1, 2);
        $this->assertTrue($resultDelete);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
