<?php

namespace App\Services;

use App\Models\TelegramChannel;
use App\Repositories\Interfaces\TelegramChannelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TelegramChannelService
{
    protected TelegramChannelRepositoryInterface $repository;

    public function __construct(TelegramChannelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllChannels(): Collection
    {
        return $this->repository->all();
    }

    public function createChannel(array $data): TelegramChannel
    {
        return $this->repository->create($data);
    }

    public function getChannelById(int $id): ?TelegramChannel
    {
        return $this->repository->find($id);
    }

    public function updateChannel(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteChannel(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getChannelsByCreator(int $creatorId): Collection
    {
        return $this->repository->getByCreator($creatorId);
    }
}
