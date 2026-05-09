<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\Creator;

class CommissionPolicy
{
    public function viewAny(?Creator $user): bool
    {
        return true;
    }

    public function view(?Creator $user, Commission $commission): bool
    {
        return $user?->id === $commission->creator_id;
    }
}
