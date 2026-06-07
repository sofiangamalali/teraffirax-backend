<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\SocialIntegration;
use App\DTOs\AdminSocialIntegrationDTO;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\SocialIntegrationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminCreatorService
{
    public function __construct(
        protected CreatorRepositoryInterface $creatorRepository,
        protected SocialIntegrationRepositoryInterface $socialIntegrationRepository,
    ) {}

    /**
     * Get paginated creators.
     */
    public function getPaginatedCreators(int $perPage = 15): LengthAwarePaginator
    {
        return $this->creatorRepository->paginate($perPage);
    }

    /**
     * Toggle active status of a creator.
     */
    public function toggleCreatorActive(int $creatorId): Creator
    {
        return DB::transaction(function () use ($creatorId) {
            $creator = $this->creatorRepository->findOrFail($creatorId);
            return $this->creatorRepository->update($creatorId, ['is_active' => !$creator->is_active]);
        });
    }

    /**
     * Get social integrations of a creator.
     */
    public function getCreatorSocialIntegrations(int $creatorId, int $perPage = 15): LengthAwarePaginator
    {
        // Ensure creator exists
        $this->creatorRepository->findOrFail($creatorId);

        return $this->socialIntegrationRepository->paginateForCreator($creatorId, $perPage);
    }

    /**
     * Create a social integration for a creator.
     */
    public function createSocialIntegration(AdminSocialIntegrationDTO $dto): SocialIntegration
    {
        // Ensure creator exists
        $this->creatorRepository->findOrFail($dto->creator_id);

        return DB::transaction(function () use ($dto) {
            return $this->socialIntegrationRepository->create($dto->toArray());
        });
    }

    /**
     * Update social integration of a creator.
     */
    public function updateSocialIntegration(int $creatorId, int $integrationId, AdminSocialIntegrationDTO $dto): SocialIntegration
    {
        // Ensure creator exists
        $this->creatorRepository->findOrFail($creatorId);

        $integration = $this->socialIntegrationRepository->find($integrationId);
        if (!$integration || $integration->creator_id !== $creatorId) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Social integration not found for this creator.");
        }

        DB::transaction(function () use ($integration, $dto) {
            $this->socialIntegrationRepository->update($integration, $dto->toArray());
        });

        return $integration;
    }

    /**
     * Delete social integration of a creator.
     */
    public function deleteSocialIntegration(int $creatorId, int $integrationId): bool
    {
        // Ensure creator exists
        $this->creatorRepository->findOrFail($creatorId);

        $integration = $this->socialIntegrationRepository->find($integrationId);
        if (!$integration || $integration->creator_id !== $creatorId) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Social integration not found for this creator.");
        }

        return DB::transaction(function () use ($integration) {
            return $this->socialIntegrationRepository->delete($integration);
        });
    }
}
