<?php

namespace App\Repositories\Interfaces;

use App\Models\SocialIntegration;
use Illuminate\Pagination\LengthAwarePaginator;

interface SocialIntegrationRepositoryInterface
{
    public function paginateForCreator(int $creatorId, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): SocialIntegration;
    public function find(int $id): ?SocialIntegration;
    public function update(SocialIntegration $socialIntegration, array $data): bool;
    public function delete(SocialIntegration $socialIntegration): bool;
}
