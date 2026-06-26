<?php

namespace App\Enums;

enum WithdrawRequestStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
