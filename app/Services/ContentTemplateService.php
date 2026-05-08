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
        
        if ($dto->social_integration_id) {
            $count = $this->repository->countForIntegration($dto->social_integration_id);
            if ($count === 0) {
                $data['is_default'] = true;
            }
        }
        
        return $this->repository->create($data);
    }

    public function find(int $id): ?ContentTemplate
    {
        return $this->repository->find($id);
    }

    public function update(ContentTemplate $contentTemplate, ContentTemplateDTO $dto): bool
    {
        return $this->repository->update($contentTemplate, $dto->toArray());
    }

    public function delete(ContentTemplate $contentTemplate): bool
    {
        return $this->repository->delete($contentTemplate);
    }
}
