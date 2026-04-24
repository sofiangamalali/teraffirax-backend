<?php

namespace App\Policies;

use App\Models\Creator;
use Illuminate\Auth\Access\Response;

class CreatorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?Creator $creator): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?Creator $creator, Creator $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?Creator $creator): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?Creator $creator, Creator $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?Creator $creator, Creator $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?Creator $creator, Creator $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?Creator $creator, Creator $model): bool
    {
        return true;
    }
}
