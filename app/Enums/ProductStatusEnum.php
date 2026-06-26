<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case ACTIVE = 'active';
    case OUT_OF_STOCK = 'out_of_stock';
    case UNAVAILABLE = 'unavailable';
}
