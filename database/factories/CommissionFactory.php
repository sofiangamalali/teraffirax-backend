<?php

namespace Database\Factories;

use App\Models\Commission;
use App\Models\Creator;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        return [
            'creator_id' => Creator::factory(),
            'vendor_id' => null,
            'earned_at' => $this->faker->date(),
            'clicks_count' => $this->faker->randomNumber(3),
            'items_count' => $this->faker->randomNumber(2),
            'returned_count' => $this->faker->randomNumber(1),
            'revenue_amount' => $this->faker->randomFloat(2, 100, 10000),
            'commission_amount' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
