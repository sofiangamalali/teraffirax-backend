<?php

namespace App\Exceptions;

use Exception;

class VendorInactiveException extends Exception
{
    public function __construct()
    {
        parent::__construct("This vendor is currently inactive and cannot be used.");
    }
}
