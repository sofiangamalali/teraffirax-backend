<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $egypt = Country::where('iso2', 'eg')->first();

        if (!$egypt) {
            $egypt = Country::updateOrCreate(
                ['iso2' => 'eg'],
                [
                    'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
                    'currency' => ['en' => 'EGP', 'ar' => 'ج.م'],
                    'timezone' => 'Africa/Cairo',
                    'phone_code' => '+20',
                    'phone_min_length' => 11,
                    'phone_max_length' => 11,
                    'icon' => 'https://flagcdn.com/eg.svg',
                    'is_active' => true,
                    'language' => 'ar',
                ]
            );
        }

        Vendor::updateOrCreate(
            ['domain' => 'https://www.amazon.eg'],
            [
                'name' => 'Amazon',
                'slug' => 'amazon',
                'country_id' => $egypt->id,
                'logo' => 'amazon.png',
                'commission' => 7.00,
                'commission_payout_days' => 90,
                'is_active' => true,
            ]
        );

        Vendor::updateOrCreate(
            ['domain' => 'https://www.noon.com'],
            [
                'name' => 'Noon',
                'slug' => 'noon',
                'country_id' => $egypt->id,
                'logo' => 'noon.png',
                'is_active' => false,
            ]
        );

        Vendor::updateOrCreate(
            ['domain' => 'https://www.jumia.com.eg'],
            [
                'name' => 'Jumia',
                'slug' => 'jumia',
                'country_id' => $egypt->id,
                'logo' => 'jumia.png',
                'is_active' => false,
            ]
        );
    }
}
