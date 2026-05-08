<?php

namespace Database\Factories;

use App\Models\WithdrawMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WithdrawMethod>
 */
class WithdrawMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => \App\Models\Country::factory(),
            'name' => ['en' => $this->faker->word, 'ar' => $this->faker->word],
            'slug' => $this->faker->slug,
            'logo' => null,
            'is_active' => true,
        ];
    }
}
