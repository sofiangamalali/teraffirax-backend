<?php

namespace App\Listeners;

use App\Events\CreatorRegistered;
use App\Models\Vendor;
use App\Models\CreatorVendorTracking;
use Illuminate\Support\Facades\Log;

class LinkCreatorToVendors
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CreatorRegistered $event): void
    {
        $creator = $event->creator;

        try {
            $vendors = Vendor::where('country_id', $creator->country_id)->get();

            foreach ($vendors as $vendor) {
                CreatorVendorTracking::updateOrCreate(
                    [
                        'creator_id' => $creator->id,
                        'vendor_id' => $vendor->id,
                    ],
                    [
                        'platform_commission_percentage' => 50,
                        'track_id' => '',
                        'is_active' => false,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to link creator to vendors for creator ' . $creator->id . ': ' . $e->getMessage());
        }
    }
}
