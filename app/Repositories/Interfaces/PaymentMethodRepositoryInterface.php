<?php

namespace App\Repositories\Interfaces;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

interface PaymentMethodRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): PaymentMethod;
    public function find(int $id): ?PaymentMethod;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getActive(): Collection;
}
