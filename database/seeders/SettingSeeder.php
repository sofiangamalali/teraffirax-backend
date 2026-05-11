<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'withdrawal.min_amount'],
            [
                'value' => '500',
                'type' => 'float',
                'group' => 'financial',
            ]
        );
    }
}
