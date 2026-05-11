<?php

namespace Tests\Unit\Services;

use App\DTOs\SettingDTO;
use App\Models\Setting;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class SettingServiceTest extends TestCase
{
    protected $repository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(SettingRepositoryInterface::class);
        $this->service = new SettingService($this->repository);
    }

    public function test_can_get_setting_from_cache()
    {
        Cache::shouldReceive('rememberForever')
            ->once()
            ->andReturn(10); // Simulated casted value

        $result = $this->service->get('withdrawal.min_amount');

        $this->assertEquals(10, $result);
    }

    public function test_can_set_setting()
    {
        $dto = new SettingDTO('site.name', 'My App', 'string', 'general');
        $settingModel = new Setting(['key' => 'site.name', 'value' => 'My App', 'type' => 'string']);

        // Mock get for old value
        Cache::shouldReceive('rememberForever')
            ->once()
            ->andReturn(null);

        $this->repository->shouldReceive('set')
            ->once()
            ->andReturn($settingModel);

        Cache::shouldReceive('forget')
            ->once()
            ->with('setting:site.name');

        $result = $this->service->set($dto);

        $this->assertInstanceOf(Setting::class, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
