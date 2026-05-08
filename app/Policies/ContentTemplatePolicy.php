<?php

namespace App\Policies;

use App\Models\ContentTemplate;
use App\Models\Creator;

class ContentTemplatePolicy
{
    public function viewAny(?Creator $user): bool
    {
        return true;
    }

    public function view(?Creator $user, ContentTemplate $contentTemplate): bool
    {
        return $user?->id === $contentTemplate->creator_id;
    }

    public function create(?Creator $user): bool
    {
        return true;
    }

    public function update(?Creator $user, ContentTemplate $contentTemplate): bool
    {
        return $user?->id === $contentTemplate->creator_id;
    }

    public function delete(?Creator $user, ContentTemplate $contentTemplate): bool
    {
        return $user?->id === $contentTemplate->creator_id;
    }
}
