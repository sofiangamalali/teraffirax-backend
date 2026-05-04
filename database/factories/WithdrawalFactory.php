<?php

namespace Database\Factories;

use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
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
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'pending',
            'creator_withdraw_method_id' => \App\Models\CreatorWithdrawMethod::factory(),
        ];
    }
}
