<?php

namespace App\Services;

use App\DTOs\CreatorDTO;
use App\Models\Creator;
use App\Repositories\Interfaces\CreatorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CreatorService
{
    public function __construct(
        protected CreatorRepositoryInterface $creatorRepository,
    ) {}

    public function getAllCreators(): Collection
    {
        return $this->creatorRepository->all();
    }

    public function getPaginatedCreators(int $perPage = 15): LengthAwarePaginator
    {
        return $this->creatorRepository->paginate($perPage);
    }

    public function getCreatorById(int $id): Creator
    {
        return $this->creatorRepository->findOrFail($id);
    }

    public function createCreator(CreatorDTO $dto): Creator
    {
        return DB::transaction(function () use ($dto) {
            return $this->creatorRepository->create($dto->toArray());
        });
    }

    public function updateCreator(int $id, CreatorDTO $dto): Creator
    {
        return DB::transaction(function () use ($id, $dto) {
            return $this->creatorRepository->update($id, $dto->toArray());
        });
    }

    public function deleteCreator(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->creatorRepository->delete($id);
        });
    }
}
