<?php

namespace App\Repositories\Eloquent;

use App\Models\ContentTemplate;
use App\Repositories\Interfaces\ContentTemplateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ContentTemplateRepository implements ContentTemplateRepositoryInterface
{
    protected ContentTemplate $model;

    public function __construct(ContentTemplate $model)
    {
        $this->model = $model;
    }

    public function paginateForCreator(int $creatorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('creator_id', $creatorId)->paginate($perPage);
    }

    public function countForPlatform(int $creatorId, string $platform): int
    {
        return $this->model->where('creator_id', $creatorId)
            ->where('platform', $platform)
            ->count();
    }

    public function create(array $data): ContentTemplate
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?ContentTemplate
    {
        return $this->model->find($id);
    }

    public function update(ContentTemplate $contentTemplate, array $data): bool
    {
        return $contentTemplate->update($data);
    }

    public function delete(ContentTemplate $contentTemplate): bool
    {
        return $contentTemplate->delete();
    }

    public function unsetDefaultsForPlatform(int $creatorId, string $platform): void
    {
        $this->model->where('creator_id', $creatorId)
            ->where('platform', $platform)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
