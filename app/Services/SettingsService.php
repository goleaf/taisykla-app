<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public const TAG_SETTINGS = 'system_settings';

    /**
     * Get a specific setting value.
     */
    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $groupSettings = $this->getGroup($group);
        
        return $groupSettings[$key] ?? $default;
    }

    /**
     * Get all settings for a specific group.
     */
    public function getGroup(string $group): array
    {
        return Cache::tags([self::TAG_SETTINGS])->rememberForever("settings_group:{$group}", function () use ($group) {
            return SystemSetting::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Clear cache for a specific setting group.
     */
    public function clearGroupCache(string $group): void
    {
        Cache::tags([self::TAG_SETTINGS])->forget("settings_group:{$group}");
    }

    /**
     * Clear all settings cache.
     */
    public function clearAllCache(): void
    {
        Cache::tags([self::TAG_SETTINGS])->flush();
    }
}
