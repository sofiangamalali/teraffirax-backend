<?php

namespace Tests\Unit\Services;

use App\DTOs\AdminLoginDTO;
use App\Models\Admin;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Services\AdminAuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AdminAuthServiceTest extends TestCase
{
    protected $repository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(AdminRepositoryInterface::class);
        $this->service = new AdminAuthService($this->repository);
    }

    /**
     * Test admin can login successfully when details are correct.
     */
    public function test_admin_can_login_successfully()
    {
        $dto = new AdminLoginDTO('admin@test.com', 'password123');

        $admin = Mockery::mock(Admin::class)->makePartial();
        $admin->email = 'admin@test.com';
        $admin->name = 'Admin User';
        $admin->password = Hash::make('password123');

        $this->repository->shouldReceive('findByEmail')
            ->once()
            ->with('admin@test.com')
            ->andReturn($admin);

        // Mock token generation
        $mockToken = (object) ['plainTextToken' => 'fake_access_token'];
        $mockRefreshToken = (object) ['plainTextToken' => 'fake_refresh_token'];

        $admin->shouldReceive('createToken')
            ->with('access_token', ['access'], Mockery::any())
            ->once()
            ->andReturn($mockToken);

        $admin->shouldReceive('createToken')
            ->with('refresh_token', ['refresh'], Mockery::any())
            ->once()
            ->andReturn($mockRefreshToken);

        $result = $this->service->login($dto);

        $this->assertEquals('fake_access_token', $result['access_token']);
        $this->assertEquals('fake_refresh_token', $result['refresh_token']);
        $this->assertEquals('admin@test.com', $result['email']);
        $this->assertEquals('Admin User', $result['name']);
    }

    /**
     * Test login throws validation exception with invalid credentials.
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        $dto = new AdminLoginDTO('admin@test.com', 'wrongpassword');

        $admin = Mockery::mock(Admin::class)->makePartial();
        $admin->email = 'admin@test.com';
        $admin->password = Hash::make('password123');

        $this->repository->shouldReceive('findByEmail')
            ->once()
            ->with('admin@test.com')
            ->andReturn($admin);

        $this->expectException(ValidationException::class);

        $this->service->login($dto);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
