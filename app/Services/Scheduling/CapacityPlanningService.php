<?php

namespace App\Services\Scheduling;

use App\Models\Appointment;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CapacityPlanningService
{
    /**
     * Get capacity metrics for all technicians
     */
    public function getTechnicianCapacityMetrics(?Carbon $date = null): Collection
    {
        $date = $date ?? today();

        return User::role(RoleCatalog::TECHNICIAN)
            ->where('is_active', true)
            ->get()
            ->map(fn(User $tech) => $this->calculateTechnicianCapacity($tech, $date));
    }

    /**
     * Calculate capacity for a single technician
     */
    public function calculateTechnicianCapacity(User $technician, Carbon $date): array
    {
        $dailyCapacity = $this->getDailyCapacity($technician, $date);
        $weeklyCapacity = $this->getWeeklyCapacity($technician, $date);
        $monthlyCapacity = $this->getMonthlyCapacity($technician, $date);

        return [
            'technician' => $technician,
            'daily' => $dailyCapacity,
            'weekly' => $weeklyCapacity,
            'monthly' => $monthlyCapacity,
            'status' => $this->getCapacityStatus($dailyCapacity['utilization']),
            'alerts' => $this->generateCapacityAlerts($dailyCapacity, $weeklyCapacity),
        ];
    }

    /**
     * Get daily capacity breakdown
     */
    private function getDailyCapacity(User $technician, Carbon $date): array
    {
        $maxMinutes = $technician->max_daily_minutes ?? 480;

        $scheduledMinutes = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereDate('scheduled_start_at', $date)
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, scheduled_start_at, scheduled_end_at)'));

        $completedMinutes = WorkOrder::where('assigned_to_user_id', $technician->id)
            ->whereDate('scheduled_start_at', $date)
            ->whereIn('status', ['completed', 'closed'])
            ->sum('labor_minutes');

        $remainingMinutes = max(0, $maxMinutes - $scheduledMinutes);
        $utilization = $maxMinutes > 0 ? round(($scheduledMinutes / $maxMinutes) * 100) : 0;

        return [
            'date' => $date->format('Y-m-d'),
            'max_minutes' => $maxMinutes,
            'scheduled_minutes' => (int) $scheduledMinutes,
            'completed_minutes' => (int) $completedMinutes,
            'remaining_minutes' => (int) $remainingMinutes,
            'utilization' => min(100, $utilization),
            'job_count' => Appointment::where('assigned_to_user_id', $technician->id)
                ->whereDate('scheduled_start_at', $date)
                ->count(),
        ];
    }

    /**
     * Get weekly capacity breakdown
     */
    private function getWeeklyCapacity(User $technician, Carbon $date): array
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();
        $maxMinutes = $technician->max_weekly_minutes ?? 2400;

        $scheduledMinutes = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereBetween('scheduled_start_at', [$weekStart, $weekEnd])
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, scheduled_start_at, scheduled_end_at)'));

        $remainingMinutes = max(0, $maxMinutes - $scheduledMinutes);
        $utilization = $maxMinutes > 0 ? round(($scheduledMinutes / $maxMinutes) * 100) : 0;

        // Daily breakdown for the week
        $dailyBreakdown = [];
        $currentDay = $weekStart->copy();
        while ($currentDay <= $weekEnd) {
            $dayMinutes = Appointment::where('assigned_to_user_id', $technician->id)
                ->whereDate('scheduled_start_at', $currentDay)
                ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, scheduled_start_at, scheduled_end_at)'));

            $dailyBreakdown[$currentDay->format('D')] = (int) $dayMinutes;
            $currentDay->addDay();
        }

        return [
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'max_minutes' => $maxMinutes,
            'scheduled_minutes' => (int) $scheduledMinutes,
            'remaining_minutes' => (int) $remainingMinutes,
            'utilization' => min(100, $utilization),
            'daily_breakdown' => $dailyBreakdown,
            'job_count' => Appointment::where('assigned_to_user_id', $technician->id)
                ->whereBetween('scheduled_start_at', [$weekStart, $weekEnd])
                ->count(),
        ];
    }

    /**
     * Get monthly capacity breakdown
     */
    private function getMonthlyCapacity(User $technician, Carbon $date): array
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();
        $workDays = $this->countWorkDays($monthStart, $monthEnd);
        $maxMinutes = ($technician->max_daily_minutes ?? 480) * $workDays;

        $scheduledMinutes = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereBetween('scheduled_start_at', [$monthStart, $monthEnd])
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, scheduled_start_at, scheduled_end_at)'));

        $utilization = $maxMinutes > 0 ? round(($scheduledMinutes / $maxMinutes) * 100) : 0;

        return [
            'month' => $date->format('F Y'),
            'work_days' => $workDays,
            'max_minutes' => $maxMinutes,
            'scheduled_minutes' => (int) $scheduledMinutes,
            'utilization' => min(100, $utilization),
            'job_count' => Appointment::where('assigned_to_user_id', $technician->id)
                ->whereBetween('scheduled_start_at', [$monthStart, $monthEnd])
                ->count(),
        ];
    }

    /**
     * Get capacity status indicator
     */
    private function getCapacityStatus(int $utilization): string
    {
        return match (true) {
            $utilization >= 100 => 'overbooked',
            $utilization >= 90 => 'near_capacity',
            $utilization >= 70 => 'optimal',
            $utilization >= 40 => 'available',
            default => 'underutilized',
        };
    }

    /**
     * Generate capacity alerts
     */
    private function generateCapacityAlerts(array $daily, array $weekly): array
    {
        $alerts = [];

        if ($daily['utilization'] >= 100) {
            $alerts[] = [
                'type' => 'overbooked',
                'severity' => 'critical',
                'message' => 'Overbooked for today',
            ];
        } elseif ($daily['utilization'] >= 90) {
            $alerts[] = [
                'type' => 'near_capacity',
                'severity' => 'warning',
                'message' => 'Near capacity for today (' . $daily['utilization'] . '%)',
            ];
        }

        if ($weekly['utilization'] >= 95) {
            $alerts[] = [
                'type' => 'weekly_limit',
                'severity' => 'warning',
                'message' => 'Approaching weekly hour limit',
            ];
        }

        if ($daily['utilization'] < 30 && $daily['job_count'] < 2) {
            $alerts[] = [
                'type' => 'underutilized',
                'severity' => 'info',
                'message' => 'Underutilized - can take more jobs',
            ];
        }

        return $alerts;
    }

    /**
     * Count work days in a range (Mon-Fri)
     */
    private function countWorkDays(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy();

        while ($current <= $end) {
            if ($current->isWeekday()) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Forecast capacity needs based on historical data
     */
    public function forecastCapacityNeeds(int $weeks = 4): array
    {
        $forecast = [];
        $currentWeek = now()->startOfWeek();

        // Analyze historical patterns
        $historicalData = $this->getHistoricalWorkloadData(12); // Last 12 weeks

        for ($i = 1; $i <= $weeks; $i++) {
            $forecastWeek = $currentWeek->copy()->addWeeks($i);

            // Simple moving average forecast
            $avgJobsPerWeek = collect($historicalData)->avg('job_count') ?? 0;
            $avgMinutesPerWeek = collect($historicalData)->avg('total_minutes') ?? 0;

            // Seasonal adjustment (simplified)
            $weekOfMonth = ceil($forecastWeek->day / 7);
            $seasonalFactor = $weekOfMonth === 1 ? 1.1 : ($weekOfMonth === 4 ? 0.9 : 1.0);

            $forecast[] = [
                'week_start' => $forecastWeek->format('Y-m-d'),
                'week_label' => $forecastWeek->format('M j'),
                'predicted_jobs' => round($avgJobsPerWeek * $seasonalFactor),
                'predicted_minutes' => round($avgMinutesPerWeek * $seasonalFactor),
                'predicted_hours' => round(($avgMinutesPerWeek * $seasonalFactor) / 60, 1),
                'confidence' => count($historicalData) >= 8 ? 'high' : 'medium',
            ];
        }

        return $forecast;
    }

    /**
     * Get historical workload data
     */
    private function getHistoricalWorkloadData(int $weeks): array
    {
        $data = [];
        $currentWeek = now()->startOfWeek();

        for ($i = 1; $i <= $weeks; $i++) {
            $weekStart = $currentWeek->copy()->subWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $workOrders = WorkOrder::whereBetween('scheduled_start_at', [$weekStart, $weekEnd])
                ->whereIn('status', ['completed', 'closed', 'assigned', 'in_progress'])
                ->get();

            $data[] = [
                'week_start' => $weekStart->format('Y-m-d'),
                'job_count' => $workOrders->count(),
                'total_minutes' => $workOrders->sum('estimated_minutes'),
            ];
        }

        return $data;
    }

    /**
     * Identify under-utilized resources
     */
    public function getUnderutilizedTechnicians(int $threshold = 50): Collection
    {
        return $this->getTechnicianCapacityMetrics()
            ->filter(fn($cap) => $cap['daily']['utilization'] < $threshold)
            ->sortBy(fn($cap) => $cap['daily']['utilization']);
    }

    /**
     * Generate hiring recommendations based on capacity trends
     */
    public function getHiringRecommendations(): array
    {
        $activeTechs = User::role(RoleCatalog::TECHNICIAN)->where('is_active', true)->count();
        $capacityMetrics = $this->getTechnicianCapacityMetrics();

        $avgUtilization = $capacityMetrics->avg(fn($m) => $m['weekly']['utilization']);
        $overbooked = $capacityMetrics->filter(fn($m) => $m['daily']['utilization'] >= 100)->count();
        $nearCapacity = $capacityMetrics->filter(fn($m) => $m['daily']['utilization'] >= 85)->count();

        $recommendations = [];

        if ($avgUtilization > 85) {
            $additionalNeeded = ceil($activeTechs * (($avgUtilization - 75) / 100));
            $recommendations[] = [
                'type' => 'hire',
                'urgency' => 'high',
                'message' => "High average utilization ({$avgUtilization}%). Consider hiring {$additionalNeeded} additional technician(s).",
                'metric' => $avgUtilization,
            ];
        }

        if ($overbooked > 0) {
            $recommendations[] = [
                'type' => 'immediate',
                'urgency' => 'critical',
                'message' => "{$overbooked} technician(s) are overbooked today.",
                'metric' => $overbooked,
            ];
        }

        if ($nearCapacity / max(1, $activeTechs) > 0.5) {
            $recommendations[] = [
                'type' => 'plan',
                'urgency' => 'medium',
                'message' => 'More than half the team is near capacity. Plan for growth.',
                'metric' => round(($nearCapacity / $activeTechs) * 100),
            ];
        }

        $forecast = $this->forecastCapacityNeeds(4);
        $forecastedGrowth = collect($forecast)->avg('predicted_jobs') - collect($this->getHistoricalWorkloadData(4))->avg('job_count');

        if ($forecastedGrowth > 5) {
            $recommendations[] = [
                'type' => 'growth',
                'urgency' => 'medium',
                'message' => "Demand is forecasted to grow. Expected +" . round($forecastedGrowth) . " jobs/week.",
                'metric' => round($forecastedGrowth),
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'stable',
                'urgency' => 'low',
                'message' => 'Current staffing levels appear adequate.',
                'metric' => $avgUtilization,
            ];
        }

        return [
            'active_technicians' => $activeTechs,
            'avg_utilization' => round($avgUtilization),
            'overbooked_count' => $overbooked,
            'near_capacity_count' => $nearCapacity,
            'recommendations' => $recommendations,
        ];
    }
}
