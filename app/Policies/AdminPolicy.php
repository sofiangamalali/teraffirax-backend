<?php

namespace App\Policies;

use App\Models\Admin;

class AdminPolicy
{
    /**
     * Determine whether the admin can manage creator resources.
     */
    public function manage(Admin $admin): bool
    {
        return true;
    }
}
