<?php

namespace App\Exceptions;

use Exception;

class CreatorNotSubscribedToVendorException extends Exception
{
    public function __construct()
    {
        parent::__construct(__("Creator is not associated with this vendor or has no tracking ID."));
    }
}
