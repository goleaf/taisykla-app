<?php

namespace App\Services\Scheduling;

use App\Models\User;
use App\Models\WorkOrder;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EnhancedAssignmentEngine
{
    protected ScheduleConflictDetector $conflictDetector;
    protected CapacityPlanningService $capacityPlanning;
    protected AutomatedSchedulingRules $schedulingRules;

    public function __construct(
        ScheduleConflictDetector $conflictDetector,
        CapacityPlanningService $capacityPlanning,
        AutomatedSchedulingRules $schedulingRules
    ) {
        $this->conflictDetector = $conflictDetector;
        $this->capacityPlanning = $capacityPlanning;
        $this->schedulingRules = $schedulingRules;
    }

    /**
     * Get comprehensive recommendations for a work order
     */
    public function getRecommendations(WorkOrder $workOrder, int $limit = 5): Collection
    {
        $technicians = User::role(RoleCatalog::TECHNICIAN)
            ->where('is_active', true)
            ->get();

        $recommendations = $technicians
            ->map(fn(User $tech) => $this->evaluateTechnician($tech, $workOrder))
            ->filter(fn($rec) => $rec['score'] > 0)
            ->sortByDesc('score')
            ->take($limit)
            ->values();

        // Add rank
        return $recommendations->map(function ($rec, $index) {
            $rec['rank'] = $index + 1;
            return $rec;
        });
    }

    /**
     * Comprehensive technician evaluation
     */
    protected function evaluateTechnician(User $technician, WorkOrder $workOrder): array
    {
        $factors = [];
        $weights = [
            'skills' => 25,
            'certifications' => 20,
            'proximity' => 20,
            'availability' => 15,
            'workload' => 10,
            'customer_history' => 5,
            'performance' => 5,
        ];

        // Calculate each factor
        $factors['skills'] = $this->evaluateSkillMatch($technician, $workOrder);
        $factors['certifications'] = $this->evaluateCertifications($technician, $workOrder);
        $factors['proximity'] = $this->evaluateProximity($technician, $workOrder);
        $factors['availability'] = $this->evaluateAvailability($technician, $workOrder);
        $factors['workload'] = $this->evaluateWorkload($technician, $workOrder);
        $factors['customer_history'] = $this->evaluateCustomerHistory($technician, $workOrder);
        $factors['performance'] = $this->evaluatePerformance($technician, $workOrder);

        // Calculate weighted score
        $totalScore = 0;
        foreach ($factors as $factor => $score) {
            $totalScore += ($score / 100) * $weights[$factor];
        }

        // Generate pros and cons
        $prosAndCons = $this->generateProsAndCons($factors, $technician, $workOrder);

        // Estimate impact and timing
        $impact = $this->calculateScheduleImpact($technician, $workOrder);
        $estimatedStart = $this->calculateEstimatedStart($technician, $workOrder);

        // Check for conflicts
        $validation = $this->validateAssignment($technician, $workOrder);

        return [
            'technician' => $technician,
            'technician_id' => $technician->id,
            'name' => $technician->name,
            'score' => round($totalScore),
            'match_percentage' => min(100, round($totalScore)),
            'factors' => $factors,
            'weights' => $weights,
            'pros' => $prosAndCons['pros'],
            'cons' => $prosAndCons['cons'],
            'impact' => $impact,
            'estimated_start' => $estimatedStart,
            'has_conflicts' => $validation['has_critical'],
            'conflicts' => $validation['conflicts'],
            'availability_status' => $technician->availability_status,
            'travel_time' => $this->estimateTravelTime($technician, $workOrder),
        ];
    }

    /**
     * Evaluate skill level match
     */
    protected function evaluateSkillMatch(User $technician, WorkOrder $workOrder): int
    {
        $techSkills = $technician->skills ?? [];
        if (!is_array($techSkills)) {
            $techSkills = json_decode($techSkills, true) ?? [];
        }

        $requiredSkills = $workOrder->required_skills ?? [];

        // If no requirements, neutral score
        if (empty($requiredSkills)) {
            return ($technician->skill_level ?? 3) * 20;
        }

        $techSkills = array_map('strtolower', $techSkills);
        $requiredSkills = array_map('strtolower', $requiredSkills);

        $matched = count(array_intersect($techSkills, $requiredSkills));
        $matchRatio = count($requiredSkills) > 0 ? $matched / count($requiredSkills) : 0;

        // Combine skill match with skill level
        $levelScore = (($technician->skill_level ?? 3) / 5) * 50;
        $matchScore = $matchRatio * 50;

        return (int) ($levelScore + $matchScore);
    }

    /**
     * Evaluate certification requirements
     */
    protected function evaluateCertifications(User $technician, WorkOrder $workOrder): int
    {
        $techCerts = $technician->certifications ?? [];
        if (!is_array($techCerts)) {
            $techCerts = json_decode($techCerts, true) ?? [];
        }

        $requiredCerts = $workOrder->required_certifications ?? [];

        // Check equipment-based requirements
        if ($workOrder->equipment) {
            $equipmentType = strtolower($workOrder->equipment->category?->name ?? '');

            $certMappings = [
                'hvac' => ['hvac certified', 'epa 608'],
                'electrical' => ['licensed electrician'],
                'refrigeration' => ['epa 608'],
            ];

            foreach ($certMappings as $type => $certs) {
                if (str_contains($equipmentType, $type)) {
                    $requiredCerts = array_merge($requiredCerts, $certs);
                }
            }
        }

        if (empty($requiredCerts)) {
            return 70; // Neutral if no requirements
        }

        $techCerts = array_map('strtolower', $techCerts);
        $requiredCerts = array_unique(array_map('strtolower', $requiredCerts));

        $matched = count(array_intersect($techCerts, $requiredCerts));
        $total = count($requiredCerts);

        return (int) (($matched / $total) * 100);
    }

    /**
     * Evaluate proximity to job location
     */
    protected function evaluateProximity(User $technician, WorkOrder $workOrder): int
    {
        if (!$workOrder->location_latitude || !$workOrder->location_longitude) {
            return 50;
        }

        $techLat = $technician->current_latitude;
        $techLng = $technician->current_longitude;

        if (!$techLat || !$techLng) {
            // Check territory match
            if ($technician->territory && $workOrder->location_address) {
                return str_contains(
                    strtolower($workOrder->location_address),
                    strtolower($technician->territory)
                ) ? 80 : 40;
            }
            return 50;
        }

        $distance = $this->haversineDistance(
            $techLat,
            $techLng,
            $workOrder->location_latitude,
            $workOrder->location_longitude
        );

        return match (true) {
            $distance <= 5 => 100,
            $distance <= 10 => 90,
            $distance <= 20 => 75,
            $distance <= 35 => 55,
            $distance <= 50 => 40,
            default => 20,
        };
    }

    /**
     * Evaluate availability
     */
    protected function evaluateAvailability(User $technician, WorkOrder $workOrder): int
    {
        $baseScores = [
            'available' => 100,
            'busy' => 50,
            'break' => 30,
            'offline' => 0,
            'emergency' => 10,
        ];

        $score = $baseScores[$technician->availability_status] ?? 50;

        // Check for conflicts at scheduled time
        if ($workOrder->scheduled_start_at) {
            $duration = $workOrder->estimated_minutes ?? 60;
            $validation = $this->conflictDetector->validateAssignment(
                $workOrder,
                $technician,
                $workOrder->scheduled_start_at,
                $duration
            );

            if ($validation['has_critical']) {
                $score = max(0, $score - 60);
            } elseif ($validation['has_warning']) {
                $score = max(0, $score - 20);
            }
        }

        return $score;
    }

    /**
     * Evaluate current workload
     */
    protected function evaluateWorkload(User $technician, WorkOrder $workOrder): int
    {
        $capacityMetrics = $this->capacityPlanning->calculateTechnicianCapacity($technician, today());

        $dailyUtilization = $capacityMetrics['daily']['utilization'];

        // Prefer technicians with available capacity
        return match (true) {
            $dailyUtilization >= 100 => 0,
            $dailyUtilization >= 90 => 20,
            $dailyUtilization >= 75 => 50,
            $dailyUtilization >= 50 => 80,
            $dailyUtilization >= 25 => 100,
            default => 90, // Very light load might indicate issues
        };
    }

    /**
     * Evaluate customer history
     */
    protected function evaluateCustomerHistory(User $technician, WorkOrder $workOrder): int
    {
        if (!$workOrder->organization_id) {
            return 50;
        }

        // Check if customer preferred this technician
        $preferredIds = $workOrder->preferred_technician_ids ?? [];
        if ($workOrder->preferred_technician_id) {
            $preferredIds[] = $workOrder->preferred_technician_id;
        }

        if (in_array($technician->id, $preferredIds)) {
            return 100;
        }

        // Check history with this customer
        $previousJobs = WorkOrder::where('assigned_to_user_id', $technician->id)
            ->where('organization_id', $workOrder->organization_id)
            ->whereIn('status', ['completed', 'closed'])
            ->count();

        // Check ratings from this customer
        $avgRating = $technician->workOrderFeedback()
            ->whereHas('workOrder', fn($q) => $q->where('organization_id', $workOrder->organization_id))
            ->avg('rating');

        $historyScore = min(50, $previousJobs * 10);
        $ratingScore = $avgRating ? (($avgRating / 5) * 50) : 25;

        return (int) ($historyScore + $ratingScore);
    }

    /**
     * Evaluate overall performance
     */
    protected function evaluatePerformance(User $technician, WorkOrder $workOrder): int
    {
        // Average rating across all jobs
        $avgRating = $technician->workOrderFeedback()->avg('rating') ?? 4;

        // On-time completion rate
        $totalCompleted = WorkOrder::where('assigned_to_user_id', $technician->id)
            ->whereIn('status', ['completed', 'closed'])
            ->count();

        $onTimeCompleted = WorkOrder::where('assigned_to_user_id', $technician->id)
            ->whereIn('status', ['completed', 'closed'])
            ->whereColumn('completed_at', '<=', 'scheduled_end_at')
            ->count();

        $onTimeRate = $totalCompleted > 0 ? ($onTimeCompleted / $totalCompleted) : 0.8;

        $ratingScore = ($avgRating / 5) * 60;
        $onTimeScore = $onTimeRate * 40;

        return (int) ($ratingScore + $onTimeScore);
    }

    /**
     * Generate pros and cons list
     */
    protected function generateProsAndCons(array $factors, User $technician, WorkOrder $workOrder): array
    {
        $pros = [];
        $cons = [];

        // Skills
        if ($factors['skills'] >= 80) {
            $pros[] = 'Strong skill match for this job';
        } elseif ($factors['skills'] <= 40) {
            $cons[] = 'Limited relevant skills';
        }

        // Certifications
        if ($factors['certifications'] >= 80) {
            $pros[] = 'Has required certifications';
        } elseif ($factors['certifications'] <= 40) {
            $cons[] = 'Missing required certifications';
        }

        // Proximity
        if ($factors['proximity'] >= 80) {
            $pros[] = 'Close to job location';
        } elseif ($factors['proximity'] <= 40) {
            $cons[] = 'Far from job location (longer travel time)';
        }

        // Availability
        if ($factors['availability'] >= 80) {
            $pros[] = 'Currently available';
        } elseif ($factors['availability'] <= 40) {
            $cons[] = 'Limited availability at scheduled time';
        }

        // Workload
        if ($factors['workload'] >= 80) {
            $pros[] = 'Light workload today';
        } elseif ($factors['workload'] <= 30) {
            $cons[] = 'Heavy workload today';
        }

        // Customer history
        if ($factors['customer_history'] >= 80) {
            $pros[] = 'Customer has worked with this technician before';
        }

        // Performance
        if ($factors['performance'] >= 80) {
            $pros[] = 'Excellent track record';
        } elseif ($factors['performance'] <= 40) {
            $cons[] = 'Performance concerns';
        }

        return ['pros' => $pros, 'cons' => $cons];
    }

    /**
     * Calculate schedule impact
     */
    protected function calculateScheduleImpact(User $technician, WorkOrder $workOrder): array
    {
        $capacity = $this->capacityPlanning->calculateTechnicianCapacity($technician, today());
        $jobMinutes = $workOrder->estimated_minutes ?? 60;

        $currentJobs = $capacity['daily']['job_count'];
        $currentUtilization = $capacity['daily']['utilization'];
        $newUtilization = min(100, $currentUtilization + (($jobMinutes / ($technician->max_daily_minutes ?? 480)) * 100));

        return [
            'current_jobs' => $currentJobs,
            'jobs_after_assignment' => $currentJobs + 1,
            'current_utilization' => $currentUtilization . '%',
            'utilization_after' => round($newUtilization) . '%',
            'remaining_capacity_minutes' => max(0, $capacity['daily']['remaining_minutes'] - $jobMinutes),
            'overtime_required' => $newUtilization > 100,
        ];
    }

    /**
     * Calculate estimated start time
     */
    protected function calculateEstimatedStart(User $technician, WorkOrder $workOrder): ?Carbon
    {
        if ($workOrder->scheduled_start_at) {
            return $workOrder->scheduled_start_at;
        }

        // Find next available slot
        $slots = $this->conflictDetector->findAvailableSlots(
            $technician,
            today(),
            $workOrder->estimated_minutes ?? 60
        );

        if (!empty($slots)) {
            return $slots[0]['start'];
        }

        // Check tomorrow
        $tomorrowSlots = $this->conflictDetector->findAvailableSlots(
            $technician,
            today()->addDay(),
            $workOrder->estimated_minutes ?? 60
        );

        return !empty($tomorrowSlots) ? $tomorrowSlots[0]['start'] : null;
    }

    /**
     * Validate assignment
     */
    protected function validateAssignment(User $technician, WorkOrder $workOrder): array
    {
        if (!$workOrder->scheduled_start_at) {
            return ['has_critical' => false, 'has_warning' => false, 'conflicts' => collect()];
        }

        return $this->conflictDetector->validateAssignment(
            $workOrder,
            $technician,
            $workOrder->scheduled_start_at,
            $workOrder->estimated_minutes ?? 60
        );
    }

    /**
     * Estimate travel time
     */
    protected function estimateTravelTime(User $technician, WorkOrder $workOrder): ?int
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

        return (int) ceil(($distance / 35) * 60); // 35 km/h average
    }

    /**
     * Haversine distance calculation
     */
    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
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

    /**
     * Auto-assign using all systems
     */
    public function autoAssign(WorkOrder $workOrder, bool $applyRules = true): array
    {
        // First try automated rules
        if ($applyRules) {
            $rulesResult = $this->schedulingRules->applyRules($workOrder);
            if ($rulesResult['success'] && $rulesResult['score'] >= 70) {
                return [
                    'source' => 'automated_rules',
                    'technician' => $rulesResult['technician'],
                    'rule_applied' => $rulesResult['rule_applied'],
                    'audit' => $rulesResult['audit'],
                ];
            }
        }

        // Fall back to recommendation engine
        $recommendations = $this->getRecommendations($workOrder, 1);

        if ($recommendations->isEmpty()) {
            return [
                'source' => null,
                'technician' => null,
                'reason' => 'No suitable technicians found',
            ];
        }

        $best = $recommendations->first();

        if ($best['score'] < 40) {
            return [
                'source' => null,
                'technician' => null,
                'reason' => 'Best match score too low (' . $best['score'] . '%)',
                'best_candidate' => $best,
            ];
        }

        return [
            'source' => 'recommendation_engine',
            'technician' => $best['technician'],
            'score' => $best['score'],
            'factors' => $best['factors'],
        ];
    }
}
