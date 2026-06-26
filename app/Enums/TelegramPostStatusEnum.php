<?php

namespace App\Enums;

enum TelegramPostStatusEnum: string
{
    case PENDING = 'pending';
    case POSTED = 'posted';
}
