<?php

namespace App\Repositories\Interfaces;

use App\Models\ContentTemplate;
use Illuminate\Pagination\LengthAwarePaginator;

interface ContentTemplateRepositoryInterface
{
    public function paginateForCreator(int $creatorId, int $perPage = 15): LengthAwarePaginator;
    public function countForIntegration(int $creatorId, int $integrationId): int;
    public function create(array $data): ContentTemplate;
    public function find(int $id): ?ContentTemplate;
    public function update(ContentTemplate $contentTemplate, array $data): bool;
    public function delete(ContentTemplate $contentTemplate): bool;
    public function unsetDefaultsForIntegration(int $creatorId, int $integrationId, ?int $excludeId = null): void;
    public function getOldestForIntegration(int $creatorId, int $integrationId, int $excludeId): ?ContentTemplate;

    /**
     * Find default content template for creator and social integration channel.
     */
    public function findDefaultTemplate(int $creatorId, int $socialIntegrationId): ?ContentTemplate;
}
