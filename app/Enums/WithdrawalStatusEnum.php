<?php

namespace App\Enums;

enum WithdrawalStatusEnum: string
{
    case PENDING = 'pending';
    case DONE = 'done';
}
