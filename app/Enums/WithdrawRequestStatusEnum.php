<?php

namespace App\Enums;

enum WithdrawRequestStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
