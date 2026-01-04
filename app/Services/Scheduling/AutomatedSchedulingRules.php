<?php

namespace App\Services\Scheduling;

use App\Models\Appointment;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutomatedSchedulingRules
{
    protected array $rules = [];
    protected array $auditLog = [];

    public function __construct()
    {
        $this->loadRules();
    }

    /**
     * Load scheduling rules from configuration
     */
    protected function loadRules(): void
    {
        // Built-in rules with default priorities
        $this->rules = [
            [
                'id' => 'customer_preference',
                'name' => 'Customer Technician Preference',
                'description' => 'Assign customer-preferred technician if available',
                'priority' => 100,
                'enabled' => true,
                'type' => 'preference',
            ],
            [
                'id' => 'equipment_certification',
                'name' => 'Equipment Certification Required',
                'description' => 'Route specific equipment types to certified technicians',
                'priority' => 90,
                'enabled' => true,
                'type' => 'requirement',
            ],
            [
                'id' => 'territory_assignment',
                'name' => 'Geographic Territory',
                'description' => 'Assign to technician covering the service territory',
                'priority' => 80,
                'enabled' => true,
                'type' => 'territory',
            ],
            [
                'id' => 'skill_routing',
                'name' => 'Skill-Based Routing',
                'description' => 'Match technician skills to job requirements',
                'priority' => 70,
                'enabled' => true,
                'type' => 'skill',
            ],
            [
                'id' => 'round_robin',
                'name' => 'Round Robin Distribution',
                'description' => 'Distribute jobs evenly among available technicians',
                'priority' => 50,
                'enabled' => true,
                'type' => 'distribution',
            ],
            [
                'id' => 'workload_balance',
                'name' => 'Workload Balancing',
                'description' => 'Prefer technicians with lighter workloads',
                'priority' => 60,
                'enabled' => true,
                'type' => 'balance',
            ],
        ];
    }

    /**
     * Apply automated scheduling rules to find best technician
     */
    public function applyRules(WorkOrder $workOrder): array
    {
        $this->auditLog = [];
        $candidates = $this->getAvailableTechnicians($workOrder);

        if ($candidates->isEmpty()) {
            $this->logAudit('no_candidates', 'No available technicians found');
            return [
                'success' => false,
                'technician' => null,
                'rule_applied' => null,
                'audit' => $this->auditLog,
            ];
        }

        // Apply rules in priority order
        $sortedRules = collect($this->rules)
            ->where('enabled', true)
            ->sortByDesc('priority');

        foreach ($sortedRules as $rule) {
            $result = $this->evaluateRule($rule, $workOrder, $candidates);

            if ($result['matched']) {
                $this->logAudit($rule['id'], "Rule matched: {$rule['name']}", [
                    'technician_id' => $result['technician']?->id,
                    'technician_name' => $result['technician']?->name,
                ]);

                return [
                    'success' => true,
                    'technician' => $result['technician'],
                    'rule_applied' => $rule,
                    'score' => $result['score'] ?? 100,
                    'audit' => $this->auditLog,
                ];
            }

            $this->logAudit($rule['id'], "Rule did not match: {$rule['name']}");
        }

        // Fallback to first available
        $fallback = $candidates->first();
        $this->logAudit('fallback', 'No rules matched, using first available', [
            'technician_id' => $fallback->id,
        ]);

        return [
            'success' => true,
            'technician' => $fallback,
            'rule_applied' => ['id' => 'fallback', 'name' => 'First Available'],
            'audit' => $this->auditLog,
        ];
    }

    /**
     * Evaluate a specific rule
     */
    protected function evaluateRule(array $rule, WorkOrder $workOrder, Collection $candidates): array
    {
        return match ($rule['type']) {
            'preference' => $this->evaluateCustomerPreference($workOrder, $candidates),
            'requirement' => $this->evaluateCertificationRequirement($workOrder, $candidates),
            'territory' => $this->evaluateTerritoryAssignment($workOrder, $candidates),
            'skill' => $this->evaluateSkillRouting($workOrder, $candidates),
            'distribution' => $this->evaluateRoundRobin($candidates),
            'balance' => $this->evaluateWorkloadBalance($workOrder, $candidates),
            default => ['matched' => false, 'technician' => null],
        };
    }

    /**
     * Customer preference rule
     */
    protected function evaluateCustomerPreference(WorkOrder $workOrder, Collection $candidates): array
    {
        // Check if work order has preferred technician(s)
        $preferredIds = $workOrder->preferred_technician_ids ?? [];
        if ($workOrder->preferred_technician_id) {
            $preferredIds[] = $workOrder->preferred_technician_id;
        }

        if (empty($preferredIds)) {
            // Check organization's preferred technician history
            if ($workOrder->organization_id) {
                $lastTechnician = WorkOrder::where('organization_id', $workOrder->organization_id)
                    ->whereIn('status', ['completed', 'closed'])
                    ->whereNotNull('assigned_to_user_id')
                    ->orderBy('completed_at', 'desc')
                    ->value('assigned_to_user_id');

                if ($lastTechnician) {
                    $preferredIds[] = $lastTechnician;
                }
            }
        }

        if (empty($preferredIds)) {
            return ['matched' => false, 'technician' => null];
        }

        $preferred = $candidates->whereIn('id', $preferredIds)->first();

        return [
            'matched' => $preferred !== null,
            'technician' => $preferred,
            'score' => 100,
        ];
    }

    /**
     * Certification requirement rule
     */
    protected function evaluateCertificationRequirement(WorkOrder $workOrder, Collection $candidates): array
    {
        $requiredCerts = $workOrder->required_certifications ?? [];

        // Also check equipment requirements
        if ($workOrder->equipment) {
            $equipmentType = strtolower($workOrder->equipment->category?->name ?? '');

            $certMappings = [
                'hvac' => ['HVAC Certified', 'EPA 608'],
                'electrical' => ['Licensed Electrician', 'Electrical Safety'],
                'refrigeration' => ['EPA 608', 'Refrigerant Handling'],
                'gas' => ['Gas Fitter License'],
            ];

            foreach ($certMappings as $type => $certs) {
                if (str_contains($equipmentType, $type)) {
                    $requiredCerts = array_merge($requiredCerts, $certs);
                }
            }
        }

        if (empty($requiredCerts)) {
            return ['matched' => false, 'technician' => null];
        }

        $requiredCerts = array_unique(array_map('strtolower', $requiredCerts));

        // Find technicians with required certifications
        $qualified = $candidates->filter(function (User $tech) use ($requiredCerts) {
            $techCerts = $tech->certifications ?? [];
            if (!is_array($techCerts)) {
                $techCerts = json_decode($techCerts, true) ?? [];
            }
            $techCerts = array_map('strtolower', $techCerts);

            return count(array_intersect($techCerts, $requiredCerts)) === count($requiredCerts);
        })->first();

        return [
            'matched' => $qualified !== null,
            'technician' => $qualified,
            'score' => 95,
        ];
    }

    /**
     * Territory assignment rule
     */
    protected function evaluateTerritoryAssignment(WorkOrder $workOrder, Collection $candidates): array
    {
        $serviceTerritory = $workOrder->service_territory ?? $this->inferTerritory($workOrder);

        if (!$serviceTerritory) {
            return ['matched' => false, 'technician' => null];
        }

        $serviceTerritory = strtolower($serviceTerritory);

        $territoryMatch = $candidates->filter(function (User $tech) use ($serviceTerritory) {
            $techTerritory = strtolower($tech->territory ?? '');
            return $techTerritory && str_contains($techTerritory, $serviceTerritory);
        })->first();

        return [
            'matched' => $territoryMatch !== null,
            'technician' => $territoryMatch,
            'score' => 85,
        ];
    }

    /**
     * Infer territory from address
     */
    protected function inferTerritory(WorkOrder $workOrder): ?string
    {
        $address = $workOrder->location_address;
        if (!$address) {
            return null;
        }

        // Extract city/zone from address (simplified)
        preg_match('/,\s*([^,]+),\s*[A-Z]{2}\s*\d{5}/i', $address, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Skill routing rule
     */
    protected function evaluateSkillRouting(WorkOrder $workOrder, Collection $candidates): array
    {
        $requiredSkills = $workOrder->required_skills ?? [];
        $requiredLevel = $workOrder->required_skill_level ?? 1;

        if (empty($requiredSkills) && $requiredLevel <= 1) {
            return ['matched' => false, 'technician' => null];
        }

        $requiredSkills = array_map('strtolower', $requiredSkills);

        $qualified = $candidates->filter(function (User $tech) use ($requiredSkills, $requiredLevel) {
            // Check skill level
            if (($tech->skill_level ?? 1) < $requiredLevel) {
                return false;
            }

            // Check specific skills if required
            if (!empty($requiredSkills)) {
                $techSkills = $tech->skills ?? [];
                if (!is_array($techSkills)) {
                    $techSkills = json_decode($techSkills, true) ?? [];
                }
                $techSkills = array_map('strtolower', $techSkills);

                $matchedSkills = array_intersect($techSkills, $requiredSkills);
                return count($matchedSkills) >= (count($requiredSkills) / 2); // At least 50% match
            }

            return true;
        })->sortByDesc('skill_level')->first();

        return [
            'matched' => $qualified !== null,
            'technician' => $qualified,
            'score' => 80,
        ];
    }

    /**
     * Round robin distribution rule
     */
    protected function evaluateRoundRobin(Collection $candidates): array
    {
        // Find technician with fewest assignments today
        $techWithFewest = $candidates->sortBy(function (User $tech) {
            return WorkOrder::where('assigned_to_user_id', $tech->id)
                ->whereDate('scheduled_start_at', today())
                ->count();
        })->first();

        return [
            'matched' => $techWithFewest !== null,
            'technician' => $techWithFewest,
            'score' => 70,
        ];
    }

    /**
     * Workload balance rule
     */
    protected function evaluateWorkloadBalance(WorkOrder $workOrder, Collection $candidates): array
    {
        $scored = $candidates->map(function (User $tech) use ($workOrder) {
            $scheduledMinutes = Appointment::where('assigned_to_user_id', $tech->id)
                ->whereDate('scheduled_start_at', $workOrder->scheduled_start_at ?? today())
                ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, scheduled_start_at, scheduled_end_at)'));

            $maxMinutes = $tech->max_daily_minutes ?? 480;
            $remainingCapacity = $maxMinutes - $scheduledMinutes;

            return [
                'technician' => $tech,
                'remaining_capacity' => $remainingCapacity,
                'utilization' => round(($scheduledMinutes / $maxMinutes) * 100),
            ];
        })->filter(fn($t) => $t['remaining_capacity'] >= ($workOrder->estimated_minutes ?? 60))
            ->sortByDesc('remaining_capacity');

        $best = $scored->first();

        return [
            'matched' => $best !== null,
            'technician' => $best['technician'] ?? null,
            'score' => 75,
        ];
    }

    /**
     * Get available technicians for a work order
     */
    protected function getAvailableTechnicians(WorkOrder $workOrder): Collection
    {
        return User::role('technician')
            ->where('is_active', true)
            ->whereIn('availability_status', ['available', 'busy'])
            ->get();
    }

    /**
     * Log audit entry
     */
    protected function logAudit(string $ruleId, string $message, array $context = []): void
    {
        $entry = [
            'rule_id' => $ruleId,
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ];

        $this->auditLog[] = $entry;

        // Also log to system for persistence
        Log::info("[SchedulingRule] {$ruleId}: {$message}", $context);
    }

    /**
     * Get all rules
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Update rule priority
     */
    public function updateRulePriority(string $ruleId, int $priority): bool
    {
        foreach ($this->rules as &$rule) {
            if ($rule['id'] === $ruleId) {
                $rule['priority'] = $priority;
                return true;
            }
        }
        return false;
    }

    /**
     * Toggle rule enabled state
     */
    public function toggleRule(string $ruleId, bool $enabled): bool
    {
        foreach ($this->rules as &$rule) {
            if ($rule['id'] === $ruleId) {
                $rule['enabled'] = $enabled;
                return true;
            }
        }
        return false;
    }

    /**
     * Manual override - assign with override flag
     */
    public function manualOverride(WorkOrder $workOrder, User $technician, string $reason): array
    {
        $this->logAudit('manual_override', "Manual assignment override: {$reason}", [
            'work_order_id' => $workOrder->id,
            'technician_id' => $technician->id,
            'overridden_by' => auth()->id(),
        ]);

        return [
            'success' => true,
            'technician' => $technician,
            'rule_applied' => [
                'id' => 'manual_override',
                'name' => 'Manual Override',
                'reason' => $reason,
            ],
            'audit' => $this->auditLog,
        ];
    }
}
