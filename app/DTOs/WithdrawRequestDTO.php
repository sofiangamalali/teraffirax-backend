<?php

namespace App\DTOs;

class WithdrawRequestDTO
{
    public function __construct(
        public int $creator_id,
        public int $creator_withdraw_method_id,
    ) {}
}
