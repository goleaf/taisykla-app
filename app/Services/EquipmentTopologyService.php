<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentRelationship;
use Illuminate\Support\Collection;

class EquipmentTopologyService
{
    /**
     * Get network topology data for visualization
     */
    public function getNetworkTopology(?int $organizationId = null): array
    {
        $query = Equipment::query()
            ->whereNotNull('ip_address')
            ->with(['manufacturer', 'category', 'parent', 'children']);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $equipment = $query->get();

        // Build nodes
        $nodes = $equipment->map(function ($item) {
            return [
                'id' => $item->id,
                'label' => $item->name,
                'type' => $item->type,
                'category' => $item->category?->name,
                'ip_address' => $item->ip_address,
                'mac_address' => $item->mac_address,
                'status' => $item->status,
                'health_score' => $item->health_score,
                'location' => $item->location_full,
                'color' => $this->getNodeColor($item),
                'size' => $this->getNodeSize($item),
            ];
        })->values()->toArray();

        // Build edges from relationships
        $relationships = EquipmentRelationship::query()
            ->whereIn('parent_equipment_id', $equipment->pluck('id'))
            ->orWhereIn('child_equipment_id', $equipment->pluck('id'))
            ->get();

        $edges = $relationships->map(function ($rel) {
            return [
                'source' => $rel->parent_equipment_id,
                'target' => $rel->child_equipment_id,
                'type' => $rel->relationship_type,
                'label' => $rel->notes,
            ];
        })->values()->toArray();

        // Also add parent-child edges from equipment hierarchy
        foreach ($equipment as $item) {
            if ($item->parent_equipment_id) {
                $edges[] = [
                    'source' => $item->parent_equipment_id,
                    'target' => $item->id,
                    'type' => 'hierarchy',
                    'label' => 'Parent',
                ];
            }
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'stats' => [
                'total_nodes' => count($nodes),
                'total_edges' => count($edges),
                'by_status' => $equipment->groupBy('status')->map->count(),
                'by_type' => $equipment->groupBy('type')->map->count(),
            ],
        ];
    }

    /**
     * Get equipment by subnet/network segment
     */
    public function getBySubnet(string $subnet): Collection
    {
        // Parse subnet (e.g., "192.168.1.0/24")
        [$network, $mask] = explode('/', $subnet);
        $networkParts = explode('.', $network);

        // Simple prefix matching for /24 and /16 subnets
        $prefix = match ((int) $mask) {
            24 => implode('.', array_slice($networkParts, 0, 3)) . '.',
            16 => implode('.', array_slice($networkParts, 0, 2)) . '.',
            8 => $networkParts[0] . '.',
            default => $network,
        };

        return Equipment::query()
            ->where('ip_address', 'like', $prefix . '%')
            ->with(['manufacturer', 'category'])
            ->get();
    }

    /**
     * Get all subnets in use
     */
    public function getAvailableSubnets(): array
    {
        $equipment = Equipment::query()
            ->whereNotNull('ip_address')
            ->whereNot('ip_address', '')
            ->select('ip_address')
            ->get();

        $subnets = [];

        foreach ($equipment as $item) {
            $parts = explode('.', $item->ip_address);
            if (count($parts) === 4) {
                $subnet24 = implode('.', array_slice($parts, 0, 3)) . '.0/24';
                if (!isset($subnets[$subnet24])) {
                    $subnets[$subnet24] = 0;
                }
                $subnets[$subnet24]++;
            }
        }

        arsort($subnets);

        return $subnets;
    }

