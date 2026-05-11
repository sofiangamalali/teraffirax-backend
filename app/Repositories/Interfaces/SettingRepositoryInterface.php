<?php

namespace App\Repositories\Interfaces;

use App\DTOs\SettingDTO;
use App\Models\Setting;
use Illuminate\Support\Collection;

interface SettingRepositoryInterface
{
    /**
     * Get a setting by key.
     */
    public function get(string $key): ?Setting;

    /**
     * Set a setting.
     */
    public function set(SettingDTO $dto): Setting;

    /**
     * Check if a setting exists.
     */
    public function has(string $key): bool;

    /**
     * Delete a setting.
     */
    public function forget(string $key): bool;

    /**
     * Get all settings by group.
     */
    public function allByGroup(string $group): Collection;
}
