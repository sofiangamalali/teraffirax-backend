<?php

namespace App\Services;

use App\Models\CreatorPaymentMethod;
use App\Repositories\Interfaces\CreatorPaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CreatorPaymentMethodService
{
    protected CreatorPaymentMethodRepositoryInterface $repository;

    public function __construct(CreatorPaymentMethodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllLinks(): Collection
    {
        return $this->repository->all();
    }

    public function getCreatorMethods(int $creatorId): Collection
    {
        return $this->repository->getByCreator($creatorId);
    }

    public function createLink(array $data): CreatorPaymentMethod
    {
        return DB::transaction(function () use ($data) {
            $link = $this->repository->create($data);
            
            if ($link->is_default) {
                $this->repository->unsetOtherDefaults($link->id, $link->creator_id);
            }
            
            return $link;
        });
    }

    public function getLinkById(int $id): ?CreatorPaymentMethod
    {
        return $this->repository->find($id);
    }

    public function updateLink(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $updated = $this->repository->update($id, $data);
            
            if ($updated && isset($data['is_default']) && $data['is_default']) {
                $link = $this->repository->find($id);
                $this->repository->unsetOtherDefaults($id, $link->creator_id);
            }
            
            return $updated;
        });
    }

    public function deleteLink(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
