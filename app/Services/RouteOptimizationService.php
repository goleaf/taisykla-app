<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RouteOptimizationService
{
    /**
     * Optimize the route for a technician's work orders using nearest neighbor algorithm
     */
    public function optimizeRoute(User $technician, ?Carbon $date = null): Collection
    {
        $date = $date ?? today();

        $workOrders = WorkOrder::where('assigned_to_user_id', $technician->id)
            ->whereDate('scheduled_start_at', $date)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->whereNotNull('location_latitude')
            ->whereNotNull('location_longitude')
            ->get();

        if ($workOrders->isEmpty()) {
            return collect();
        }

        // Get starting point (technician's current location or home base)
        $startLat = $technician->current_latitude ?? $technician->home_latitude ?? null;
        $startLng = $technician->current_longitude ?? $technician->home_longitude ?? null;

        if (!$startLat || !$startLng) {
            // If no starting point, just sort by scheduled time
            return $workOrders->sortBy('scheduled_start_at')->values();
        }

        return $this->nearestNeighborOptimization($workOrders, $startLat, $startLng);
    }

    /**
     * Nearest neighbor algorithm for TSP-like route optimization
     */
    private function nearestNeighborOptimization(Collection $workOrders, float $startLat, float $startLng): Collection
    {
        $optimizedRoute = collect();
        $remaining = $workOrders->keyBy('id');
        $currentLat = $startLat;
        $currentLng = $startLng;

        while ($remaining->isNotEmpty()) {
            $nearest = null;
            $nearestDistance = PHP_FLOAT_MAX;
            $nearestId = null;

            foreach ($remaining as $id => $wo) {
                $distance = $this->haversineDistance(
                    $currentLat,
                    $currentLng,
                    $wo->location_latitude,
                    $wo->location_longitude
                );

                // Factor in time windows - penalize if we'd arrive too early or late
                $timePenalty = $this->calculateTimePenalty($wo, $optimizedRoute->count());
                $adjustedDistance = $distance + $timePenalty;

                if ($adjustedDistance < $nearestDistance) {
                    $nearestDistance = $adjustedDistance;
                    $nearest = $wo;
                    $nearestId = $id;
                }
            }

            if ($nearest) {
                $optimizedRoute->push($nearest);
                $remaining->forget($nearestId);
                $currentLat = $nearest->location_latitude;
                $currentLng = $nearest->location_longitude;
            }
        }

        return $this->addRouteMetadata($optimizedRoute, $startLat, $startLng);
    }

    /**
     * Calculate time penalty for arriving outside the scheduled window
     */
    private function calculateTimePenalty(WorkOrder $workOrder, int $position): float
    {
        if (!$workOrder->scheduled_start_at) {
            return 0;
        }

        // Estimate arrival time based on position (assuming 45 min per stop on average)
        $estimatedArrival = now()->addMinutes($position * 45 + 15);
        $scheduledTime = $workOrder->scheduled_start_at;

        $diffMinutes = $estimatedArrival->diffInMinutes($scheduledTime, false);

        // Penalty for being early (customer may not be ready)
        if ($diffMinutes > 60) {
            return abs($diffMinutes) * 0.1;
        }

        // Heavy penalty for being late
        if ($diffMinutes < -30) {
            return abs($diffMinutes) * 0.5;
        }

        // Bonus for being on time (negative penalty)
        if (abs($diffMinutes) <= 15) {
            return -5;
        }

        return 0;
    }

    /**
     * Add travel time and distance metadata to optimized route
     */
    private function addRouteMetadata(Collection $route, float $startLat, float $startLng): Collection
    {
        $currentLat = $startLat;
        $currentLng = $startLng;
        $cumulativeDistance = 0;
        $cumulativeDuration = 0;
        $sequence = 1;

        return $route->map(function (WorkOrder $wo) use (&$currentLat, &$currentLng, &$cumulativeDistance, &$cumulativeDuration, &$sequence) {
            $distance = $this->haversineDistance(
                $currentLat,
                $currentLng,
                $wo->location_latitude,
                $wo->location_longitude
            );

            // Estimate travel time (assume 35 km/h urban speed)
            $travelMinutes = (int) ceil(($distance / 35) * 60);

            $cumulativeDistance += $distance;
            $cumulativeDuration += $travelMinutes;

            // Add metadata as attributes
            $wo->route_sequence = $sequence++;
            $wo->leg_distance_km = round($distance, 1);
            $wo->leg_travel_minutes = $travelMinutes;
            $wo->cumulative_distance_km = round($cumulativeDistance, 1);
            $wo->cumulative_duration_minutes = $cumulativeDuration;
            $wo->estimated_arrival = now()->addMinutes($cumulativeDuration);

            // Update current position
            $currentLat = $wo->location_latitude;
            $currentLng = $wo->location_longitude;

            // Add estimated job duration
            $wo->estimated_completion = $wo->estimated_arrival->copy()
                ->addMinutes($wo->estimated_minutes ?? 60);

            $cumulativeDuration += ($wo->estimated_minutes ?? 60);

            return $wo;
        });
    }

    /**
     * Get route summary statistics
     */
    public function getRouteSummary(Collection $optimizedRoute): array
    {
        if ($optimizedRoute->isEmpty()) {
            return [
                'total_stops' => 0,
                'total_distance_km' => 0,
                'total_travel_minutes' => 0,
                'total_work_minutes' => 0,
                'estimated_end_time' => null,
                'efficiency_score' => 0,
            ];
        }

        $lastStop = $optimizedRoute->last();
        $totalWorkMinutes = $optimizedRoute->sum('estimated_minutes') ?? $optimizedRoute->count() * 60;

        return [
            'total_stops' => $optimizedRoute->count(),
            'total_distance_km' => $lastStop->cumulative_distance_km ?? 0,
            'total_travel_minutes' => $lastStop->cumulative_duration_minutes - $totalWorkMinutes,
            'total_work_minutes' => $totalWorkMinutes,
            'estimated_end_time' => $lastStop->estimated_completion ?? null,
            'efficiency_score' => $this->calculateEfficiencyScore($optimizedRoute),
        ];
    }

    /**
     * Calculate route efficiency score (lower travel time ratio = better)
     */
    private function calculateEfficiencyScore(Collection $route): int
    {
        $totalTravel = $route->sum('leg_travel_minutes');
        $totalWork = $route->sum('estimated_minutes') ?? $route->count() * 60;

        if ($totalWork === 0) {
            return 0;
        }

        $travelRatio = $totalTravel / $totalWork;

        // Score based on ratio (lower = better efficiency)
        // < 0.3 = excellent (90-100), 0.3-0.5 = good (70-90), 0.5-0.8 = fair (50-70), > 0.8 = poor (< 50)
        return match (true) {
            $travelRatio < 0.2 => 100,
            $travelRatio < 0.3 => 90,
            $travelRatio < 0.4 => 80,
            $travelRatio < 0.5 => 70,
            $travelRatio < 0.6 => 60,
            $travelRatio < 0.8 => 50,
            default => 30,
        };
    }

    /**
     * Detect scheduling conflicts
     */
    public function detectConflicts(Collection $optimizedRoute): Collection
    {
        $conflicts = collect();

        foreach ($optimizedRoute as $wo) {
            if (!$wo->scheduled_start_at || !$wo->estimated_arrival) {
                continue;
            }

            $arrivalTime = $wo->estimated_arrival;
            $scheduledTime = $wo->scheduled_start_at;

            $diffMinutes = $arrivalTime->diffInMinutes($scheduledTime, false);

            // Late by more than 15 minutes
            if ($diffMinutes < -15) {
                $conflicts->push([
                    'work_order' => $wo,
                    'type' => 'late_arrival',
                    'severity' => abs($diffMinutes) > 60 ? 'high' : 'medium',
                    'message' => "Estimated arrival is " . abs($diffMinutes) . " minutes late",
                    'scheduled' => $scheduledTime->format('g:i A'),
                    'estimated' => $arrivalTime->format('g:i A'),
                ]);
            }

            // Early by more than 60 minutes
            if ($diffMinutes > 60) {
                $conflicts->push([
                    'work_order' => $wo,
                    'type' => 'early_arrival',
                    'severity' => 'low',
                    'message' => "May arrive " . $diffMinutes . " minutes early",
                    'scheduled' => $scheduledTime->format('g:i A'),
                    'estimated' => $arrivalTime->format('g:i A'),
                ]);
            }
        }

        return $conflicts;
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
