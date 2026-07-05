<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\SocialIntegration;
use App\Models\CreatorVendorTracking;
use App\DTOs\AdminSocialIntegrationDTO;
use App\DTOs\AdminCreatorVendorTrackingDTO;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use App\Repositories\Interfaces\SocialIntegrationRepositoryInterface;
use App\Repositories\Interfaces\CreatorVendorTrackingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class AdminCreatorService
{
    public function __construct(
        protected CreatorRepositoryInterface $creatorRepository,
        protected SocialIntegrationRepositoryInterface $socialIntegrationRepository,
        protected CreatorVendorTrackingRepositoryInterface $creatorVendorTrackingRepository,
        protected TelegramService $telegramService,
    ) {
    }

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
            throw new ModelNotFoundException("Social integration not found for this creator.");
        }

        $data = $dto->toArray();
        $platform = $dto->platform ?? ($integration->platform ? $integration->platform->value : null);

        if ($platform === 'telegram') {
            $username = $dto->username ?? $integration->username;
            if ($username) {
                $data['external_id'] = $this->telegramService->resolveExternalIdFromUsername($username);
            } else {
                $data['external_id'] = null;
            }
        }

        DB::transaction(function () use ($integration, $data) {
            $this->socialIntegrationRepository->update($integration, $data);
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
            throw new ModelNotFoundException("Social integration not found for this creator.");
        }

        return DB::transaction(function () use ($integration) {
            return $this->socialIntegrationRepository->delete($integration);
        });
    }

    /**
     * Get vendor trackings of a creator.
     */
    public function getCreatorVendorTrackings(int $creatorId, int $perPage = 15): LengthAwarePaginator
    {
        // Ensure creator exists
        $this->creatorRepository->findOrFail($creatorId);

        return $this->creatorVendorTrackingRepository->paginateForCreator($creatorId, $perPage);
    }

    /**
     * Update vendor tracking of a creator.
     */
    public function updateCreatorVendorTracking(int $creatorId, int $trackingId, AdminCreatorVendorTrackingDTO $dto): CreatorVendorTracking
    {
        // Ensure creator exists
        $this->creatorRepository->findOrFail($creatorId);

        $tracking = $this->creatorVendorTrackingRepository->find($trackingId);
        if (!$tracking || $tracking->creator_id !== $creatorId) {
            throw new ModelNotFoundException("Vendor tracking record not found for this creator.");
        }

        DB::transaction(function () use ($trackingId, $dto) {
            $this->creatorVendorTrackingRepository->update($trackingId, $dto->toArray());
        });

        return $tracking->refresh();
    }

    /**
     * Assign an account manager to a creator.
     */
    public function assignAccountManager(int $creatorId, ?int $accountManagerId): Creator
    {
        $creator = $this->creatorRepository->findOrFail($creatorId);

        DB::transaction(function () use ($creatorId, $accountManagerId) {
            $this->creatorRepository->update($creatorId, [
                'account_manager_id' => $accountManagerId
            ]);
        });

        return $creator->refresh();
    }
}
