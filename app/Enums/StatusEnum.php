<?php

namespace App\Enums;

enum StatusEnum: string
{
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case REVOKED = 'revoked';
}
