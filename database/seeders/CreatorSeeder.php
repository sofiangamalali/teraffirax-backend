<?php

namespace Database\Seeders;

use App\Models\Creator;
use App\Models\Country;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\SocialIntegration;
use App\Models\CreatorVendorTrackingId;
use App\Models\Commission;
use App\Models\ContentTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $egypt = Country::where('iso2', 'eg')->first();

            $creator = Creator::updateOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test Creator',
                    'phone' => '01000000000',
                    'password' => Hash::make('password'),
                    'gender' => 'male',
                    'pending_balance' => 0,
                    'available_balance' => 0,
                    'withdrawn_balance' => 0,
                    'country_id' => $egypt ? $egypt->id : 1,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );


            $category = Category::first();
            if ($category) {
                $creator->categories()->sync([$category->id]);
            }


            $vendor = Vendor::where('domain', 'https://www.amazon.eg')->first();
            if ($vendor) {
                CreatorVendorTrackingId::updateOrCreate(
                    [
                        'creator_id' => $creator->id,
                        'vendor_id' => $vendor->id,
                    ],
                    [
                        'tracking_id' => 'dealsegypt21-21',
                    ]
                );

                // Add 5 old commissions
                for ($i = 1; $i <= 5; $i++) {
                    Commission::create([
                        'creator_id' => $creator->id,
                        'vendor_id' => $vendor->id,
                        'earned_at' => now()->subDays(90 + $i)->format('Y-m-d'),
                        'clicks_count' => rand(100, 500),
                        'items_count' => rand(5, 20),
                        'returned_count' => rand(0, 2),
                        'revenue_amount' => 1000.00,
                        'commission_amount' => 100.00,
                    ]);
                }
            }

            $integration = SocialIntegration::updateOrCreate(
                [
                    'creator_id' => $creator->id,
                    'platform' => 'telegram',
                    'type' => 'channel',
                ],
                [
                    'status' => 'active',
                    'name' => 'Test Channel',
                    'username' => 'test_channel',
                    'external_id' => '123456789',
                ]
            );

            ContentTemplate::updateOrCreate(
                [
                    'creator_id' => $creator->id,
                    'social_integration_id' => $integration->id,
                    'is_default' => true,
                ],
                [
                    'name' => 'Default Template',
                    'content' => "{{discount}}% discount on {{product_name}} at {{product_price}} EGP\n\nOffer link: {{link}}",
                ]
            );
        });
    }
}
