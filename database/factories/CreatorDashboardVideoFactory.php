<?php

namespace Database\Factories;

use App\Models\CreatorDashboardVideo;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreatorDashboardVideoFactory extends Factory
{
    protected $model = CreatorDashboardVideo::class;

    public function definition(): array
    {
        return [
            'video_url' => 'https://www.tiktok.com/@faker/video/' . $this->faker->randomNumber(9),
            'thumbnail_url' => 'https://picsum.photos/200/300',
            'country_id' => Country::factory(),
            'views_count' => $this->faker->numberBetween(100, 100000),
            'likes_count' => $this->faker->numberBetween(10, 5000),
            'comments_count' => $this->faker->numberBetween(5, 1000),
            'shares_count' => $this->faker->numberBetween(2, 500),
            'total_orders' => $this->faker->numberBetween(1, 100),
            'total_commission' => $this->faker->randomFloat(2, 10, 2000),
        ];
    }
}
