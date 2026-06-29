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
            ['en' => "Men's Fashion", 'ar' => 'أزياء رجالي'],
            ['en' => "Women's Fashion", 'ar' => 'أزياء حريمي'],
            ['en' => "Men's Shoes", 'ar' => 'أحذية رجالي'],
            ['en' => "Men's Clothing", 'ar' => 'ملابس رجالي'],
            ['en' => "Men's Bags", 'ar' => 'حقائب رجالي'],
            ['en' => 'Watches', 'ar' => 'ساعات'],
            ['en' => 'Electronics', 'ar' => 'إلكترونيات'],
            ['en' => 'Headphones & Audio', 'ar' => 'سماعات وصوتيات'],
            ['en' => 'Mobile Accessories', 'ar' => 'إكسسوارات الموبايل'],
            ['en' => 'Mobiles & Tablets', 'ar' => 'موبايلات وتابلت'],
            ['en' => 'Computers & Laptops', 'ar' => 'كمبيوتر ولاب توب'],
            ['en' => 'Home & Kitchen', 'ar' => 'المنزل والمطبخ'],
            ['en' => 'Health & Personal Care', 'ar' => 'صحة وعناية شخصية'],
            ['en' => 'Beauty & Cosmetics', 'ar' => 'جمال ومستحضرات تجميل'],
            ['en' => 'Automotive', 'ar' => 'مستلزمات سيارات'],
            ['en' => 'Supermarket & Grocery', 'ar' => 'سوبرماركت وبقالة'],
        ];

        foreach ($categories as $category) {
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
