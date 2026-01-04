<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            app(\App\Services\ReferenceDataService::class)->clearEquipmentCategoriesCache();
        });

        static::deleted(function () {
            app(\App\Services\ReferenceDataService::class)->clearEquipmentCategoriesCache();
        });
    }
}
