<?php

namespace App\Services\Tracking;

use App\Services\Interfaces\TrackingUrlGeneratorInterface;
use App\Models\Vendor;
use App\Services\AmazonTrackingService;
use Illuminate\Support\Str;

class AmazonTrackingUrlGenerator implements TrackingUrlGeneratorInterface
{
    public function __construct(
        protected AmazonTrackingService $amazonTrackingService
    ) {}

    public function supports(Vendor $vendor): bool
    {
        return $vendor->domain && Str::contains($vendor->domain, 'amazon.eg');
    }

    public function generate(string $originalUrl, string $trackingId): string
    {
        return $this->amazonTrackingService->appendTrackingId($originalUrl, $trackingId);
    }
}
