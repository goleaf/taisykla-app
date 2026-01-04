<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Message;
use App\Services\SchedulingRecommendationService;
use App\Services\RouteOptimizationService;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class DispatchDashboard extends Component
{
    // UI state
    public string $technicianView = 'grid'; // 'grid' or 'list'
    public ?int $selectedTechnicianId = null;
    public ?int $selectedRequestId = null;
    public array $selectedRequests = [];
    public bool $showAssignmentModal = false;
    public bool $showBulkModal = false;

    // Filters
    public string $requestFilter = 'all';
    public string $technicianFilter = 'all';

    // Assignment
    public ?int $assignToTechnicianId = null;
    public ?string $scheduledDate = null;
    public ?string $scheduledTime = null;

    protected $queryString = ['technicianView', 'requestFilter'];

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->can(PermissionCatalog::SCHEDULE_MANAGE), 403);

        $this->scheduledDate = today()->format('Y-m-d');
    }

    // Quick assignment
    public function quickAssign(int $requestId, int $technicianId): void
    {
        $workOrder = WorkOrder::findOrFail($requestId);
        $technician = User::findOrFail($technicianId);

        $workOrder->update([
            'assigned_to_user_id' => $technicianId,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $workOrder->events()->create([
            'user_id' => auth()->id(),
            'type' => 'assigned',
            'note' => "Assigned to {$technician->name}",
        ]);

        $this->dispatch('assignment-complete');
        session()->flash('success', "Assigned to {$technician->name}");
    }

    public function openAssignmentModal(int $requestId): void
    {
        $this->selectedRequestId = $requestId;
        $this->showAssignmentModal = true;
    }

    public function closeAssignmentModal(): void
    {
        $this->selectedRequestId = null;
        $this->showAssignmentModal = false;
        $this->assignToTechnicianId = null;
    }

    public function confirmAssignment(): void
    {
        if (!$this->selectedRequestId || !$this->assignToTechnicianId) {
            return;
        }

        $workOrder = WorkOrder::findOrFail($this->selectedRequestId);
        $technician = User::findOrFail($this->assignToTechnicianId);

        $scheduledAt = null;
        if ($this->scheduledDate && $this->scheduledTime) {
            $scheduledAt = Carbon::parse("{$this->scheduledDate} {$this->scheduledTime}");
        }

        $workOrder->update([
            'assigned_to_user_id' => $this->assignToTechnicianId,
            'status' => 'assigned',
            'assigned_at' => now(),
            'scheduled_start_at' => $scheduledAt ?? $workOrder->scheduled_start_at,
        ]);

        $workOrder->events()->create([
            'user_id' => auth()->id(),
            'type' => 'assigned',
            'note' => "Assigned to {$technician->name}" . ($scheduledAt ? " for {$scheduledAt->format('M d g:i A')}" : ''),
        ]);

        $this->closeAssignmentModal();
        session()->flash('success', "Work order assigned to {$technician->name}");
    }

    public function toggleRequestSelection(int $requestId): void
    {
        if (in_array($requestId, $this->selectedRequests)) {
            $this->selectedRequests = array_diff($this->selectedRequests, [$requestId]);
        } else {
            $this->selectedRequests[] = $requestId;
        }
    }

    public function selectAllRequests(): void
    {
        $this->selectedRequests = $this->getUnassignedRequests()->pluck('id')->toArray();
    }

    public function clearSelection(): void
    {
        $this->selectedRequests = [];
    }

    public function openBulkModal(): void
    {
        if (empty($this->selectedRequests)) {
            return;
        }
        $this->showBulkModal = true;
    }

    public function bulkAssign(): void
    {
        if (empty($this->selectedRequests) || !$this->assignToTechnicianId) {
            return;
        }

        $technician = User::findOrFail($this->assignToTechnicianId);
        $count = 0;

        foreach ($this->selectedRequests as $requestId) {
            $workOrder = WorkOrder::find($requestId);
            if ($workOrder && !$workOrder->assigned_to_user_id) {
                $workOrder->update([
                    'assigned_to_user_id' => $this->assignToTechnicianId,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);
                $count++;
            }
        }

        $this->selectedRequests = [];
        $this->showBulkModal = false;
        $this->assignToTechnicianId = null;

        session()->flash('success', "{$count} work orders assigned to {$technician->name}");
    }

    public function selectTechnician(int $technicianId): void
    {
        $this->selectedTechnicianId = $this->selectedTechnicianId === $technicianId ? null : $technicianId;
    }

    public function acknowledgeAlert(int $workOrderId, string $alertType): void
    {
        // Mark alert as acknowledged
        $workOrder = WorkOrder::find($workOrderId);
        if ($workOrder) {
            $workOrder->events()->create([
                'user_id' => auth()->id(),
                'type' => 'alert_acknowledged',
                'note' => "Acknowledged {$alertType} alert",
            ]);
        }
    }

    public function render()
    {
        // Get unassigned requests
        $unassignedRequests = $this->getUnassignedRequests();

        // Get all technicians with their status
        $technicians = $this->getTechnicians();

        // Get KPIs
        $kpis = $this->getKPIs();

        // Get alerts
        $alerts = $this->getAlerts();

        // Get timeline data
        $timeline = $this->getTimelineData();

        // Get recommendations for selected request
        $recommendations = [];
        if ($this->selectedRequestId) {
            $workOrder = WorkOrder::find($this->selectedRequestId);
            if ($workOrder) {
                $recommendationService = app(SchedulingRecommendationService::class);
                $recommendations = $recommendationService->getRecommendations($workOrder, 5);
            }
        }

        // Heat map data (pending requests by location)
        $heatMapData = $this->getHeatMapData($unassignedRequests, $technicians);

        return view('livewire.dispatch-dashboard', [
            'unassignedRequests' => $unassignedRequests,
            'technicians' => $technicians,
            'kpis' => $kpis,
            'alerts' => $alerts,
            'timeline' => $timeline,
            'recommendations' => $recommendations,
            'heatMapData' => $heatMapData,
        ]);
    }

    private function getUnassignedRequests(): Collection
    {
        $query = WorkOrder::with(['organization', 'equipment', 'category'])
            ->whereNull('assigned_to_user_id')
            ->whereNotIn('status', ['completed', 'closed', 'canceled']);

        // Apply filters
        if ($this->requestFilter === 'urgent') {
            $query->where('priority', 'urgent');
        } elseif ($this->requestFilter === 'overdue') {
            $query->where(function ($q) {
                $q->whereNotNull('sla_deadline_at')
                    ->where('sla_deadline_at', '<', now());
            });
        }

        return $query
            ->orderByRaw("CASE priority 
                WHEN 'urgent' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'standard' THEN 3 
                ELSE 4 END")
            ->orderBy('requested_at')
            ->get()
            ->map(function ($wo) {
                // Calculate waiting time
                $wo->waiting_minutes = $wo->requested_at
                    ? now()->diffInMinutes($wo->requested_at)
                    : 0;

                // Calculate SLA urgency
                if ($wo->sla_deadline_at) {
                    $wo->sla_minutes_remaining = now()->diffInMinutes($wo->sla_deadline_at, false);
                    $wo->sla_status = $wo->sla_minutes_remaining < 0 ? 'breached'
                        : ($wo->sla_minutes_remaining < 60 ? 'critical'
                            : ($wo->sla_minutes_remaining < 240 ? 'warning' : 'ok'));
                } else {
                    $wo->sla_minutes_remaining = null;
                    $wo->sla_status = 'none';
                }

                // Urgency score (higher = more urgent)
                $wo->urgency_score = $this->calculateUrgencyScore($wo);

                return $wo;
            })
            ->sortByDesc('urgency_score')
            ->values();
    }

    private function calculateUrgencyScore(WorkOrder $wo): int
    {
        $score = 0;

        // Priority weight
        $priorityScores = ['urgent' => 100, 'high' => 70, 'standard' => 40, 'routine' => 10];
        $score += $priorityScores[$wo->priority] ?? 40;

        // Waiting time weight (1 point per 10 minutes waiting)
        $score += min(50, intval($wo->waiting_minutes / 10));

        // SLA urgency
        if ($wo->sla_minutes_remaining !== null) {
            if ($wo->sla_minutes_remaining < 0) {
                $score += 100; // SLA breached
            } elseif ($wo->sla_minutes_remaining < 60) {
                $score += 80; // Critical
            } elseif ($wo->sla_minutes_remaining < 240) {
                $score += 40; // Warning
            }
        }

        // Customer tier (if available)
        $tierScores = ['enterprise' => 30, 'premium' => 20, 'standard' => 10];
        $tier = $wo->organization?->service_tier ?? 'standard';
        $score += $tierScores[$tier] ?? 10;

        return $score;
    }

    private function getTechnicians(): Collection
    {
        $query = User::role(RoleCatalog::TECHNICIAN)
            ->where('is_active', true)
            ->with([
                'assignedWorkOrders' => function ($q) {
                    $q->whereIn('status', ['assigned', 'in_progress'])
                        ->whereDate('scheduled_start_at', today());
                }
            ]);

        if ($this->technicianFilter === 'available') {
            $query->where('availability_status', 'available');
        } elseif ($this->technicianFilter === 'busy') {
            $query->whereIn('availability_status', ['traveling', 'working', 'on_site']);
        }

        return $query->get()->map(function ($tech) {
            // Current work order
            $tech->current_job = $tech->assignedWorkOrders
                ->where('status', 'in_progress')
                ->first();

            // Today's schedule
            $tech->todays_schedule = $tech->assignedWorkOrders
                ->sortBy('scheduled_start_at');

            // Utilization (scheduled hours / 8 hour day)
            $totalMinutes = $tech->assignedWorkOrders->sum('estimated_minutes') ?? 0;
            $tech->utilization = min(100, round(($totalMinutes / 480) * 100));

            // Performance (actual vs estimated)
            $completedToday = WorkOrder::where('assigned_to_user_id', $tech->id)
                ->whereDate('actual_end_at', today())
                ->whereNotNull('labor_minutes')
                ->whereNotNull('estimated_minutes')
                ->get();

            if ($completedToday->count() > 0) {
                $avgActual = $completedToday->avg('labor_minutes');
                $avgEstimated = $completedToday->avg('estimated_minutes');
                $tech->performance_ratio = $avgEstimated > 0
                    ? round(($avgActual / $avgEstimated) * 100)
                    : 100;
            } else {
                $tech->performance_ratio = 100;
            }

            // Capacity (remaining slots today, assuming 8 hour day and avg 1 hour per job)
            $tech->remaining_capacity = max(0, 8 - $tech->assignedWorkOrders->count());

            // Status color
            $tech->status_color = match ($tech->availability_status) {
                'available' => 'green',
                'traveling' => 'blue',
                'on_site', 'working' => 'orange',
                'break' => 'yellow',
                'off_duty' => 'gray',
                default => 'gray',
            };

            // Check if overdue
            if ($tech->current_job) {
                $started = $tech->current_job->actual_start_at;
                $estimated = $tech->current_job->estimated_minutes ?? 60;
                if ($started && now()->diffInMinutes($started) > $estimated * 1.5) {
                    $tech->status_color = 'red';
                    $tech->is_overdue = true;
                }
            }

            return $tech;
        });
    }

    private function getKPIs(): array
    {
        $today = today();

        // Jobs completed today
        $completedToday = WorkOrder::whereDate('actual_end_at', $today)->count();
        $targetToday = 20; // Could be dynamic based on technician count

        // Jobs in progress
        $inProgress = WorkOrder::where('status', 'in_progress')->count();

        // Average response time (time from request to assignment)
        $avgResponseMinutes = WorkOrder::whereDate('assigned_at', $today)
            ->whereNotNull('requested_at')
            ->get()
            ->avg(fn($wo) => $wo->requested_at?->diffInMinutes($wo->assigned_at) ?? 0);

        // Average completion time
        $avgCompletionMinutes = WorkOrder::whereDate('actual_end_at', $today)
            ->whereNotNull('actual_start_at')
            ->get()
            ->avg(fn($wo) => $wo->actual_start_at?->diffInMinutes($wo->actual_end_at) ?? 0);

        // Technician utilization
        $technicians = User::role(RoleCatalog::TECHNICIAN)->where('is_active', true)->count();
        $busyTechnicians = User::role(RoleCatalog::TECHNICIAN)
            ->where('is_active', true)
            ->whereIn('availability_status', ['traveling', 'on_site', 'working'])
            ->count();
        $utilizationRate = $technicians > 0 ? round(($busyTechnicians / $technicians) * 100) : 0;

        // Historical comparison (last 7 days average)
        $histAvgCompleted = WorkOrder::whereDate('actual_end_at', '>=', $today->copy()->subDays(7))
            ->whereDate('actual_end_at', '<', $today)
            ->count() / 7;

        return [
            'completed_today' => $completedToday,
            'target_today' => $targetToday,
            'completion_percent' => $targetToday > 0 ? round(($completedToday / $targetToday) * 100) : 0,
            'in_progress' => $inProgress,
            'avg_response_minutes' => round($avgResponseMinutes ?? 0),
            'avg_completion_minutes' => round($avgCompletionMinutes ?? 0),
            'utilization_rate' => $utilizationRate,
            'total_technicians' => $technicians,
            'busy_technicians' => $busyTechnicians,
            'hist_avg_completed' => round($histAvgCompleted, 1),
            'pending_count' => WorkOrder::whereNull('assigned_to_user_id')
                ->whereNotIn('status', ['completed', 'closed', 'canceled'])
                ->count(),
        ];
    }

    private function getAlerts(): Collection
    {
        $alerts = collect();

        // Jobs running over time (2x estimated)
        $overtimeJobs = WorkOrder::where('status', 'in_progress')
            ->whereNotNull('actual_start_at')
            ->whereNotNull('estimated_minutes')
            ->get()
            ->filter(function ($wo) {
                $elapsed = now()->diffInMinutes($wo->actual_start_at);
                return $elapsed > ($wo->estimated_minutes * 1.5);
            });

        foreach ($overtimeJobs as $job) {
            $elapsed = now()->diffInMinutes($job->actual_start_at);
            $alerts->push([
                'id' => $job->id,
                'type' => 'overtime',
                'severity' => 'warning',
                'title' => 'Job Running Over Time',
                'message' => "#{$job->id} has been active for {$elapsed} min (est. {$job->estimated_minutes} min)",
                'work_order' => $job,
            ]);
        }

        // SLA violations
        $slaViolations = WorkOrder::whereNotIn('status', ['completed', 'closed', 'canceled'])
            ->whereNotNull('sla_deadline_at')
            ->where('sla_deadline_at', '<', now())
            ->with('organization')
            ->get();

        foreach ($slaViolations as $job) {
            $overdue = now()->diffInMinutes($job->sla_deadline_at);
            $alerts->push([
                'id' => $job->id,
                'type' => 'sla_violation',
                'severity' => 'critical',
                'title' => 'SLA Violated',
                'message' => "#{$job->id} for {$job->organization?->name} is {$overdue} min over SLA",
                'work_order' => $job,
            ]);
        }

        // Near SLA violations (within 1 hour)
        $nearSla = WorkOrder::whereNotIn('status', ['completed', 'closed', 'canceled'])
            ->whereNotNull('sla_deadline_at')
            ->where('sla_deadline_at', '>', now())
            ->where('sla_deadline_at', '<', now()->addHour())
            ->with('organization')
            ->get();

        foreach ($nearSla as $job) {
            $remaining = now()->diffInMinutes($job->sla_deadline_at);
            $alerts->push([
                'id' => $job->id,
                'type' => 'sla_warning',
                'severity' => 'warning',
                'title' => 'SLA At Risk',
                'message' => "#{$job->id} has only {$remaining} min until SLA deadline",
                'work_order' => $job,
            ]);
        }

        // Missed check-ins (assigned > 30 min ago, not checked in)
        $missedCheckins = WorkOrder::where('status', 'assigned')
            ->where('scheduled_start_at', '<', now()->subMinutes(30))
            ->with(['assignedTo', 'organization'])
            ->get();

        foreach ($missedCheckins as $job) {
            $late = now()->diffInMinutes($job->scheduled_start_at);
            $alerts->push([
                'id' => $job->id,
                'type' => 'missed_checkin',
                'severity' => 'warning',
                'title' => 'Technician Late',
                'message' => "{$job->assignedTo?->name} is {$late} min late for #{$job->id}",
                'work_order' => $job,
            ]);
        }

        return $alerts->sortByDesc(fn($a) => $a['severity'] === 'critical' ? 1 : 0)->values();
    }

    private function getTimelineData(): array
    {
        $technicians = User::role(RoleCatalog::TECHNICIAN)
            ->where('is_active', true)
            ->with([
                'assignedWorkOrders' => function ($q) {
                    $q->whereDate('scheduled_start_at', today())
                        ->orderBy('scheduled_start_at');
                }
            ])
            ->get();

        $hours = range(6, 20); // 6 AM to 8 PM

        return [
            'hours' => $hours,
            'technicians' => $technicians->map(fn($tech) => [
                'id' => $tech->id,
                'name' => $tech->name,
                'status' => $tech->availability_status,
                'jobs' => $tech->assignedWorkOrders->map(fn($wo) => [
                    'id' => $wo->id,
                    'start_hour' => $wo->scheduled_start_at?->format('G') ?? 8,
                    'start_minute' => $wo->scheduled_start_at?->format('i') ?? 0,
                    'duration' => $wo->estimated_minutes ?? 60,
                    'title' => \Illuminate\Support\Str::limit($wo->subject ?? $wo->organization?->name ?? "Job #{$wo->id}", 20),
                    'status' => $wo->status,
                    'priority' => $wo->priority,
                ])->toArray(),
            ])->toArray(),
        ];
    }

    private function getHeatMapData(Collection $pendingRequests, Collection $technicians): array
    {
        return [
            'pending' => $pendingRequests
                ->filter(fn($wo) => $wo->location_latitude && $wo->location_longitude)
                ->map(fn($wo) => [
                    'id' => $wo->id,
                    'lat' => $wo->location_latitude,
                    'lng' => $wo->location_longitude,
                    'priority' => $wo->priority,
                    'urgency' => $wo->urgency_score,
                ])->values()->toArray(),
            'technicians' => $technicians
                ->filter(fn($t) => $t->current_latitude && $t->current_longitude)
                ->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'lat' => $t->current_latitude,
                    'lng' => $t->current_longitude,
                    'status' => $t->availability_status,
                ])->values()->toArray(),
        ];
    }
}
