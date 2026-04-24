<?php

namespace App\Repositories\Interfaces;

use App\Models\Creator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CreatorRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Creator;

    public function findOrFail(int $id): Creator;
    
    public function findByEmail(string $email): ?Creator;

    public function findByPhone(string $phone): ?Creator;

    public function create(array $data): Creator;

    public function update(int $id, array $data): Creator;

    public function delete(int $id): bool;
}
