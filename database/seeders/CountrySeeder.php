<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::updateOrCreate(
            ['iso2' => 'eg'],
            [
                'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
                'phone_code' => '+20',
                'phone_min_length' => 11,
                'phone_max_length' => 11,
                'icon' => 'https://flagcdn.com/eg.svg',
                'is_active' => true,
            ]
        );
    }
}
