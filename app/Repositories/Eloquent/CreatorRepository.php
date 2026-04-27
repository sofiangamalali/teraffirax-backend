<?php

namespace App\Repositories\Eloquent;

use App\Models\Creator;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CreatorRepository implements CreatorRepositoryInterface
{
    public function __construct(
        protected Creator $model,
    ) {}

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function find(int $id): ?Creator
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Creator
    {
        return $this->model->findOrFail($id);
    }

    public function findByEmail(string $email): ?Creator
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?Creator
    {
        return $this->model->where('phone', $phone)->first();
    }

    public function create(array $data): Creator
    {
        $creator = $this->model->create(array_diff_key($data, array_flip(['categories'])));

        if (isset($data['categories'])) {
            $creator->categories()->sync($data['categories']);
        }

        return $creator;
    }

    public function update(int $id, array $data): Creator
    {
        $creator = $this->findOrFail($id);
        
        $creator->update(array_diff_key($data, array_flip(['categories'])));

        if (isset($data['categories'])) {
            $creator->categories()->sync($data['categories']);
        }

        return $creator->fresh();
    }

    public function delete(int $id): bool
    {
        $creator = $this->findOrFail($id);

        return $creator->delete();
    }
}
