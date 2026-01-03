<?php

namespace App\Support;

use App\Models\LabelOverride;
use Illuminate\Support\Facades\Cache;

class LabelCatalog
{
    public static function label(string $key, string $default = ''): string
    {
        $locale = app()->getLocale();

        $labels = self::labelsForLocale($locale);
        if (array_key_exists($key, $labels)) {
            return $labels[$key];
        }

        $fallbackLocale = config('app.fallback_locale', 'en');
        if ($fallbackLocale !== $locale) {
            $fallbackLabels = self::labelsForLocale($fallbackLocale);
            if (array_key_exists($key, $fallbackLabels)) {
                return $fallbackLabels[$key];
            }
        }

        $globalLabels = self::labelsForLocale('*');
        if (array_key_exists($key, $globalLabels)) {
            return $globalLabels[$key];
        }

        return $default !== '' ? $default : $key;
    }

    public static function clearCache(?string $locale = null): void
    {
        if ($locale) {
            Cache::forget(self::cacheKey($locale));
        }

        Cache::forget(self::cacheKey('*'));
    }

    private static function labelsForLocale(string $locale): array
    {
        return Cache::remember(self::cacheKey($locale), 3600, function () use ($locale) {
            return LabelOverride::where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    private static function cacheKey(string $locale): string
    {
        return 'customization.labels.' . $locale;
    }
}
