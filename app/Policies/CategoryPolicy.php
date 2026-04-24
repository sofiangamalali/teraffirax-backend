<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Creator;

class CategoryPolicy
{
    public function viewAny(?Creator $user): bool { return true; }
    public function view(?Creator $user, Category $category): bool { return true; }
    public function create(?Creator $user): bool { return true; }
    public function update(?Creator $user, Category $category): bool { return true; }
    public function delete(?Creator $user, Category $category): bool { return true; }
    public function restore(?Creator $user, Category $category): bool { return true; }
    public function forceDelete(?Creator $user, Category $category): bool { return true; }
}
