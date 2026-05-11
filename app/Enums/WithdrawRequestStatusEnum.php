<?php

namespace App\Enums;

enum WithdrawRequestStatusEnum: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
