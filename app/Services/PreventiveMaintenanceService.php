<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PreventiveMaintenanceService
{
    /**
     * Get all schedules that are due or overdue
     */
    public function getDueSchedules(?int $limit = null): Collection
    {
        return PreventiveMaintenanceSchedule::query()
            ->active()
            ->where('next_due_at', '<=', now())
            ->with(['equipment', 'assignedUser'])
            ->when($limit, fn($q) => $q->limit($limit))
            ->orderBy('next_due_at')
            ->get();
    }

    /**
     * Get schedules that are coming up soon
     */
    public function getUpcomingSchedules(int $days = 7): Collection
    {
        return PreventiveMaintenanceSchedule::query()
            ->active()
            ->whereBetween('next_due_at', [now(), now()->addDays($days)])
            ->with(['equipment', 'assignedUser'])
            ->orderBy('next_due_at')
            ->get();
    }

    /**
     * Create a work order from a maintenance schedule
     */
    public function createWorkOrderFromSchedule(PreventiveMaintenanceSchedule $schedule): ?WorkOrder
    {
        if (!$schedule->auto_create_work_order) {
            return null;
        }

        $equipment = $schedule->equipment;

        // Find or create a maintenance category
        $category = WorkOrderCategory::firstOrCreate(
            ['name' => 'Preventive Maintenance'],
            ['description' => 'Scheduled preventive maintenance', 'is_active' => true]
        );

        $workOrder = WorkOrder::create([
            'organization_id' => $equipment->organization_id,
            'equipment_id' => $equipment->id,
            'assigned_to_user_id' => $schedule->assigned_user_id,
            'category_id' => $category->id,
            'priority' => 'standard',
            'status' => 'submitted',
            'subject' => sprintf('Preventive Maintenance: %s', $schedule->name),
            'description' => $this->buildWorkOrderDescription($schedule),
            'location_name' => $equipment->location_name,
            'location_address' => $equipment->location_address,
            'requested_at' => now(),
            'scheduled_start_at' => $schedule->next_due_at,
            'custom_fields' => [
                'preventive_maintenance_schedule_id' => $schedule->id,
                'checklist' => $schedule->checklist_template,
            ],
        ]);

        return $workOrder;
    }

    /**
     * Mark a schedule as completed and update dates
     */
    public function markCompleted(PreventiveMaintenanceSchedule $schedule, ?Carbon $completedAt = null): void
    {
        $completedAt = $completedAt ?? now();

        $schedule->last_performed_at = $completedAt;
        $schedule->next_due_at = $schedule->calculateNextDueDate();
        $schedule->save();

        // Update equipment's last maintenance timestamp
        $schedule->equipment->update([
            'last_maintenance_at' => $completedAt,
            'next_maintenance_due_at' => $schedule->next_due_at,
        ]);
    }

    /**
     * Process all due schedules and create work orders
     */
    public function processDueSchedules(): array
    {
        $dueSchedules = $this->getDueSchedules();
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($dueSchedules as $schedule) {
            try {
                // Check if work order already exists for this schedule
                $existingWorkOrder = WorkOrder::query()
                    ->where('equipment_id', $schedule->equipment_id)
                    ->whereJsonContains('custom_fields->preventive_maintenance_schedule_id', $schedule->id)
                    ->whereIn('status', ['submitted', 'scheduled', 'in_progress', 'on_hold'])
                    ->exists();

                if ($existingWorkOrder) {
                    $skipped++;
                    continue;
                }

                $workOrder = $this->createWorkOrderFromSchedule($schedule);

                if ($workOrder) {
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'schedule_id' => $schedule->id,
                    'equipment_id' => $schedule->equipment_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'processed' => $dueSchedules->count(),
            'work_orders_created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Get maintenance summary for equipment
     */
    public function getMaintenanceSummary(Equipment $equipment): array
    {
        $schedules = $equipment->maintenanceSchedules()->active()->get();
        $overdue = $schedules->filter(fn($s) => $s->isOverdue())->count();
        $dueSoon = $schedules->filter(fn($s) => $s->isDueSoon())->count();

        return [
            'total_schedules' => $schedules->count(),
            'overdue' => $overdue,
            'due_soon' => $dueSoon,
            'on_track' => $schedules->count() - $overdue - $dueSoon,
            'next_due' => $schedules->sortBy('next_due_at')->first()?->next_due_at,
            'last_performed' => $equipment->last_maintenance_at,
        ];
    }

    /**
     * Create a default maintenance schedule for equipment based on category
     */
    public function createDefaultSchedule(Equipment $equipment): ?PreventiveMaintenanceSchedule
    {
        // Get default schedule configuration based on equipment type
        $config = $this->getDefaultScheduleConfig($equipment->type);

        if (!$config) {
            return null;
        }

        return PreventiveMaintenanceSchedule::create([
            'equipment_id' => $equipment->id,
            'equipment_category_id' => $equipment->equipment_category_id,
            'name' => $config['name'],
            'description' => $config['description'],
            'frequency_type' => $config['frequency_type'],
            'frequency_value' => $config['frequency_value'],
            'next_due_at' => now()->add($config['frequency_type'], $config['frequency_value']),
            'checklist_template' => $config['checklist'],
            'reminder_days_before' => 7,
            'auto_create_work_order' => true,
            'is_active' => true,
        ]);
    }

    // ─── Private Methods ──────────────────────────────────────────────

    private function buildWorkOrderDescription(PreventiveMaintenanceSchedule $schedule): string
    {
        $description = sprintf(
            "Scheduled preventive maintenance for %s.\n\n",
            $schedule->equipment->name
        );

        if ($schedule->description) {
            $description .= $schedule->description . "\n\n";
        }

        if ($schedule->checklist_template) {
            $description .= "## Checklist\n";
            foreach ($schedule->checklist_template as $item) {
                $description .= sprintf("- [ ] %s\n", $item);
            }
        }

        return $description;
    }

    private function getDefaultScheduleConfig(string $equipmentType): ?array
    {
        $defaults = [
            'server' => [
                'name' => 'Quarterly Server Maintenance',
                'description' => 'Regular server maintenance including updates and checks',
                'frequency_type' => 'months',
                'frequency_value' => 3,
                'checklist' => [
                    'Check system logs for errors',
                    'Verify backup procedures',
                    'Update operating system',
                    'Clean dust filters',
                    'Check storage capacity',
                    'Verify network connectivity',
                ],
            ],
            'hvac' => [
                'name' => 'Monthly HVAC Inspection',
                'description' => 'Regular HVAC system inspection and filter replacement',
                'frequency_type' => 'months',
                'frequency_value' => 1,
                'checklist' => [
                    'Replace air filters',
                    'Check refrigerant levels',
                    'Inspect electrical connections',
                    'Clean condenser coils',
                    'Test thermostat operation',
                ],
            ],
            'printer' => [
                'name' => 'Monthly Printer Maintenance',
                'description' => 'Regular printer cleaning and consumable check',
                'frequency_type' => 'months',
                'frequency_value' => 1,
                'checklist' => [
                    'Clean print heads',
                    'Check paper feed mechanism',
                    'Verify toner/ink levels',
                    'Test print quality',
                    'Clean scanner glass',
                ],
            ],
        ];

        $type = strtolower($equipmentType);

        return $defaults[$type] ?? null;
    }
}
