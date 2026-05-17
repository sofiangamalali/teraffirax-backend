<?php

namespace App\Exceptions;

use Exception;

class UnsupportedVendorException extends Exception
{
    public function __construct()
    {
        parent::__construct(__("This product URL does not belong to any supported vendor."));
    }
}
