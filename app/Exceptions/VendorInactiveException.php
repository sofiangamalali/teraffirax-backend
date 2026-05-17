<?php

namespace App\Exceptions;

use Exception;

class VendorInactiveException extends Exception
{
    public function __construct()
    {
        parent::__construct(__("This vendor is currently inactive and cannot be used."));
    }
}