    /**
     * Perform impact analysis - what depends on this equipment
     */
    public function getImpactAnalysis(Equipment $equipment): array
    {
        $directlyAffected = $this->getDirectDependents($equipment);
        $indirectlyAffected = $this->getIndirectDependents($equipment, $directlyAffected->pluck('id')->toArray());

        return [
            'equipment' => [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'type' => $equipment->type,
                'status' => $equipment->status,
            ],
            'directly_affected' => $directlyAffected->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'relationship' => $item->pivot?->relationship_type ?? 'child',
                ];
            })->values(),
            'indirectly_affected' => $indirectlyAffected->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                ];
            })->values(),
            'impact_severity' => $this->calculateImpactSeverity($equipment, $directlyAffected, $indirectlyAffected),
            'total_affected' => $directlyAffected->count() + $indirectlyAffected->count(),
            'recommendations' => $this->getImpactRecommendations($equipment, $directlyAffected, $indirectlyAffected),
        ];
    }

    /**
     * Get all equipment that directly depends on the given equipment
     */
    public function getDirectDependents(Equipment $equipment): Collection
    {
        // Get children from hierarchy
        $children = $equipment->children;

        // Get dependents from relationships
        $dependents = Equipment::query()
            ->whereHas('childRelationships', function ($q) use ($equipment) {
                $q->where('parent_equipment_id', $equipment->id);
            })
            ->get();

        return $children->merge($dependents)->unique('id');
    }

    /**
     * Get indirect dependents (dependents of dependents)
     */
    public function getIndirectDependents(Equipment $equipment, array $excludeIds = [], int $maxDepth = 3): Collection
    {
        $allDependents = collect();
        $currentLevel = $this->getDirectDependents($equipment);
        $visited = array_merge($excludeIds, [$equipment->id]);
        $depth = 0;

        while ($currentLevel->isNotEmpty() && $depth < $maxDepth) {
            $nextLevel = collect();

            foreach ($currentLevel as $item) {
                if (in_array($item->id, $visited)) {
                    continue;
                }

                $visited[] = $item->id;
                $dependents = $this->getDirectDependents($item);

                foreach ($dependents as $dep) {
                    if (!in_array($dep->id, $visited) && !in_array($dep->id, $excludeIds)) {
                        $nextLevel->push($dep);
                        $allDependents->push($dep);
                    }
                }
            }

            $currentLevel = $nextLevel;
            $depth++;
        }

        return $allDependents->unique('id');
    }

    /**
     * Get dependency chain (what this equipment depends on)
     */
    public function getDependencyChain(Equipment $equipment, int $maxDepth = 5): array
    {
        $chain = [];
        $current = $equipment;
        $depth = 0;

        while ($current && $depth < $maxDepth) {
            $parent = $current->parent;

            if (!$parent) {
                // Check relationship for dependency
                $dependency = EquipmentRelationship::query()
                    ->where('child_equipment_id', $current->id)
                    ->where('relationship_type', 'depends_on')
                    ->first();

                if ($dependency) {
                    $parent = Equipment::find($dependency->parent_equipment_id);
                }
            }

            if ($parent) {
                $chain[] = [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'type' => $parent->type,
                    'status' => $parent->status,
                    'health_score' => $parent->health_score,
                ];
                $current = $parent;
            } else {
                break;
            }

            $depth++;
        }

        return $chain;
    }

    // ─── Private Helpers ──────────────────────────────────────────────

    private function getNodeColor(Equipment $equipment): string
    {
        if ($equipment->status === 'retired' || $equipment->status === 'decommissioned') {
            return '#9ca3af'; // gray
        }

        $score = $equipment->health_score ?? 50;

        return match (true) {
            $score >= 80 => '#22c55e', // green
            $score >= 60 => '#3b82f6', // blue
            $score >= 40 => '#f59e0b', // amber
            default => '#ef4444', // red
        };
    }

    private function getNodeSize(Equipment $equipment): int
    {
        // Larger nodes for equipment with more dependents
        $dependentCount = $equipment->children()->count();

        return match (true) {
            $dependentCount >= 10 => 40,
            $dependentCount >= 5 => 30,
            $dependentCount >= 1 => 20,
            default => 15,
        };
    }

    private function calculateImpactSeverity(
        Equipment $equipment,
        Collection $direct,
        Collection $indirect
    ): string {
        $total = $direct->count() + $indirect->count();

        return match (true) {
            $total >= 20 => 'critical',
            $total >= 10 => 'high',
            $total >= 5 => 'medium',
            $total >= 1 => 'low',
            default => 'minimal',
        };
    }

    private function getImpactRecommendations(
        Equipment $equipment,
        Collection $direct,
        Collection $indirect
    ): array {
        $recommendations = [];
        $total = $direct->count() + $indirect->count();

        if ($total >= 10) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'This is a critical infrastructure component. Plan maintenance during off-hours.',
            ];
        }

        if ($equipment->health_score && $equipment->health_score < 50) {
            $recommendations[] = [
                'type' => 'critical',
                'message' => 'Low health score with high dependency count. Consider replacement planning.',
            ];
        }

        if (!$equipment->has_active_warranty && $total >= 5) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'No active warranty. Consider purchasing extended coverage for critical equipment.',
            ];
        }

        if ($direct->count() > 0 && !$equipment->children()->where('status', 'operational')->exists()) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Consider implementing redundancy for dependent equipment.',
            ];
        }

        return $recommendations;
    }
}
