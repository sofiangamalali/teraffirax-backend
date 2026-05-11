<?php

namespace App\Repositories\Eloquent;

use App\DTOs\SettingDTO;
use App\Models\Setting;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use Illuminate\Support\Collection;

class SettingRepository implements SettingRepositoryInterface
{
    public function __construct(
        protected Setting $model,
    ) {}

    public function get(string $key): ?Setting
    {
        return $this->model->where('key', $key)->first();
    }

    public function set(SettingDTO $dto): Setting
    {
        return $this->model->updateOrCreate(
            ['key' => $dto->key],
            [
                'value' => $dto->value,
                'type' => $dto->type,
                'group' => $dto->group,
            ]
        );
    }

    public function has(string $key): bool
    {
        return $this->model->where('key', $key)->exists();
    }

    public function forget(string $key): bool
    {
        return $this->model->where('key', $key)->delete() > 0;
    }

    public function allByGroup(string $group): Collection
    {
        return $this->model->where('group', $group)->get();
    }
}
