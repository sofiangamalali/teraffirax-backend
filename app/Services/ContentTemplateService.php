<?php

namespace App\Services;

use App\DTOs\ContentTemplateDTO;
use App\Models\ContentTemplate;
use App\Repositories\Interfaces\ContentTemplateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $data = $dto->toArray();

        if ($dto->platform) {
            $count = $this->repository->countForPlatform($dto->creator_id, $dto->platform);
            if ($count === 0) {
                $data['is_default'] = true;
            }
        }

        if (isset($data['is_default']) && $data['is_default'] === true) {
            $this->repository->unsetDefaultsForPlatform($dto->creator_id, $dto->platform);
        }

        return $this->repository->create($data);
    }

    public function find(int $id): ?ContentTemplate
    {
        return $this->repository->find($id);
    }

    public function update(ContentTemplate $contentTemplate, ContentTemplateDTO $dto): bool
    {
        $data = $dto->toArray();

        if (isset($data['is_default']) && $data['is_default'] === true) {
            $this->repository->unsetDefaultsForPlatform($contentTemplate->creator_id, $contentTemplate->platform);
        }

        return $this->repository->update($contentTemplate, $data);
    }

    public function delete(ContentTemplate $contentTemplate): bool
    {
        return $this->repository->delete($contentTemplate);
    }
}
