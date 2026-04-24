<?php

namespace App\Repositories\Eloquent;

use App\Models\TrackingLink;
use App\Repositories\Interfaces\TrackingLinkRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TrackingLinkRepository implements TrackingLinkRepositoryInterface
{
    public function __construct(protected
        TrackingLink $model,
        )
    {
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function find(int $id): ?TrackingLink
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): TrackingLink
    {
        return $this->model->findOrFail($id);
    }

    public function findBySlug(string $slug): ?TrackingLink
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function findByCreatorAndProduct(int $creatorId, int $productId): ?TrackingLink
    {
        return $this->model->where('creator_id', $creatorId)
            ->where('product_id', $productId)
            ->first();
    }

    public function create(array $data): TrackingLink
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): TrackingLink
    {
        $link = $this->model->findOrFail($id);
        $link->update($data);

        return $link->fresh();
    }


}
