<?php

namespace App\Repositories\Interfaces;

use App\Models\ContentTemplate;
use Illuminate\Pagination\LengthAwarePaginator;

interface ContentTemplateRepositoryInterface
{
    public function paginateForCreator(int $creatorId, int $perPage = 15): LengthAwarePaginator;
    public function countForIntegration(int $integrationId): int;
    public function create(array $data): ContentTemplate;
    public function find(int $id): ?ContentTemplate;
    public function update(ContentTemplate $contentTemplate, array $data): bool;
    public function delete(ContentTemplate $contentTemplate): bool;
}
