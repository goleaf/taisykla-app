<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function (SystemSetting $setting) {
            app(\App\Services\SettingsService::class)->clearGroupCache($setting->group);
        });

        static::deleted(function (SystemSetting $setting) {
            app(\App\Services\SettingsService::class)->clearGroupCache($setting->group);
        });
    }
}
