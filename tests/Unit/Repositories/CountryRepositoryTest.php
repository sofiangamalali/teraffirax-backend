<?php

namespace Tests\Unit\Repositories;

use App\Models\Country;
use App\Repositories\Eloquent\CountryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CountryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CountryRepository(new Country());
    }

    public function test_can_create_country(): void
    {
        $data = [
            'name'             => 'Egypt',
            'iso2'             => 'EG',
            'phone_code'       => '+20',
            'phone_min_length' => 10,
            'phone_max_length' => 10,
            'is_active'        => true,
        ];

        $country = $this->repository->create($data);

        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('Egypt', $country->name);
        $this->assertDatabaseHas('countries', ['iso2' => 'EG']);
    }

    public function test_can_find_country(): void
    {
        $country = Country::factory()->create();

        $found = $this->repository->find($country->id);

        $this->assertNotNull($found);
        $this->assertEquals($country->id, $found->id);
    }

    public function test_can_update_country(): void
    {
        $country = Country::factory()->create(['name' => 'Old Name']);

        $updated = $this->repository->update($country->id, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('countries', ['id' => $country->id, 'name' => 'New Name']);
    }

    public function test_can_delete_country(): void
    {
        $country = Country::factory()->create();

        $result = $this->repository->delete($country->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}
