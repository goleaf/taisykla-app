<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Warranty;
use Illuminate\Support\Carbon;

class EquipmentLifecycleService
{
    public function determineStatus(Equipment $equipment): string
    {
        // Priority order matters here
        if ($equipment->status === Equipment::STATUS_DECOMMISSIONED) {
            return Equipment::LIFECYCLE_DECOMMISSIONED;
        }

        if ($equipment->status === Equipment::STATUS_RETIRED) {
            return Equipment::LIFECYCLE_DECOMMISSIONED;
        }

        $score = $equipment->health_score ?? 50;
        $agePercentage = $this->getAgePercentage($equipment);
        $hasActiveWarranty = $equipment->has_active_warranty;
        $warrantyExpiringSoon = $this->isWarrantyExpiringSoon($equipment);

        // Check if needs replacement (health < 35% or age > 100%)
        if ($score < 35 || $agePercentage > 100) {
            return Equipment::LIFECYCLE_NEEDS_REPLACEMENT;
        }

        // Check if warranty is expiring soon
        if ($warrantyExpiringSoon) {
            return Equipment::LIFECYCLE_WARRANTY_EXPIRING;
        }

        // Check if recently purchased (< 3 months)
        if ($equipment->purchase_date && $equipment->purchase_date->diffInMonths(Carbon::today()) < 3) {
            return Equipment::LIFECYCLE_NEW;
        }

        return Equipment::LIFECYCLE_OPERATIONAL;
    }

    public function progressStatus(Equipment $equipment): array
    {
        $previousStatus = $equipment->lifecycle_status;
        $newStatus = $this->determineStatus($equipment);

        if ($previousStatus !== $newStatus) {
            $equipment->update(['lifecycle_status' => $newStatus]);

            return [
                'changed' => true,
                'previous' => $previousStatus,
                'current' => $newStatus,
            ];
        }

        return [
            'changed' => false,
            'previous' => $previousStatus,
            'current' => $newStatus,
        ];
    }

    public function getLifecycleTimeline(Equipment $equipment): array
    {
        $timeline = [];

        // Purchase date
        if ($equipment->purchase_date) {
            $timeline[] = [
                'date' => $equipment->purchase_date,
                'event' => 'Purchased',
                'status' => Equipment::LIFECYCLE_NEW,
                'icon' => 'o-shopping-cart',
                'color' => 'success',
            ];
        }

        // Warranty start
        $firstWarranty = $equipment->warranties()->orderBy('starts_at')->first();
        if ($firstWarranty && $firstWarranty->starts_at) {
            $timeline[] = [
                'date' => $firstWarranty->starts_at,
                'event' => 'Warranty Started',
                'status' => Equipment::LIFECYCLE_OPERATIONAL,
                'icon' => 'o-shield-check',
                'color' => 'info',
            ];
        }

        // First service event
        $firstService = $equipment->serviceEvents()->orderBy('completed_at')->first();
        if ($firstService && $firstService->completed_at) {
            $timeline[] = [
                'date' => $firstService->completed_at,
                'event' => 'First Service',
                'status' => Equipment::LIFECYCLE_OPERATIONAL,
                'icon' => 'o-wrench',
                'color' => 'info',
            ];
        }

        // Warranty expiration
        $latestWarranty = $equipment->warranties()->orderByDesc('ends_at')->first();
        if ($latestWarranty && $latestWarranty->ends_at) {
            $timeline[] = [
                'date' => $latestWarranty->ends_at,
                'event' => $latestWarranty->ends_at->isPast() ? 'Warranty Expired' : 'Warranty Expires',
                'status' => Equipment::LIFECYCLE_WARRANTY_EXPIRING,
                'icon' => 'o-shield-exclamation',
                'color' => 'warning',
                'is_future' => $latestWarranty->ends_at->isFuture(),
            ];
        }

        // Expected end of life
        if ($equipment->purchase_date && $equipment->expected_lifespan_months) {
            $endOfLife = $equipment->purchase_date->copy()->addMonths($equipment->expected_lifespan_months);
            $timeline[] = [
                'date' => $endOfLife,
                'event' => $endOfLife->isPast() ? 'Expected EOL Passed' : 'Expected End of Life',
                'status' => Equipment::LIFECYCLE_NEEDS_REPLACEMENT,
                'icon' => 'o-x-circle',
                'color' => 'error',
                'is_future' => $endOfLife->isFuture(),
            ];
        }

        // Sort by date
        usort($timeline, fn($a, $b) => $a['date']->timestamp - $b['date']->timestamp);

        return $timeline;
    }

    public function getLifecycleStats(Equipment $equipment): array
    {
        $now = Carbon::now();
        $purchaseDate = $equipment->purchase_date;
        $expectedLifespan = $equipment->expected_lifespan_months ?? 60;

        $ageMonths = $purchaseDate ? $purchaseDate->diffInMonths($now) : null;
        $remainingMonths = $purchaseDate
            ? max(0, $expectedLifespan - $ageMonths)
            : null;

        $activeWarranty = $equipment->active_warranty;

        return [
            'age_months' => $ageMonths,
            'age_years' => $ageMonths ? round($ageMonths / 12, 1) : null,
            'expected_lifespan_months' => $expectedLifespan,
            'remaining_months' => $remainingMonths,
            'remaining_years' => $remainingMonths ? round($remainingMonths / 12, 1) : null,
            'lifecycle_percentage' => $ageMonths && $expectedLifespan
                ? min(100, round(($ageMonths / $expectedLifespan) * 100, 1))
                : null,
            'warranty_active' => (bool) $activeWarranty,
            'warranty_days_remaining' => $activeWarranty?->days_remaining,
            'total_service_events' => $equipment->serviceEvents()->count(),
            'total_service_cost' => $equipment->total_service_cost,
        ];
    }

    public function batchUpdateLifecycleStatus(?int $limit = 100): int
    {
        $equipment = Equipment::query()
            ->whereIn('status', [Equipment::STATUS_OPERATIONAL, Equipment::STATUS_NEEDS_ATTENTION])
            ->limit($limit)
            ->get();

        $updated = 0;
        foreach ($equipment as $item) {
            $result = $this->progressStatus($item);
            if ($result['changed']) {
                $updated++;
            }
        }

        return $updated;
    }

    // ─── Private Helpers ──────────────────────────────────────────────

    private function getAgePercentage(Equipment $equipment): float
    {
        if (!$equipment->purchase_date || !$equipment->expected_lifespan_months) {
            return 0;
        }

        $ageMonths = $equipment->purchase_date->diffInMonths(Carbon::today());

        return ($ageMonths / $equipment->expected_lifespan_months) * 100;
    }

    private function isWarrantyExpiringSoon(Equipment $equipment, int $thresholdDays = 30): bool
    {
        $activeWarranty = $equipment->active_warranty;

        if (!$activeWarranty) {
            return false;
        }

        return $activeWarranty->days_remaining <= $thresholdDays;
    }
}
