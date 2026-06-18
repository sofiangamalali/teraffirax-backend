<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => \App\Models\Vendor::factory(),
            'category_id' => \App\Models\Category::factory(),
            'external_id' => $this->faker->unique()->uuid,
            'name' => $this->faker->sentence,
            'url' => $this->faker->url,
            'price_after' => $this->faker->randomFloat(2, 5, 1000),
            'status' => 'active',
            'score' => $this->faker->numberBetween(0, 100),
        ];
    }
}
