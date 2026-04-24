<?php

namespace App\Policies;

use App\Models\Country;
use App\Models\Creator;
use Illuminate\Auth\Access\Response;

class CountryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Creator $user): bool
    {
        return true; // Adjust based on requirements
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Creator $user, Country $country): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Creator $user): bool
    {
        return true; // Adjust, likely admin only
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Creator $user, Country $country): bool
    {
        return true; // Adjust, likely admin only
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Creator $user, Country $country): bool
    {
        return true; // Adjust, likely admin only
    }
}
