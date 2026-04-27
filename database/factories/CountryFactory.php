<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->country,
            'iso2' => $this->faker->unique()->countryCode,
            'phone_code' => $this->faker->randomNumber(3),
            'phone_min_length' => 8,
            'phone_max_length' => 15,
            'is_active' => true,
        ];
    }
}
