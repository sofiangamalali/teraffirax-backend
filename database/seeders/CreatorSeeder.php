<?php

namespace Database\Seeders;

use App\Models\Creator;
use App\Models\Country;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\SocialIntegration;
use App\Models\CreatorVendorTracking;
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


            $vendor = Vendor::where('slug', 'amazon')->first();
            if ($vendor) {
                CreatorVendorTracking::updateOrCreate(
                    [
                        'creator_id' => $creator->id,
                        'vendor_id' => $vendor->id,
                    ],
                    [
                        'track_id' => 'traffirax-21',
                    ]
                );
            }

            $integration = SocialIntegration::updateOrCreate(
                [
                    'creator_id' => $creator->id,
                    'platform' => 'telegram',
                    'type' => 'channel',
                ],
                [
                    'status' => 'pending',
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
                    'content' => "{{product_name}} بسعر {{product_price}} \n\nرابط العرض: {{link}}",
                ]
            );
        });
    }
}
