<?php

namespace App\Repositories\Eloquent;

use App\Models\SocialIntegration;
use App\Repositories\Interfaces\SocialIntegrationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SocialIntegrationRepository implements SocialIntegrationRepositoryInterface
{
    protected SocialIntegration $model;

    public function __construct(SocialIntegration $model)
    {
        $this->model = $model;
    }

    public function paginateForCreator(int $creatorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('creator_id', $creatorId)->paginate($perPage);
    }

    public function create(array $data): SocialIntegration
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?SocialIntegration
    {
        return $this->model->find($id);
    }

    public function update(SocialIntegration $socialIntegration, array $data): bool
    {
        return $socialIntegration->update($data);
    }

    public function delete(SocialIntegration $socialIntegration): bool
    {
        return $socialIntegration->delete();
    }

    /**
     * Update social integration status or fields by ID.
     */
    public function updateById(int $id, array $data): bool
    {
        return $this->model->newQuery()->where('id', $id)->update($data) > 0;
    }
}
