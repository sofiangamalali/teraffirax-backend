<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\Category;
use App\Models\VendorCategory;
use App\Enums\VendorCategoryStatusEnum;
use Illuminate\Database\Seeder;

class VendorCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $amazon = Vendor::where('slug', 'amazon')->first();

        if (!$amazon) {
            $this->command->error("Amazon vendor not found. Please run VendorSeeder first.");
            return;
        }

        $platformCategories = Category::all()->keyBy(function ($c) {
            return $c->getTranslation('name', 'en');
        });

        $amazonCategories = [
            [
                'target_category_en' => "Men's Fashion",
                'external_id' => '21845147031',
                'external_name' => 'men',
                'commission' => 14.0
            ],
            [
                'target_category_en' => 'Electronics',
                'external_id' => '18018102031',
                'external_name' => 'electronics',
                'commission' => 2.0
            ],
            [
                'target_category_en' => "Women's Fashion",
                'external_id' => '21845145031',
                'external_name' => 'women',
                'commission' => 14.0
            ],
            [
                'target_category_en' => 'Home & Kitchen',
                'external_id' => '21863792031',
                'external_name' => 'home',
                'commission' => 5.0
            ],
            [
                'target_category_en' => 'Health & Personal Care',
                'external_id' => '18021875031',
                'external_name' => 'Health & Personal Care',
                'commission' => 5.0
            ],
            [
                'target_category_en' => 'Beauty & Cosmetics',
                'external_id' => '18017988031',
                'external_name' => 'Beauty',
                'commission' => 8.0
            ],
            [
                'target_category_en' => 'Automotive',
                'external_id' => '18017874031',
                'external_name' => 'Automotive',
                'commission' => 8.0
            ],
            [
                'target_category_en' => 'Supermarket & Grocery',
                'external_id' => '18020637031',
                'external_name' => 'Grocery',
                'commission' => 4.0
            ],
            [
                'target_category_en' => "Men's Shoes",
                'external_id' => '21845184031',
                'external_name' => 'men shoes',
                'commission' => 14.0
            ],
            [
                'target_category_en' => 'Headphones & Audio',
                'external_id' => '21832869031',
                'external_name' => 'headphones',
                'commission' => 5.0
            ],
            [
                'target_category_en' => 'Mobile Accessories',
                'external_id' => '21832886031',
                'external_name' => 'phones accessories',
                'commission' => 5.0
            ],
            [
                'target_category_en' => 'Mobiles & Tablets',
                'external_id' => '21832883031',
                'external_name' => 'mobiles',
                'commission' => 5.0
            ],
            [
                'target_category_en' => 'Computers & Laptops',
                'external_id' => '21832872031',
                'external_name' => 'computer',
                'commission' => 5.0
            ],
            [
                'target_category_en' => "Men's Clothing",
                'external_id' => '21845413031',
                'external_name' => 'men clothes',
                'commission' => 14.0
            ],
            [
                'target_category_en' => "Men's Bags",
                'external_id' => '21845183031',
                'external_name' => 'men bags',
                'commission' => 14.0
            ],
            [
                'target_category_en' => 'Watches',
                'external_id' => '21845180031',
                'external_name' => 'men watches',
                'commission' => 14.0
            ]
        ];



        foreach ($amazonCategories as $cat) {
            $targetCat = $platformCategories->get($cat['target_category_en']);
            if (!$targetCat) {
                if (isset($this->command)) {
                    $this->command->error("Category '{$cat['target_category_en']}' not found in categories table.");
                }
                continue;
            }

            VendorCategory::create([
                'vendor_id' => $amazon->id,
                'category_id' => $targetCat->id,
                'external_id' => $cat['external_id'],
                'external_name' => $cat['external_name'],
                'status' => VendorCategoryStatusEnum::ACTIVE,
                'commission' => $cat['commission'],
            ]);
        }

        if (isset($this->command)) {
            $this->command->info("Amazon vendor categories seeded successfully!");
        }
    }
}
