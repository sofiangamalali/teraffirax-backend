<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\WithdrawMethod;
use Illuminate\Database\Seeder;

class WithdrawMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $egypt = Country::where('iso2', 'eg')->first();
        if (!$egypt) {
            $this->command->error('Egypt country not found! Please run CountrySeeder first.');
            return;
        }

        // 1. Vodafone Cash / Wallet
        $wallet = WithdrawMethod::updateOrCreate(
            ['slug' => 'wallet-eg'],
            [
                'country_id' => $egypt->id,
                'name' => ['en' => 'E-Wallets', 'ar' => 'المحافظ الإلكترونية'],
                'logo' => 'e-wallet.jpg',
                'is_active' => true,
            ]
        );

        $wallet->fields()->delete(); // Clear old fields
        $wallet->fields()->create([
            'name' => 'number',
            'label' => ['en' => 'Wallet Number', 'ar' => 'رقم المحفظة'],
            'type' => 'number',
            'placeholder' => ['en' => '01xxxxxxxxx', 'ar' => '01xxxxxxxxx'],
            'sort_order' => 1,
            'validation' => [
                'required' => true,
                'type' => 'number',
                'digits' => 11,
                'regex' => '^[0-9]+$'
            ]
        ]);

        // 2. InstaPay
        $instapay = WithdrawMethod::updateOrCreate(
            ['slug' => 'instapay-eg'],
            [
                'country_id' => $egypt->id,
                'name' => ['en' => 'InstaPay', 'ar' => 'انستا باي'],
                'logo' => 'instapay.jpg',
                'is_active' => true,
            ]
        );

        $instapay->fields()->delete(); // Clear old fields
        $instapay->fields()->create([
            'name' => 'number',
            'label' => ['en' => 'InstaPay Number', 'ar' => 'رقم انستا باي'],
            'type' => 'number',
            'placeholder' => ['en' => '01xxxxxxxxx', 'ar' => '01xxxxxxxxx'],
            'sort_order' => 1,
            'validation' => [
                'required' => true,
                'type' => 'number',
                'digits' => 11,
                'regex' => '^[0-9]+$'
            ]
        ]);

        $instapay->fields()->create([
            'name' => 'username',
            'label' => ['en' => 'InstaPay Address', 'ar' => 'عنوان انستا باي'],
            'type' => 'text',
            'placeholder' => ['en' => 'username@instapay', 'ar' => 'اسم المستخدم@instapay'],
            'sort_order' => 2,
            'validation' => [
                'required' => true,
                'type' => 'string',
                'regex' => '^[a-zA-Z0-9._]+@instapay$'
            ]
        ]);
    }
}
