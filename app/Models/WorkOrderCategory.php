<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'default_estimated_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'category_id');
    }

    protected static function booted(): void
    {
        static::saved(function () {
            app(\App\Services\ReferenceDataService::class)->clearWorkOrderCategoriesCache();
        });

        static::deleted(function () {
            app(\App\Services\ReferenceDataService::class)->clearWorkOrderCategoriesCache();
        });
    }
}
