<?php

namespace App\Exceptions;

use Exception;

class UnsupportedTrackingGeneratorException extends Exception
{
    public function __construct()
    {
        parent::__construct("No tracking URL generator found for this vendor.");
    }
}
