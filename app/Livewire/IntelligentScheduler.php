<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\RouteOptimizationService;
use App\Services\Scheduling\AutomatedSchedulingRules;
use App\Services\Scheduling\CapacityPlanningService;
use App\Services\Scheduling\EnhancedAssignmentEngine;
use App\Services\Scheduling\RecurringScheduleService;
use App\Services\Scheduling\ScheduleAdjustmentService;
use App\Services\Scheduling\ScheduleConflictDetector;
use App\Support\PermissionCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class IntelligentScheduler extends Component
{
    // View state
    #[Url]
    public string $activeTab = 'assignments';

    #[Url]
    public ?string $selectedDate = null;

    #[Url]
    public ?int $selectedTechnicianId = null;

    // Assignment recommendation
    public ?int $selectedWorkOrderId = null;
    public array $recommendations = [];
    public bool $showRecommendations = false;

    // Route optimization
    public ?int $optimizeTechnicianId = null;
    public array $currentRoute = [];
    public array $optimizedRoute = [];
    public array $routeSummary = [];
    public bool $showOptimizationPreview = false;

    // Conflict detection
    public array $detectedConflicts = [];

    // Capacity planning
    public array $capacityMetrics = [];
    public array $forecast = [];
    public array $hiringRecommendations = [];

    // Scheduling rules
    public array $schedulingRules = [];
    public bool $showRulesEditor = false;

    // Schedule adjustments
    public ?int $draggedAppointmentId = null;
    public array $pendingChanges = [];
    public array $impactAnalysis = [];

    // Recurring schedules
    public bool $showRecurringModal = false;
    public array $recurringFormData = [
        'frequency' => 'weekly',
        'interval' => 1,
        'days_of_week' => [],
        'starts_at' => '',
        'duration_minutes' => 60,
        'ends_at' => null,
        'occurrence_count' => null,
    ];
    public array $recurringPreview = [];

    // Bulk operations
    public array $selectedAppointments = [];
    public bool $showBulkModal = false;
    public string $bulkAction = 'reschedule';

    // Emergency insertion
    public bool $showEmergencyModal = false;
    public ?int $emergencyWorkOrderId = null;
    public ?int $emergencyTechnicianId = null;
    public ?string $emergencyTime = null;
    public array $emergencyBumpOptions = [];

    protected $listeners = [
        'appointment-dropped' => 'handleAppointmentDrop',
        'date-changed' => 'handleDateChange',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::SCHEDULE_MANAGE), 403);

        $this->selectedDate = $this->selectedDate ?? today()->format('Y-m-d');
        $this->loadCapacityMetrics();
        $this->loadSchedulingRules();
    }

    // ===========================================
    // Assignment Recommendations
    // ===========================================

    public function selectWorkOrderForAssignment(int $workOrderId): void
    {
        $this->selectedWorkOrderId = $workOrderId;
        $this->loadRecommendations();
        $this->showRecommendations = true;
    }

    public function loadRecommendations(): void
    {
        if (!$this->selectedWorkOrderId) {
            return;
        }

        $workOrder = WorkOrder::findOrFail($this->selectedWorkOrderId);
        $engine = app(EnhancedAssignmentEngine::class);

        $this->recommendations = $engine->getRecommendations($workOrder, 5)->toArray();
    }

    public function assignTechnician(int $technicianId): void
    {
        $workOrder = WorkOrder::findOrFail($this->selectedWorkOrderId);
        $technician = User::findOrFail($technicianId);

        // Validate assignment
        $conflictDetector = app(ScheduleConflictDetector::class);
        $validation = $conflictDetector->validateAssignment(
            $workOrder,
            $technician,
            $workOrder->scheduled_start_at ?? now(),
            $workOrder->estimated_minutes ?? 60
        );

        if (!$validation['can_proceed']) {
            $this->detectedConflicts = $validation['conflicts']->toArray();
            $this->dispatch('show-conflicts');
            return;
        }

        // Create appointment
        $endTime = ($workOrder->scheduled_start_at ?? now())
            ->copy()
            ->addMinutes($workOrder->estimated_minutes ?? 60);

        Appointment::create([
            'work_order_id' => $workOrder->id,
            'assigned_to_user_id' => $technicianId,
            'scheduled_start_at' => $workOrder->scheduled_start_at ?? now(),
            'scheduled_end_at' => $endTime,
            'status' => 'scheduled',
        ]);

        $workOrder->update([
            'assigned_to_user_id' => $technicianId,
            'status' => 'assigned',
        ]);

        $this->showRecommendations = false;
        $this->selectedWorkOrderId = null;
        $this->recommendations = [];

        $this->dispatch('assignment-complete');
        $this->dispatch('notify', message: 'Work order assigned successfully');
    }

    public function autoAssign(): void
    {
        if (!$this->selectedWorkOrderId) {
            return;
        }

        $workOrder = WorkOrder::findOrFail($this->selectedWorkOrderId);
        $engine = app(EnhancedAssignmentEngine::class);

        $result = $engine->autoAssign($workOrder);

        if ($result['technician']) {
            $this->assignTechnician($result['technician']->id);
        } else {
            $this->dispatch('notify', message: $result['reason'] ?? 'Could not auto-assign', type: 'error');
        }
    }

    // ===========================================
    // Route Optimization
    // ===========================================

    public function selectTechnicianForOptimization(int $technicianId): void
    {
        $this->optimizeTechnicianId = $technicianId;
        $this->loadCurrentRoute();
        $this->optimizeRoute();
        $this->showOptimizationPreview = true;
    }

    protected function loadCurrentRoute(): void
    {
        $technician = User::findOrFail($this->optimizeTechnicianId);

        $appointments = Appointment::where('assigned_to_user_id', $technician->id)
            ->whereDate('scheduled_start_at', $this->selectedDate)
            ->orderBy('scheduled_start_at')
            ->with('workOrder')
            ->get();

        $this->currentRoute = $appointments->map(fn($a) => [
            'id' => $a->id,
            'work_order_id' => $a->work_order_id,
            'subject' => $a->workOrder?->subject,
            'address' => $a->workOrder?->location_address,
            'start' => $a->scheduled_start_at->format('g:i A'),
            'end' => $a->scheduled_end_at->format('g:i A'),
            'latitude' => $a->workOrder?->location_latitude,
            'longitude' => $a->workOrder?->location_longitude,
        ])->toArray();
    }

    public function optimizeRoute(): void
    {
        $technician = User::findOrFail($this->optimizeTechnicianId);
        $routeService = app(RouteOptimizationService::class);

        $optimizedCollection = $routeService->optimizeRoute($technician, Carbon::parse($this->selectedDate));

        $this->optimizedRoute = $optimizedCollection->map(fn($wo) => [
            'id' => $wo->appointments->first()?->id,
            'work_order_id' => $wo->id,
            'subject' => $wo->subject,
            'address' => $wo->location_address,
            'sequence' => $wo->route_sequence,
            'travel_minutes' => $wo->leg_travel_minutes,
            'estimated_arrival' => $wo->estimated_arrival?->format('g:i A'),
            'distance_km' => $wo->leg_distance_km,
        ])->toArray();

        $this->routeSummary = $routeService->getRouteSummary($optimizedCollection);

        // Calculate time savings
        $currentTravelTime = count($this->currentRoute) * 15; // Rough estimate
        $optimizedTravelTime = $this->routeSummary['total_travel_minutes'] ?? 0;
        $this->routeSummary['time_saved'] = max(0, $currentTravelTime - $optimizedTravelTime);
    }

    public function acceptOptimization(): void
    {
        $technician = User::findOrFail($this->optimizeTechnicianId);
        $date = Carbon::parse($this->selectedDate);

        // Get optimized order
        $routeService = app(RouteOptimizationService::class);
        $optimizedCollection = $routeService->optimizeRoute($technician, $date);

        $currentTime = $date->copy()->setTime(8, 0);

        foreach ($optimizedCollection as $wo) {
            $appointment = Appointment::where('work_order_id', $wo->id)
                ->where('assigned_to_user_id', $technician->id)
                ->whereDate('scheduled_start_at', $date)
                ->first();

            if ($appointment) {
                $duration = $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at);

                $appointment->update([
                    'scheduled_start_at' => $currentTime->copy(),
                    'scheduled_end_at' => $currentTime->copy()->addMinutes($duration),
                ]);

                $currentTime->addMinutes($duration + 15); // 15 min buffer
            }
        }

        $this->showOptimizationPreview = false;
        $this->optimizeTechnicianId = null;
        $this->dispatch('route-optimized');
        $this->dispatch('notify', message: 'Route optimized and applied');
    }

    // ===========================================
    // Capacity Planning
    // ===========================================

    public function loadCapacityMetrics(): void
    {
        $capacityService = app(CapacityPlanningService::class);

        $metrics = $capacityService->getTechnicianCapacityMetrics(Carbon::parse($this->selectedDate));

        $this->capacityMetrics = $metrics->map(fn($m) => [
            'technician_id' => $m['technician']->id,
            'name' => $m['technician']->name,
            'daily' => $m['daily'],
            'weekly' => $m['weekly'],
            'status' => $m['status'],
            'alerts' => $m['alerts'],
        ])->toArray();

        $this->forecast = $capacityService->forecastCapacityNeeds(4);
        $this->hiringRecommendations = $capacityService->getHiringRecommendations();
    }

    #[Computed]
    public function underutilizedTechnicians(): array
    {
        return collect($this->capacityMetrics)
            ->filter(fn($m) => $m['daily']['utilization'] < 50)
            ->sortBy(fn($m) => $m['daily']['utilization'])
            ->values()
            ->toArray();
    }

    #[Computed]
    public function overbookedTechnicians(): array
    {
        return collect($this->capacityMetrics)
            ->filter(fn($m) => $m['daily']['utilization'] >= 100)
            ->values()
            ->toArray();
    }

    // ===========================================
    // Scheduling Rules
    // ===========================================

    public function loadSchedulingRules(): void
    {
        $rulesService = app(AutomatedSchedulingRules::class);
        $this->schedulingRules = $rulesService->getRules();
    }

    public function toggleRule(string $ruleId, bool $enabled): void
    {
        $rulesService = app(AutomatedSchedulingRules::class);
        $rulesService->toggleRule($ruleId, $enabled);
        $this->loadSchedulingRules();
    }

    public function updateRulePriority(string $ruleId, int $priority): void
    {
        $rulesService = app(AutomatedSchedulingRules::class);
        $rulesService->updateRulePriority($ruleId, $priority);
        $this->loadSchedulingRules();
    }

    // ===========================================
    // Schedule Adjustments
    // ===========================================

    public function handleAppointmentDrop(int $appointmentId, string $newStart, ?int $newTechnicianId = null): void
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $newStartTime = Carbon::parse($newStart);

        // Analyze impact first
        $adjustmentService = app(ScheduleAdjustmentService::class);
        $this->impactAnalysis = $adjustmentService->analyzeImpact($appointment, $newStartTime, $newTechnicianId);

        $this->pendingChanges = [
            'appointment_id' => $appointmentId,
            'new_start' => $newStart,
            'new_technician_id' => $newTechnicianId,
        ];

        $this->dispatch('show-impact-analysis');
    }

    public function confirmReschedule(): void
    {
        if (empty($this->pendingChanges)) {
            return;
        }

        $appointment = Appointment::findOrFail($this->pendingChanges['appointment_id']);
        $adjustmentService = app(ScheduleAdjustmentService::class);

        $result = $adjustmentService->reschedule(
            $appointment,
            Carbon::parse($this->pendingChanges['new_start']),
            null,
            $this->pendingChanges['new_technician_id']
        );

        if ($result['success']) {
            $this->pendingChanges = [];
            $this->impactAnalysis = [];
            $this->dispatch('schedule-updated');
            $this->dispatch('notify', message: 'Appointment rescheduled successfully');
        } else {
            $this->detectedConflicts = $result['conflicts']->toArray();
            $this->dispatch('show-conflicts');
        }
    }

    public function cancelReschedule(): void
    {
        $this->pendingChanges = [];
        $this->impactAnalysis = [];
    }

    public function swapAppointments(int $appointment1Id, int $appointment2Id): void
    {
        $a1 = Appointment::findOrFail($appointment1Id);
        $a2 = Appointment::findOrFail($appointment2Id);

        $adjustmentService = app(ScheduleAdjustmentService::class);
        $result = $adjustmentService->swapAppointments($a1, $a2);

        if ($result['success']) {
            $this->dispatch('schedule-updated');
            $this->dispatch('notify', message: 'Appointments swapped');
        } else {
            $this->dispatch('notify', message: $result['message'], type: 'error');
        }
    }

    public function compressSchedule(int $technicianId): void
    {
        $technician = User::findOrFail($technicianId);
        $adjustmentService = app(ScheduleAdjustmentService::class);

        $result = $adjustmentService->compressSchedule($technician, Carbon::parse($this->selectedDate));

        $this->dispatch('schedule-updated');
        $this->dispatch('notify', message: $result['message']);
    }

    // ===========================================
    // Bulk Operations
    // ===========================================

    public function toggleAppointmentSelection(int $appointmentId): void
    {
        if (in_array($appointmentId, $this->selectedAppointments)) {
            $this->selectedAppointments = array_diff($this->selectedAppointments, [$appointmentId]);
        } else {
            $this->selectedAppointments[] = $appointmentId;
        }
    }

    public function selectAllAppointments(): void
    {
        $this->selectedAppointments = Appointment::whereDate('scheduled_start_at', $this->selectedDate)
            ->pluck('id')
            ->toArray();
    }

    public function clearSelection(): void
    {
        $this->selectedAppointments = [];
    }

    public function openBulkModal(string $action): void
    {
        if (empty($this->selectedAppointments)) {
            $this->dispatch('notify', message: 'No appointments selected', type: 'warning');
            return;
        }

        $this->bulkAction = $action;
        $this->showBulkModal = true;
    }

    public function executeBulkAction(array $params): void
    {
        $adjustmentService = app(ScheduleAdjustmentService::class);

        $rescheduleData = collect($this->selectedAppointments)->map(function ($id) use ($params) {
            return [
                'appointment_id' => $id,
                'new_start' => $params['new_date'] . ' ' . $params['new_time'],
                'new_technician_id' => $params['new_technician_id'] ?? null,
            ];
        })->toArray();

        $result = $adjustmentService->bulkReschedule($rescheduleData);

        $successCount = count($result['success']);
        $failedCount = count($result['failed']);

        $this->selectedAppointments = [];
        $this->showBulkModal = false;

        $this->dispatch('schedule-updated');
        $this->dispatch('notify', message: "{$successCount} rescheduled, {$failedCount} failed");
    }

    // ===========================================
    // Emergency Insertion
    // ===========================================

    public function openEmergencyInsert(int $workOrderId): void
    {
        $this->emergencyWorkOrderId = $workOrderId;
        $this->showEmergencyModal = true;
    }

    public function previewEmergencyInsert(): void
    {
        if (!$this->emergencyWorkOrderId || !$this->emergencyTechnicianId || !$this->emergencyTime) {
            return;
        }

        $workOrder = WorkOrder::findOrFail($this->emergencyWorkOrderId);
        $technician = User::findOrFail($this->emergencyTechnicianId);
        $adjustmentService = app(ScheduleAdjustmentService::class);

        $result = $adjustmentService->emergencyInsert(
            $workOrder,
            $technician,
            Carbon::parse($this->emergencyTime)
        );

        if (!$result['success'] && $result['requires_confirmation']) {
            $this->emergencyBumpOptions = $result['conflicts'];
        } elseif ($result['success']) {
            $this->showEmergencyModal = false;
            $this->dispatch('schedule-updated');
            $this->dispatch('notify', message: 'Emergency appointment inserted');
        }
    }

    public function confirmEmergencyInsert(array $bumpDecisions): void
    {
        $workOrder = WorkOrder::findOrFail($this->emergencyWorkOrderId);
        $technician = User::findOrFail($this->emergencyTechnicianId);
        $adjustmentService = app(ScheduleAdjustmentService::class);

        $result = $adjustmentService->confirmEmergencyInsert(
            $workOrder,
            $technician,
            Carbon::parse($this->emergencyTime),
            $bumpDecisions
        );

        if ($result['success']) {
            $this->showEmergencyModal = false;
            $this->emergencyWorkOrderId = null;
            $this->emergencyTechnicianId = null;
            $this->emergencyTime = null;
            $this->emergencyBumpOptions = [];

            $this->dispatch('schedule-updated');
            $this->dispatch('notify', message: 'Emergency appointment inserted with bumps applied');
        }
    }

    // ===========================================
    // Recurring Schedules
    // ===========================================

    public function openRecurringModal(): void
    {
        $this->showRecurringModal = true;
        $this->recurringFormData = [
            'frequency' => 'weekly',
            'interval' => 1,
            'days_of_week' => [],
            'starts_at' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'ends_at' => null,
            'occurrence_count' => null,
        ];
    }

    public function previewRecurringSchedule(): void
    {
        $recurringService = app(RecurringScheduleService::class);
        $this->recurringPreview = $recurringService->previewOccurrences($this->recurringFormData, 10);
    }

    public function createRecurringSchedule(): void
    {
        if (!$this->selectedWorkOrderId) {
            return;
        }

        $recurringService = app(RecurringScheduleService::class);

        $schedule = $recurringService->createRecurringSchedule([
            'work_order_id' => $this->selectedWorkOrderId,
            'assigned_to_user_id' => $this->recurringFormData['assigned_to_user_id'] ?? null,
            'starts_at' => $this->recurringFormData['starts_at'],
            'duration_minutes' => $this->recurringFormData['duration_minutes'],
            'frequency' => $this->recurringFormData['frequency'],
            'interval' => $this->recurringFormData['interval'],
            'days_of_week' => $this->recurringFormData['days_of_week'],
            'ends_at' => $this->recurringFormData['ends_at'],
            'occurrence_count' => $this->recurringFormData['occurrence_count'],
        ]);

        $this->showRecurringModal = false;
        $this->dispatch('notify', message: 'Recurring schedule created');
    }

    // ===========================================
    // Date Navigation
    // ===========================================

    public function handleDateChange(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadCapacityMetrics();
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
        $this->loadCapacityMetrics();
    }

    public function nextDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->format('Y-m-d');
        $this->loadCapacityMetrics();
    }

    public function goToToday(): void
    {
        $this->selectedDate = today()->format('Y-m-d');
        $this->loadCapacityMetrics();
    }

    // ===========================================
    // Computed Properties
    // ===========================================

    #[Computed]
    public function unassignedWorkOrders(): Collection
    {
        return WorkOrder::whereNull('assigned_to_user_id')
            ->whereIn('status', ['open', 'pending'])
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_start_at')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function technicians(): Collection
    {
        return User::role('technician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function todaysAppointments(): Collection
    {
        return Appointment::whereDate('scheduled_start_at', $this->selectedDate)
            ->with(['workOrder', 'assignedTo'])
            ->orderBy('scheduled_start_at')
            ->get();
    }

    #[Computed]
    public function selectedDateFormatted(): string
    {
        return Carbon::parse($this->selectedDate)->format('l, F j, Y');
    }

    public function render()
    {
        return view('livewire.intelligent-scheduler');
    }
}
