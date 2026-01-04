<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkOrder;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SchedulingRecommendationService
{
    /**
     * Get recommended technicians for a work order, ranked by suitability score
     */
    public function getRecommendations(WorkOrder $workOrder, int $limit = 5): Collection
    {
        $technicians = User::role(RoleCatalog::TECHNICIAN)
            ->where('is_active', true)
            ->with([
                'workOrders' => function ($query) {
                    $query->whereIn('status', ['assigned', 'in_progress'])
                        ->whereDate('scheduled_start_at', today());
                }
            ])
            ->get();

        return $technicians
            ->map(fn(User $tech) => $this->scoreTechnician($tech, $workOrder))
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /**
     * Calculate a comprehensive score for technician-work-order match
     */
    private function scoreTechnician(User $technician, WorkOrder $workOrder): array
    {
        $factors = [];
        $weights = [
            'availability' => 30,
            'skills' => 25,
            'proximity' => 20,
            'workload' => 15,
            'history' => 10,
        ];

        // Availability Score (0-100)
        $factors['availability'] = $this->calculateAvailabilityScore($technician, $workOrder);

        // Skills Match Score (0-100)
        $factors['skills'] = $this->calculateSkillsScore($technician, $workOrder);

        // Proximity Score (0-100)
        $factors['proximity'] = $this->calculateProximityScore($technician, $workOrder);

        // Workload Score (0-100) - lower workload = higher score
        $factors['workload'] = $this->calculateWorkloadScore($technician);

        // History Score (0-100) - successful history with customer
        $factors['history'] = $this->calculateHistoryScore($technician, $workOrder);

        // Calculate weighted total
        $totalScore = 0;
        foreach ($factors as $factor => $score) {
            $totalScore += ($score / 100) * $weights[$factor];
        }

        return [
            'technician' => $technician,
            'score' => round($totalScore),
            'factors' => $factors,
            'reasons' => $this->generateReasons($factors, $technician, $workOrder),
            'availability_status' => $technician->availability_status,
            'current_workload' => $technician->workOrders->count(),
            'estimated_travel' => $this->estimateTravelTime($technician, $workOrder),
        ];
    }

    private function calculateAvailabilityScore(User $technician, WorkOrder $workOrder): int
    {
        // Base score from availability status
        $statusScores = [
            'available' => 100,
            'busy' => 40,
            'break' => 30,
            'offline' => 0,
        ];

        $baseScore = $statusScores[$technician->availability_status] ?? 50;

        // Check if scheduled time overlaps with existing appointments
        if ($workOrder->scheduled_start_at) {
            $hasConflict = $technician->appointments()
                ->where(function ($query) use ($workOrder) {
                    $start = $workOrder->scheduled_start_at;
                    $end = $workOrder->scheduled_end_at ?? $start->copy()->addHours(2);

                    $query->where('scheduled_start_at', '<', $end)
                        ->where('scheduled_end_at', '>', $start);
                })
                ->exists();

            if ($hasConflict) {
                $baseScore = max(0, $baseScore - 50);
            }
        }

        // Check working hours preference
        if ($workOrder->scheduled_start_at && $technician->working_hours_start && $technician->working_hours_end) {
            $scheduledHour = $workOrder->scheduled_start_at->format('H:i');
            if ($scheduledHour < $technician->working_hours_start || $scheduledHour > $technician->working_hours_end) {
                $baseScore = max(0, $baseScore - 30);
            }
        }

        return $baseScore;
    }

    private function calculateSkillsScore(User $technician, WorkOrder $workOrder): int
    {
        $techSkills = $technician->skills ?? [];
        if (!is_array($techSkills)) {
            $techSkills = json_decode($techSkills, true) ?? [];
        }

        if (empty($techSkills)) {
            return 50; // Neutral if no skills defined
        }

        // Determine required skills based on category
        $requiredSkills = [];
        if ($workOrder->category) {
            $categoryName = strtolower($workOrder->category->name ?? '');

            $skillMappings = [
                'hvac' => ['hvac', 'heating', 'cooling', 'refrigeration'],
                'electrical' => ['electrical', 'wiring', 'circuits'],
                'plumbing' => ['plumbing', 'pipes', 'water'],
                'computer' => ['computer', 'it', 'networking', 'hardware'],
                'appliance' => ['appliance', 'repair', 'maintenance'],
            ];

            foreach ($skillMappings as $category => $skills) {
                if (str_contains($categoryName, $category)) {
                    $requiredSkills = array_merge($requiredSkills, $skills);
                }
            }
        }

        if (empty($requiredSkills)) {
            return 70; // Good default if no specific skills required
        }

        $matchedSkills = array_intersect(
            array_map('strtolower', $techSkills),
            array_map('strtolower', $requiredSkills)
        );

        $matchRatio = count($matchedSkills) / count($requiredSkills);
        return (int) round($matchRatio * 100);
    }

    private function calculateProximityScore(User $technician, WorkOrder $workOrder): int
    {
        if (!$workOrder->location_latitude || !$workOrder->location_longitude) {
            return 50; // Neutral if no work order location
        }

        // Use technician's current location if available, otherwise territory center
        $techLat = $technician->current_latitude;
        $techLng = $technician->current_longitude;

        if (!$techLat || !$techLng) {
            // Check territory match instead
            if ($technician->territory && $workOrder->location_address) {
                $matchesTerritory = str_contains(
                    strtolower($workOrder->location_address),
                    strtolower($technician->territory)
                );
                return $matchesTerritory ? 80 : 40;
            }
            return 50;
        }

        $distance = $this->haversineDistance(
            $techLat,
            $techLng,
            $workOrder->location_latitude,
            $workOrder->location_longitude
        );

        // Score based on distance (closer = better)
        // Within 5km = 100, 10km = 80, 20km = 60, 50km = 40, 100km+ = 20
        return match (true) {
            $distance <= 5 => 100,
            $distance <= 10 => 80,
            $distance <= 20 => 60,
            $distance <= 50 => 40,
            default => 20,
        };
    }

    private function calculateWorkloadScore(User $technician): int
    {
        $todayWorkOrders = $technician->workOrders->count();

        // Score based on current workload
        // 0 tasks = 100, 1-2 = 80, 3-4 = 60, 5-6 = 40, 7+ = 20
        return match (true) {
            $todayWorkOrders === 0 => 100,
            $todayWorkOrders <= 2 => 80,
            $todayWorkOrders <= 4 => 60,
            $todayWorkOrders <= 6 => 40,
            default => 20,
        };
    }

    private function calculateHistoryScore(User $technician, WorkOrder $workOrder): int
    {
        if (!$workOrder->organization_id) {
            return 50;
        }

        // Check previous successful work with this customer
        $previousOrders = WorkOrder::where('assigned_to_user_id', $technician->id)
            ->where('organization_id', $workOrder->organization_id)
            ->whereIn('status', ['completed', 'closed'])
            ->count();

        // Check feedback ratings
        $avgRating = $technician->workOrderFeedback()
            ->whereHas('workOrder', fn($q) => $q->where('organization_id', $workOrder->organization_id))
            ->avg('rating');

        $historyScore = min(100, $previousOrders * 15); // Up to 100 from history
        $ratingBonus = $avgRating ? ($avgRating / 5) * 30 : 0;

        return (int) min(100, $historyScore + $ratingBonus);
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

    private function estimateTravelTime(User $technician, WorkOrder $workOrder): ?int
    {
        if (
            !$technician->current_latitude || !$technician->current_longitude
            || !$workOrder->location_latitude || !$workOrder->location_longitude
        ) {
            return null;
        }

        $distance = $this->haversineDistance(
            $technician->current_latitude,
            $technician->current_longitude,
            $workOrder->location_latitude,
            $workOrder->location_longitude
        );

        // Assume 35 km/h average speed
        return (int) ceil(($distance / 35) * 60);
    }

    private function generateReasons(array $factors, User $technician, WorkOrder $workOrder): array
    {
        $reasons = [];

        if ($factors['availability'] >= 80) {
            $reasons[] = 'Currently available';
        } elseif ($factors['availability'] < 50) {
            $reasons[] = 'Limited availability';
        }

        if ($factors['skills'] >= 80) {
            $reasons[] = 'Strong skill match';
        }

        if ($factors['proximity'] >= 80) {
            $reasons[] = 'Nearby location';
        }

        if ($factors['workload'] >= 80) {
            $reasons[] = 'Light workload today';
        } elseif ($factors['workload'] < 50) {
            $reasons[] = 'Heavy workload';
        }

        if ($factors['history'] >= 60) {
            $reasons[] = 'Previous experience with customer';
        }

        return $reasons;
    }

    /**
     * Auto-assign the best available technician
     */
    public function autoAssign(WorkOrder $workOrder): ?User
    {
        $recommendations = $this->getRecommendations($workOrder, 1);

        if ($recommendations->isEmpty()) {
            return null;
        }

        $best = $recommendations->first();

        // Only auto-assign if score is above threshold
        if ($best['score'] < 40) {
            return null;
        }

        return $best['technician'];
    }
}
