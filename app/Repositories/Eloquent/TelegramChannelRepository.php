<?php

namespace App\Repositories\Eloquent;

use App\Models\TelegramChannel;
use App\Repositories\Interfaces\TelegramChannelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TelegramChannelRepository implements TelegramChannelRepositoryInterface
{
    protected TelegramChannel $model;

    public function __construct(TelegramChannel $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): TelegramChannel
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?TelegramChannel
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $channel = $this->find($id);
        return $channel ? $channel->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $channel = $this->find($id);
        return $channel ? $channel->delete() : false;
    }

    public function getByCreator(int $creatorId): Collection
    {
        return $this->model->where('creator_id', $creatorId)->get();
    }
}
