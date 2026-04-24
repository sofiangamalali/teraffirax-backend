<?php

namespace App\Repositories\Interfaces;

use App\Models\TelegramChannel;
use Illuminate\Database\Eloquent\Collection;

interface TelegramChannelRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): TelegramChannel;
    public function find(int $id): ?TelegramChannel;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByCreator(int $creatorId): Collection;
}
