<?php

namespace App\Services;

use App\Models\EquipmentCategory;
use App\Models\ServiceAgreement;
use App\Models\WorkOrderCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ReferenceDataService
{
    public const TAG_REFERENCE_DATA = 'reference_data';

    public const KEY_EQUIPMENT_CATEGORIES = 'equipment_categories';
    public const KEY_WORK_ORDER_CATEGORIES = 'work_order_categories';
    public const KEY_SERVICE_AGREEMENTS = 'service_agreements';

    /**
     * Check if the cache driver supports tags.
     */
    protected function supportsTags(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached', 'array']);
    }

    /**
     * Get the cache store, with tags if supported.
     */
    protected function cache(): \Illuminate\Contracts\Cache\Repository
    {
        if ($this->supportsTags()) {
            return Cache::tags([self::TAG_REFERENCE_DATA]);
        }

        return Cache::store();
    }

    /**
     * Get all equipment categories, cached.
     *
     * @return Collection<int, EquipmentCategory>
     */
    public function getAllEquipmentCategories(): Collection
    {
        return $this->cache()->rememberForever(self::KEY_EQUIPMENT_CATEGORIES, function () {
            return EquipmentCategory::orderBy('name')->get();
        });
    }

    public function getActiveEquipmentCategories(): Collection
    {
        return $this->getAllEquipmentCategories()->where('is_active', true)->values();
    }

    /**
     * Get all work order categories (service catalog), cached.
     *
     * @return Collection<int, WorkOrderCategory>
     */
    public function getAllWorkOrderCategories(): Collection
    {
        return $this->cache()->rememberForever(self::KEY_WORK_ORDER_CATEGORIES, function () {
            return WorkOrderCategory::orderBy('name')->get();
        });
    }

    public function getActiveWorkOrderCategories(): Collection
    {
        return $this->getAllWorkOrderCategories()->where('is_active', true)->values();
    }

    /**
     * Get all service agreements, cached.
     *
     * @return Collection<int, ServiceAgreement>
     */
    public function getAllServiceAgreements(): Collection
    {
        return $this->cache()->rememberForever(self::KEY_SERVICE_AGREEMENTS, function () {
            return ServiceAgreement::orderBy('name')->get();
        });
    }

    public function getActiveServiceAgreements(): Collection
    {
        return $this->getAllServiceAgreements()->where('is_active', true)->values();
    }

    public function clearEquipmentCategoriesCache(): void
    {
        if ($this->supportsTags()) {
            Cache::tags([self::TAG_REFERENCE_DATA])->forget(self::KEY_EQUIPMENT_CATEGORIES);
        } else {
            Cache::forget(self::KEY_EQUIPMENT_CATEGORIES);
        }
    }

    public function clearWorkOrderCategoriesCache(): void
    {
        if ($this->supportsTags()) {
            Cache::tags([self::TAG_REFERENCE_DATA])->forget(self::KEY_WORK_ORDER_CATEGORIES);
        } else {
            Cache::forget(self::KEY_WORK_ORDER_CATEGORIES);
        }
    }

    public function clearServiceAgreementsCache(): void
    {
        if ($this->supportsTags()) {
            Cache::tags([self::TAG_REFERENCE_DATA])->forget(self::KEY_SERVICE_AGREEMENTS);
        } else {
            Cache::forget(self::KEY_SERVICE_AGREEMENTS);
        }
    }
}
