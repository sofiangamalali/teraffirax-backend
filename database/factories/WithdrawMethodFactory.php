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
            'name' => $this->faker->word,
            'logo' => null,
            'type' => $this->faker->randomElement(['bank', 'wallet', 'other']),
            'details' => ['info' => $this->faker->sentence],
            'is_active' => true,
        ];
    }
}
