<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'slug' => $this->faker->unique()->slug,
            'country_id' => \App\Models\Country::factory(),
            'domain' => $this->faker->domainName,
            'logo' => $this->faker->imageUrl(),
            'commission' => $this->faker->randomFloat(2, 1, 15),
            'is_active' => true,
        ];
    }
}
