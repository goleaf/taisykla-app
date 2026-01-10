<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public const TAG_SETTINGS = 'system_settings';

    /**
     * Check if the cache driver supports tags.
     */
    protected function supportsTags(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached', 'array']);
    }

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
        $cacheKey = "settings_group:{$group}";

        if ($this->supportsTags()) {
            return Cache::tags([self::TAG_SETTINGS])->rememberForever($cacheKey, function () use ($group) {
                return SystemSetting::where('group', $group)
                    ->pluck('value', 'key')
                    ->toArray();
            });
        }

        return Cache::rememberForever($cacheKey, function () use ($group) {
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
        $cacheKey = "settings_group:{$group}";

        if ($this->supportsTags()) {
            Cache::tags([self::TAG_SETTINGS])->forget($cacheKey);
        } else {
            Cache::forget($cacheKey);
        }
    }

    /**
     * Clear all settings cache.
     */
    public function clearAllCache(): void
    {
        if ($this->supportsTags()) {
            Cache::tags([self::TAG_SETTINGS])->flush();
        } else {
            // For non-tag drivers, clear known setting groups
            $groups = SystemSetting::distinct()->pluck('group');
            foreach ($groups as $group) {
                Cache::forget("settings_group:{$group}");
            }
        }
    }
}
