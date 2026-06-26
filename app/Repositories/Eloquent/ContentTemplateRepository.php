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
        return $this->model->where('creator_id', $creatorId)->with('socialIntegration')->paginate($perPage);
    }

    public function countForIntegration(int $creatorId, int $integrationId): int
    {
        return $this->model->where('creator_id', $creatorId)
            ->where('social_integration_id', $integrationId)
            ->count();
    }

    public function create(array $data): ContentTemplate
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?ContentTemplate
    {
        return $this->model->with('socialIntegration')->find($id);
    }

    public function update(ContentTemplate $contentTemplate, array $data): bool
    {
        return $contentTemplate->update($data);
    }

    public function delete(ContentTemplate $contentTemplate): bool
    {
        return $contentTemplate->delete();
    }

    public function unsetDefaultsForIntegration(int $creatorId, int $integrationId, ?int $excludeId = null): void
    {
        $query = $this->model->where('creator_id', $creatorId)
            ->where('social_integration_id', $integrationId)
            ->where('is_default', true);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $query->update(['is_default' => false]);
    }

    public function getOldestForIntegration(int $creatorId, int $integrationId, int $excludeId): ?ContentTemplate
    {
        return $this->model->where('creator_id', $creatorId)
            ->where('social_integration_id', $integrationId)
            ->where('id', '!=', $excludeId)
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * Find default content template for creator and social integration channel.
     */
    public function findDefaultTemplate(int $creatorId, int $socialIntegrationId): ?ContentTemplate
    {
        return $this->model->newQuery()
            ->where('creator_id', $creatorId)
            ->where(function ($q) use ($socialIntegrationId) {
                $q->where('social_integration_id', $socialIntegrationId)
                    ->orWhereNull('social_integration_id');
            })
            ->where('is_default', true)
            ->first();
    }
}
