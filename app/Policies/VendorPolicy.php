<?php

namespace App\Policies;

use App\Models\Vendor;
use App\Models\Creator; // Using Creator as the potential authenticatable if needed, but keeping it generic or nullable
use Illuminate\Auth\Access\Response;

class VendorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?Creator $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?Creator $user, Vendor $vendor): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?Creator $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?Creator $user, Vendor $vendor): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?Creator $user, Vendor $vendor): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?Creator $user, Vendor $vendor): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?Creator $user, Vendor $vendor): bool
    {
        return true;
    }
}
