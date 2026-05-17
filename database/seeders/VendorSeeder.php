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
            // Fallback if country seeder hasn't run yet
            $egypt = Country::create([
                'iso2' => 'eg',
                'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
                'phone_code' => '+20',
                'phone_min_length' => 11,
                'phone_max_length' => 11,
                'icon' => 'https://flagcdn.com/eg.svg',
                'is_active' => true,
            ]);
        }

        Vendor::updateOrCreate(
            ['domain' => 'https://www.amazon.eg'],
            [
                'name' => 'Amazon',
                'country_id' => $egypt->id,
                'logo' => 'amazon.png',
                'is_active' => true,
            ]
        );

        Vendor::updateOrCreate(
            ['domain' => 'https://www.noon.com'],
            [
                'name' => 'Noon',
                'country_id' => $egypt->id,
                'logo' => 'noon.png',
                'is_active' => false,
            ]
        );

        Vendor::updateOrCreate(
            ['domain' => 'https://www.jumia.com.eg'],
            [
                'name' => 'Jumia',
                'country_id' => $egypt->id,
                'logo' => 'jumia.png',
                'is_active' => false,
            ]
        );
    }
}
