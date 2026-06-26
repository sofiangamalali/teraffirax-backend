<?php

namespace App\Enums;

enum SocialIntegrationStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}
