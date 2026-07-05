<?php

namespace Database\Seeders;

use App\Models\ProductBlockedKeyword;
use Illuminate\Database\Seeder;

class ProductBlockedKeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keywords = [
            'جراب',
            'سوار',
            'أساور',
            'كابل',
            'نوكيا',
            'واقي',
            'واقي ذكري',
            'واقي شاشة',
            'شاشة حماية',
            'دلـع موبايلك',
            'موبايل ثنائي الشريحة',
            'موبايل ثنائى الشريحة',
            'هاتف شاومي',
        ];

        foreach ($keywords as $keyword) {
            ProductBlockedKeyword::updateOrCreate(
                ['keyword' => $keyword],
                ['is_active' => true]
            );
        }
    }
}
