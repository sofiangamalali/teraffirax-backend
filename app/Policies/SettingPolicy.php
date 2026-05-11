<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\Creator;

class SettingPolicy
{
    public function viewAny(?Creator $user): bool 
    { 
        return true; // TODO: Restrict to admins in production
    }

    public function view(?Creator $user, Setting $setting): bool 
    { 
        return true; // TODO: Restrict to admins in production
    }

    public function create(?Creator $user): bool 
    { 
        return true; // TODO: Restrict to admins in production
    }

    public function update(?Creator $user, Setting $setting): bool 
    { 
        return true; // TODO: Restrict to admins in production
    }

    public function delete(?Creator $user, Setting $setting): bool 
    { 
        return true; // TODO: Restrict to admins in production
    }
}
