<?php

namespace App\Exceptions;

use Exception;

class VendorNotFoundForProductException extends Exception
{
    public function __construct()
    {
        parent::__construct("Vendor not found for this product.");
    }
}
