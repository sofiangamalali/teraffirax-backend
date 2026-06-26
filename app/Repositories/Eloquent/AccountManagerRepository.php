<?php

namespace App\Repositories\Eloquent;

use App\Models\AccountManager;
use App\Repositories\Interfaces\AccountManagerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AccountManagerRepository implements AccountManagerRepositoryInterface
{
    public function __construct(
        protected AccountManager $model,
    ) {
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest('id')->paginate($perPage);
    }

    public function findOrFail(int $id): AccountManager
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): AccountManager
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): AccountManager
    {
        $manager = $this->findOrFail($id);
        $manager->update($data);
        return $manager->fresh();
    }

    public function delete(int $id): bool
    {
        $manager = $this->findOrFail($id);
        return $manager->delete();
    }
}
