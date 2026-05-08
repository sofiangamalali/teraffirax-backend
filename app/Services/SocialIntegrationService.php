<?php

namespace App\Services;

use App\DTOs\SocialIntegrationDTO;
use App\Models\SocialIntegration;
use App\Repositories\Interfaces\SocialIntegrationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SocialIntegrationService
{
    public function __construct(protected SocialIntegrationRepositoryInterface $repository)
    {
    }

    public function paginateForCreator(int $creatorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateForCreator($creatorId, $perPage);
    }

    public function create(SocialIntegrationDTO $dto): SocialIntegration
    {
        return $this->repository->create($dto->toArray());
    }

    public function find(int $id): ?SocialIntegration
    {
        return $this->repository->find($id);
    }

    public function update(SocialIntegration $socialIntegration, SocialIntegrationDTO $dto): bool
    {
        return $this->repository->update($socialIntegration, $dto->toArray());
    }

    public function delete(SocialIntegration $socialIntegration): bool
    {
        return $this->repository->delete($socialIntegration);
    }
}
