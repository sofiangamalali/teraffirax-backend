<?php

namespace App\Policies;

use App\Models\SocialIntegration;
use App\Models\Creator;

class SocialIntegrationPolicy
{
    public function viewAny(?Creator $user): bool
    {
        return true;
    }

    public function view(?Creator $user, SocialIntegration $socialIntegration): bool
    {
        return $user?->id === $socialIntegration->creator_id;
    }

    public function create(?Creator $user): bool
    {
        return true;
    }

    public function update(?Creator $user, SocialIntegration $socialIntegration): bool
    {
        return $user?->id === $socialIntegration->creator_id;
    }

    public function delete(?Creator $user, SocialIntegration $socialIntegration): bool
    {
        return $user?->id === $socialIntegration->creator_id;
    }
}
