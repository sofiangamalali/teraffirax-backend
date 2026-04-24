<?php

namespace App\Exceptions;

use Exception;
use App\Models\TrackingLink;

class DuplicateTrackingLinkException extends Exception
{
    protected TrackingLink $trackingLink;

    public function __construct(TrackingLink $trackingLink)
    {
        parent::__construct("The product already exists for this creator");
        $this->trackingLink = $trackingLink;
    }

    public function getTrackingLink(): TrackingLink
    {
        return $this->trackingLink;
    }
}
