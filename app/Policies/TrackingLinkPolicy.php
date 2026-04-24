<?php

namespace App\Policies;

use App\Models\TrackingLink;
use App\Models\Creator;
use Illuminate\Auth\Access\Response;

class TrackingLinkPolicy
{
    public function viewAny(?Creator $user): bool { return true; }
    public function view(?Creator $user, TrackingLink $link): bool { return true; }
    public function create(?Creator $user): bool { return true; }
    public function update(?Creator $user, TrackingLink $link): bool { return true; }
    public function delete(?Creator $user, TrackingLink $link): bool { return true; }
}
