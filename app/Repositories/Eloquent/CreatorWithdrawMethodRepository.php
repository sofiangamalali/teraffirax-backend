<?php

namespace App\Repositories\Eloquent;

use App\Models\CreatorWithdrawMethod;
use App\Repositories\Interfaces\CreatorWithdrawMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CreatorWithdrawMethodRepository implements CreatorWithdrawMethodRepositoryInterface
{
    protected CreatorWithdrawMethod $model;

    public function __construct(CreatorWithdrawMethod $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): CreatorWithdrawMethod
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?CreatorWithdrawMethod
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $method = $this->find($id);
        return $method ? $method->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $method = $this->find($id);
        return $method ? $method->delete() : false;
    }

    public function getByCreator(int $creatorId): Collection
    {
        return $this->model->where('creator_id', $creatorId)->get();
    }

    public function unsetOtherDefaults(int $id, int $creatorId): void
    {
        $this->model->where('creator_id', $creatorId)
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);
    }
}
