<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\ServiceAgreement;
use App\Models\PriorityLevel;
use Carbon\Carbon;

class SLAService
{
    /**
     * Calculate and set SLA target times for a work order.
     */
    public function calculateTargets(WorkOrder $workOrder): void
    {
        // 1. Try to find a Service Agreement via Organization
        $agreement = $workOrder->organization?->serviceAgreement;

        // 2. Fallback to global Priority Level defaults
        $priorityLevel = PriorityLevel::where('name', $workOrder->priority)->first();

        $responseTimeMinutes = null;
        $resolutionTimeMinutes = null;

        // Logic: Service Agreement overrides Priority Level if set
        if ($agreement) {
            $responseTimeMinutes = $agreement->response_time_minutes;
            $resolutionTimeMinutes = $agreement->resolution_time_minutes;
        } elseif ($priorityLevel) {
            $responseTimeMinutes = $priorityLevel->response_time_minutes;
            $resolutionTimeMinutes = $priorityLevel->resolution_time_minutes;
        }

        // Set targets relative to created_at (or requested_at if essential)
        $baseTime = $workOrder->created_at ?? now();

        if ($responseTimeMinutes) {
            $workOrder->target_response_at = $baseTime->copy()->addMinutes($responseTimeMinutes);
        }

        if ($resolutionTimeMinutes) {
            $workOrder->target_resolution_at = $baseTime->copy()->addMinutes($resolutionTimeMinutes);
        }

        $workOrder->save();
    }

    /**
     * Check for breached SLAs and update status.
     * This should be called by a scheduled job.
     */
    public function checkBreaches(): void
    {
        $now = now();

        // 1. Check Response Time Breaches
        // Condition: Status is 'submitted' or 'assigned' (not started), and target_response_at < now
        WorkOrder::whereIn('status', ['submitted', 'assigned'])
            ->whereNotNull('target_response_at')
            ->where('target_response_at', '<', $now)
            ->where('sla_status', '!=', 'breached')
            ->chunk(100, function ($workOrders) use ($now) {
                foreach ($workOrders as $wo) {
                    $this->markAsBreached($wo, $now, 'response_time');
                }
            });

        // 2. Check Resolution Time Breaches
        // Condition: Status is NOT closed/completed/canceled, and target_resolution_at < now
        WorkOrder::whereNotIn('status', ['completed', 'closed', 'canceled'])
            ->whereNotNull('target_resolution_at')
            ->where('target_resolution_at', '<', $now)
            ->where('sla_status', '!=', 'breached')
            ->chunk(100, function ($workOrders) use ($now) {
                foreach ($workOrders as $wo) {
                    $this->markAsBreached($wo, $now, 'resolution_time');
                }
            });
    }

    private function markAsBreached(WorkOrder $workOrder, Carbon $timestamp, string $reason): void
    {
        $workOrder->update([
            'sla_status' => 'breached',
            'sla_breached_at' => $timestamp,
        ]);

        // Fire automation event
        // event(new \App\Events\WorkOrderSLABreached($workOrder, $reason));
    }
}
