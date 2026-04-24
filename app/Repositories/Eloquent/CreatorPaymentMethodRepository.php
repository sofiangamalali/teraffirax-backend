<?php

namespace App\Repositories\Eloquent;

use App\Models\CreatorPaymentMethod;
use App\Repositories\Interfaces\CreatorPaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CreatorPaymentMethodRepository implements CreatorPaymentMethodRepositoryInterface
{
    protected CreatorPaymentMethod $model;

    public function __construct(CreatorPaymentMethod $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): CreatorPaymentMethod
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?CreatorPaymentMethod
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $link = $this->find($id);
        return $link ? $link->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $link = $this->find($id);
        return $link ? $link->delete() : false;
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
