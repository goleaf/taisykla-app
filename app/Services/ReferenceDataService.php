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
     * Get all equipment categories, cached.
     *
     * @return Collection<int, EquipmentCategory>
     */
    public function getAllEquipmentCategories(): Collection
    {
        return Cache::tags([self::TAG_REFERENCE_DATA])->rememberForever(self::KEY_EQUIPMENT_CATEGORIES, function () {
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
        return Cache::tags([self::TAG_REFERENCE_DATA])->rememberForever(self::KEY_WORK_ORDER_CATEGORIES, function () {
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
        return Cache::tags([self::TAG_REFERENCE_DATA])->rememberForever(self::KEY_SERVICE_AGREEMENTS, function () {
            return ServiceAgreement::orderBy('name')->get();
        });
    }

    public function getActiveServiceAgreements(): Collection
    {
        return $this->getAllServiceAgreements()->where('is_active', true)->values();
    }
    
    public function clearEquipmentCategoriesCache(): void
    {
        Cache::tags([self::TAG_REFERENCE_DATA])->forget(self::KEY_EQUIPMENT_CATEGORIES);
    }

    public function clearWorkOrderCategoriesCache(): void
    {
        Cache::tags([self::TAG_REFERENCE_DATA])->forget(self::KEY_WORK_ORDER_CATEGORIES);
    }

    public function clearServiceAgreementsCache(): void
    {
        Cache::tags([self::TAG_REFERENCE_DATA])->forget(self::KEY_SERVICE_AGREEMENTS);
    }
}
