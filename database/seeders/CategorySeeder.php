<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Electronics',
            'Fashion & Apparel',
            'Beauty & Personal Care',
            'Home & Kitchen',
            'Health & Wellness',
            'Sports & Outdoors',
            'Books & Stationery',
            'Toys & Games',
            'Automotive',
            'Pet Supplies',
            'Digital Services',
            'Software & Apps',
            'Education & Courses',
            'Real Estate',
            'Food & Beverages',
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category],
                ['name' => $category]
            );
        }
    }
}
