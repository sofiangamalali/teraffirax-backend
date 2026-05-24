<?php

namespace App\Services;

use App\DTOs\ContentTemplateDTO;
use App\Models\ContentTemplate;
use App\Repositories\Interfaces\ContentTemplateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContentTemplateService
{
    public function __construct(protected ContentTemplateRepositoryInterface $repository)
    {
    }

    public function paginateForCreator(int $creatorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateForCreator($creatorId, $perPage);
    }

    public function create(ContentTemplateDTO $dto): ContentTemplate
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();

            if ($dto->social_integration_id) {
                $count = $this->repository->countForIntegration($dto->creator_id, $dto->social_integration_id);
                if ($count === 0) {
                    $data['is_default'] = true;
                }
            }

            if (isset($data['is_default']) && $data['is_default'] === true) {
                // For create, we do not have an ID yet, so we just unset all.
                $this->repository->unsetDefaultsForIntegration($dto->creator_id, $dto->social_integration_id);
            }

            return $this->repository->create($data);
        });
    }

    public function find(int $id): ?ContentTemplate
    {
        return $this->repository->find($id);
    }

    public function update(ContentTemplate $contentTemplate, ContentTemplateDTO $dto): bool
    {
        return DB::transaction(function () use ($contentTemplate, $dto) {
            $data = $dto->toArray();

            // Prevent unsetting default directly
            if (isset($data['is_default']) && !$data['is_default'] && $contentTemplate->is_default) {
                unset($data['is_default']);
            }

            $updated = $this->repository->update($contentTemplate, $data);

            if ($updated && isset($data['is_default']) && $data['is_default'] === true) {
                $this->repository->unsetDefaultsForIntegration($contentTemplate->creator_id, $contentTemplate->social_integration_id, $contentTemplate->id);
            }

            return $updated;
        });
    }

    public function delete(ContentTemplate $contentTemplate): bool
    {
        return DB::transaction(function () use ($contentTemplate) {
            $deleted = $this->repository->delete($contentTemplate);

            // If the deleted template was default, promote the oldest remaining one for this platform
            if ($deleted && $contentTemplate->is_default) {
                $newDefault = $this->repository->getOldestForIntegration($contentTemplate->creator_id, $contentTemplate->social_integration_id, $contentTemplate->id);
                if ($newDefault) {
                    $this->repository->update($newDefault, ['is_default' => true]);
                }
            }

            return $deleted;
        });
    }
}
