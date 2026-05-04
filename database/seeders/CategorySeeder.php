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
            ['en' => 'Electronics', 'ar' => 'إلكترونيات'],
            ['en' => 'Fashion & Apparel', 'ar' => 'أزياء وملابس'],
            ['en' => 'Beauty & Personal Care', 'ar' => 'جمال وعناية شخصية'],
            ['en' => 'Home & Kitchen', 'ar' => 'المنزل والمطبخ'],
            ['en' => 'Health & Wellness', 'ar' => 'صحة وعافية'],
            ['en' => 'Sports & Outdoors', 'ar' => 'رياضة وأنشطة خارجية'],
            ['en' => 'Books & Stationery', 'ar' => 'كتب وأدوات مكتبية'],
            ['en' => 'Toys & Games', 'ar' => 'ألعاب'],
            ['en' => 'Automotive', 'ar' => 'سيارات'],
            ['en' => 'Pet Supplies', 'ar' => 'مستلزمات الحيوانات الأليفة'],
            ['en' => 'Digital Services', 'ar' => 'خدمات رقمية'],
            ['en' => 'Software & Apps', 'ar' => 'برمجيات وتطبيقات'],
            ['en' => 'Education & Courses', 'ar' => 'تعليم ودورات'],
            ['en' => 'Real Estate', 'ar' => 'عقارات'],
            ['en' => 'Food & Beverages', 'ar' => 'أغذية ومشروبات'],
        ];

        foreach ($categories as $category) {
            // Find existing by English name if already JSON or old string
            $existing = Category::all()->filter(function($c) use ($category) {
                return $c->getTranslation('name', 'en') === $category['en'];
            })->first();

            if ($existing) {
                $existing->update(['name' => $category]);
            } else {
                Category::create(['name' => $category]);
            }
        }
    }
}
