<?php

namespace App\Repositories\Interfaces;

use App\Models\TrackingLink;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TrackingLinkRepositoryInterface
{
    public function paginate(int $perPage = 15, ?int $creatorId = null): LengthAwarePaginator;

    public function find(int $id): ?TrackingLink;

    public function findOrFail(int $id): TrackingLink;

    public function findBySlug(string $slug): ?TrackingLink;

    public function findByCreatorAndProduct(int $creatorId, int $productId): ?TrackingLink;

    public function create(array $data): TrackingLink;

    public function update(int $id, array $data): TrackingLink;


}
