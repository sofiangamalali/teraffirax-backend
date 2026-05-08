<?php

namespace Database\Factories;

use App\Models\CreatorWithdrawMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreatorWithdrawMethod>
 */
class CreatorWithdrawMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'creator_id' => \App\Models\Creator::factory(),
            'withdraw_method_id' => \App\Models\WithdrawMethod::factory(),
            'is_default' => $this->faker->boolean,
        ];
    }
}
