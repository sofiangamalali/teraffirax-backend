<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Creator;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(?Creator $user): bool { return true; }
    public function view(?Creator $user, Product $product): bool { return true; }
    public function create(?Creator $user): bool { return true; }
    public function update(?Creator $user, Product $product): bool { return true; }
    public function delete(?Creator $user, Product $product): bool { return true; }
    public function restore(?Creator $user, Product $product): bool { return true; }
    public function forceDelete(?Creator $user, Product $product): bool { return true; }
}
