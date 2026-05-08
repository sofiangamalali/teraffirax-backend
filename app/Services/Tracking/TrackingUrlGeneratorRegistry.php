<?php

namespace App\Services\Tracking;

use App\Models\Vendor;
use App\Services\Interfaces\TrackingUrlGeneratorInterface;
use App\Exceptions\UnsupportedTrackingGeneratorException;

class TrackingUrlGeneratorRegistry
{
    /**
     * @var TrackingUrlGeneratorInterface[]
     */
    protected array $generators = [];

    public function __construct(array $generators)
    {
        $this->generators = $generators;
    }

    /**
     * Resolve the appropriate generator for the vendor.
     *
     * @param Vendor $vendor
     * @return TrackingUrlGeneratorInterface
     * @throws UnsupportedTrackingGeneratorException
     */
    public function resolve(Vendor $vendor): TrackingUrlGeneratorInterface
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($vendor)) {
                return $generator;
            }
        }

        throw new UnsupportedTrackingGeneratorException();
    }
}
