<?php

namespace App\Services;

use App\DTOs\SettingDTO;
use App\Enums\SettingKeyEnum;

use App\Models\Setting;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class SettingService
{
    public function __construct(
        protected SettingRepositoryInterface $settingRepository,
    ) {}

    /**
     * Get a setting value.
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = "setting:{$key}";

        return Cache::rememberForever($cacheKey, function () use ($key, $default) {
            $setting = $this->settingRepository->get($key);

            if (!$setting) {
                return $default;
            }

            return Setting::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value.
     */
    public function set(SettingDTO $dto): Setting
    {
        $setting = $this->settingRepository->set($dto);

        // Invalidate cache
        Cache::forget("setting:{$dto->key}");

        return $setting;
    }

    /**
     * Check if a setting exists.
     */
    public function has(string $key): bool
    {
        return $this->settingRepository->has($key);
    }

    /**
     * Delete a setting.
     */
    public function forget(string $key, ?int $userId = null): bool
    {
        $deleted = $this->settingRepository->forget($key);

        if ($deleted) {
            Cache::forget("setting:{$key}");
        }

        return $deleted;
    }

    /**
     * Get all settings by group as a key-value array.
     */
    public function allByGroup(string $group): array
    {
        $settings = $this->settingRepository->allByGroup($group);
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = Setting::castValue($setting->value, $setting->type);
        }

        return $result;
    }
}
