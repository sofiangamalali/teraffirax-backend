<?php

namespace App\Repositories\Interfaces;

use App\Models\CreatorPaymentMethod;
use Illuminate\Database\Eloquent\Collection;

interface CreatorPaymentMethodRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): CreatorPaymentMethod;
    public function find(int $id): ?CreatorPaymentMethod;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByCreator(int $creatorId): Collection;
    public function unsetOtherDefaults(int $id, int $creatorId): void;
}
