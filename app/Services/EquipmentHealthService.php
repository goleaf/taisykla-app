<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EquipmentHealthService
{
    // Weight factors for health calculation (total = 100%)
    private const WEIGHT_AGE = 0.30;
    private const WEIGHT_SERVICE_FREQUENCY = 0.25;
    private const WEIGHT_REPAIR_COST_TREND = 0.20;
    private const WEIGHT_DOWNTIME = 0.15;
    private const WEIGHT_CRITICAL_FAILURES = 0.10;

    public function calculateScore(Equipment $equipment): int
    {
        $equipment->load(['serviceEvents', 'workOrders', 'warranties']);

        $scores = [
            'age' => $this->calculateAgeScore($equipment),
            'service_frequency' => $this->calculateServiceFrequencyScore($equipment),
            'repair_cost' => $this->calculateRepairCostTrendScore($equipment),
            'downtime' => $this->calculateDowntimeScore($equipment),
            'critical_failures' => $this->calculateCriticalFailuresScore($equipment),
        ];

        $weightedScore =
            ($scores['age'] * self::WEIGHT_AGE) +
            ($scores['service_frequency'] * self::WEIGHT_SERVICE_FREQUENCY) +
            ($scores['repair_cost'] * self::WEIGHT_REPAIR_COST_TREND) +
            ($scores['downtime'] * self::WEIGHT_DOWNTIME) +
            ($scores['critical_failures'] * self::WEIGHT_CRITICAL_FAILURES);

        return (int) round(max(0, min(100, $weightedScore)));
    }

    public function getHealthGrade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    public function getHealthLabel(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 50 => 'Fair',
            $score >= 25 => 'Poor',
            default => 'Critical',
        };
    }

    public function getHealthColor(int $score): string
    {
        return match (true) {
            $score >= 80 => 'success',
            $score >= 60 => 'info',
            $score >= 40 => 'warning',
            default => 'error',
        };
    }

    public function getPredictiveAlerts(Equipment $equipment): Collection
    {
        $alerts = collect();
        $score = $this->calculateScore($equipment);

        // Low health score alert
        if ($score < 40) {
            $alerts->push([
                'type' => 'critical',
                'title' => 'Equipment Health Critical',
                'message' => 'This equipment has a health score below 40% and may need immediate attention or replacement.',
                'icon' => 'o-exclamation-triangle',
            ]);
        } elseif ($score < 60) {
            $alerts->push([
                'type' => 'warning',
                'title' => 'Equipment Health Declining',
                'message' => 'This equipment is showing signs of wear. Consider scheduling preventive maintenance.',
                'icon' => 'o-exclamation-circle',
            ]);
        }

        // Age-based alerts
        if ($equipment->expected_lifespan_months && $equipment->purchase_date) {
            $ageMonths = $equipment->purchase_date->diffInMonths(Carbon::today());
            $lifespanPercentage = ($ageMonths / $equipment->expected_lifespan_months) * 100;

            if ($lifespanPercentage >= 100) {
                $alerts->push([
                    'type' => 'critical',
                    'title' => 'Beyond Expected Lifespan',
                    'message' => 'This equipment has exceeded its expected lifespan. Consider replacement planning.',
                    'icon' => 'o-clock',
                ]);
            } elseif ($lifespanPercentage >= 80) {
                $alerts->push([
                    'type' => 'warning',
                    'title' => 'Approaching End of Lifespan',
                    'message' => sprintf('This equipment is at %d%% of its expected lifespan.', (int) $lifespanPercentage),
                    'icon' => 'o-clock',
                ]);
            }
        }

        // Warranty expiration alert
        $activeWarranty = $equipment->active_warranty;
        if ($activeWarranty && $activeWarranty->is_expiring_soon) {
            $alerts->push([
                'type' => 'info',
                'title' => 'Warranty Expiring Soon',
                'message' => sprintf('Warranty expires in %d days. Consider renewal options.', $activeWarranty->days_remaining),
                'icon' => 'o-shield-check',
            ]);
        }

        // Frequent repairs alert
        $recentRepairs = $equipment->serviceEvents()
            ->where('event_type', 'repair')
            ->where('completed_at', '>=', now()->subMonths(6))
            ->count();

        if ($recentRepairs >= 3) {
            $alerts->push([
                'type' => 'warning',
                'title' => 'Frequent Repairs',
                'message' => sprintf('%d repairs in the last 6 months. This equipment may be unreliable.', $recentRepairs),
                'icon' => 'o-wrench',
            ]);
        }

        // Overdue maintenance alert
        if ($equipment->next_maintenance_due_at && $equipment->next_maintenance_due_at->isPast()) {
            $alerts->push([
                'type' => 'warning',
                'title' => 'Maintenance Overdue',
                'message' => 'Scheduled maintenance is overdue. Schedule service soon.',
                'icon' => 'o-calendar',
            ]);
        }

        return $alerts;
    }

    public function getReplacementRecommendation(Equipment $equipment): ?array
    {
        $score = $this->calculateScore($equipment);

        if ($score >= 50) {
            return null;
        }

        $reason = match (true) {
            $score < 20 => 'Equipment is in critical condition with minimal remaining useful life.',
            $score < 35 => 'Equipment health is poor. Repair costs may exceed replacement value.',
            default => 'Equipment is showing significant wear. Plan for replacement in the near future.',
        };

        // Estimate based on age and service events
        $totalRepairCost = $equipment->serviceEvents()->sum('labor_cost') + $equipment->serviceEvents()->sum('parts_cost');
        $purchasePrice = $equipment->purchase_price ?? 0;

        return [
            'should_replace' => $score < 35,
            'urgency' => $score < 20 ? 'immediate' : ($score < 35 ? 'soon' : 'planned'),
            'reason' => $reason,
            'health_score' => $score,
            'total_repair_cost' => $totalRepairCost,
            'original_cost' => $purchasePrice,
            'repair_to_cost_ratio' => $purchasePrice > 0 ? round($totalRepairCost / $purchasePrice * 100, 1) : null,
        ];
    }

    public function updateEquipmentHealthScore(Equipment $equipment): void
    {
        $score = $this->calculateScore($equipment);
        $equipment->update(['health_score' => $score]);
    }

    public function batchUpdateHealthScores(?int $limit = 100): int
    {
        $equipment = Equipment::query()
            ->where(function ($q) {
                $q->whereNull('health_score')
                    ->orWhere('updated_at', '<', now()->subDay());
            })
            ->limit($limit)
            ->get();

        foreach ($equipment as $item) {
            $this->updateEquipmentHealthScore($item);
        }

        return $equipment->count();
    }

    // ─── Private Score Calculation Methods ────────────────────────────

    private function calculateAgeScore(Equipment $equipment): float
    {
        if (!$equipment->purchase_date) {
            return 70; // Default middle score if no purchase date
        }

        $ageMonths = $equipment->purchase_date->diffInMonths(Carbon::today());
        $expectedLifespan = $equipment->expected_lifespan_months ?? 60; // Default 5 years

        $agePercentage = min(150, ($ageMonths / $expectedLifespan) * 100);

        // Score decreases as age percentage increases
        return max(0, 100 - ($agePercentage * 0.67));
    }

    private function calculateServiceFrequencyScore(Equipment $equipment): float
    {
        $serviceCount = $equipment->serviceEvents()->count();
        $ageMonths = $equipment->purchase_date
            ? max(1, $equipment->purchase_date->diffInMonths(Carbon::today()))
            : 12;

        $servicesPerYear = ($serviceCount / $ageMonths) * 12;

        // 0-1 service/year = excellent, 2-3 = good, 4+ = concerning
        return match (true) {
            $servicesPerYear <= 1 => 100,
            $servicesPerYear <= 2 => 80,
            $servicesPerYear <= 3 => 60,
            $servicesPerYear <= 5 => 40,
            default => 20,
        };
    }

    private function calculateRepairCostTrendScore(Equipment $equipment): float
    {
        $purchasePrice = $equipment->purchase_price;
        if (!$purchasePrice || $purchasePrice <= 0) {
            return 70; // Default if no purchase price
        }

        $totalRepairCost = $equipment->serviceEvents()
            ->where('event_type', 'repair')
            ->selectRaw('SUM(labor_cost + parts_cost) as total')
            ->value('total') ?? 0;

        $costPercentage = ($totalRepairCost / $purchasePrice) * 100;

        return match (true) {
            $costPercentage <= 10 => 100,
            $costPercentage <= 25 => 80,
            $costPercentage <= 50 => 60,
            $costPercentage <= 75 => 40,
            default => max(0, 100 - $costPercentage),
        };
    }

    private function calculateDowntimeScore(Equipment $equipment): float
    {
        $completedWorkOrders = $equipment->workOrders()
            ->whereNotNull('completed_at')
            ->get();

        if ($completedWorkOrders->isEmpty()) {
            return 100;
        }

        $totalDowntimeHours = 0;
        foreach ($completedWorkOrders as $wo) {
            if ($wo->started_at && $wo->completed_at) {
                $totalDowntimeHours += $wo->started_at->diffInHours($wo->completed_at);
            }
        }

        $ageMonths = max(1, $equipment->purchase_date?->diffInMonths(Carbon::today()) ?? 12);
        $downtimePerMonth = $totalDowntimeHours / $ageMonths;

        return match (true) {
            $downtimePerMonth <= 2 => 100,
            $downtimePerMonth <= 8 => 80,
            $downtimePerMonth <= 24 => 60,
            $downtimePerMonth <= 48 => 40,
            default => 20,
        };
    }

    private function calculateCriticalFailuresScore(Equipment $equipment): float
    {
        // Count high-priority work orders as critical failures
        $criticalFailures = $equipment->workOrders()
            ->whereIn('priority', ['urgent', 'emergency'])
            ->whereNotNull('completed_at')
            ->count();

        return match (true) {
            $criticalFailures === 0 => 100,
            $criticalFailures === 1 => 80,
            $criticalFailures <= 3 => 60,
            $criticalFailures <= 5 => 40,
            default => 20,
        };
    }
}
