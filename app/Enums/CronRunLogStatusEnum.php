<?php

namespace App\Enums;

enum CronRunLogStatusEnum: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case PARTIAL = 'partial';
}
