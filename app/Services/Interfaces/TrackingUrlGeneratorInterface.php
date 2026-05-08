<?php

namespace App\Services\Interfaces;

use App\Models\Vendor;

interface TrackingUrlGeneratorInterface
{
    public function supports(Vendor $vendor): bool;

    public function generate(string $originalUrl, string $trackingId): string;
}
