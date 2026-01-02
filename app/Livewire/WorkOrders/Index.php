<?php

namespace App\Livewire\WorkOrders;

use App\Models\Appointment;
use App\Models\Equipment;
use App\Models\Organization;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderEvent;
use App\Services\AutomationService;
use App\Services\AuditLogger;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $search = '';
    public bool $showCreate = false;
    public array $new = [];

    protected $paginationTheme = 'tailwind';

    public array $statusOptions = [
        'submitted',
        'assigned',
        'in_progress',
        'on_hold',
        'completed',
        'closed',
        'canceled',
    ];

    public array $priorityOptions = [
        'standard',
        'high',
        'urgent',
    ];

    public function mount(): void
    {
        $this->resetNew();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetNew(): void
    {
        $user = auth()->user();
        $this->new = [
            'organization_id' => $user->organization_id,
            'equipment_id' => null,
            'category_id' => null,
            'assigned_to_user_id' => null,
            'priority' => 'standard',
            'subject' => '',
            'description' => '',
            'scheduled_start_at' => null,
            'scheduled_end_at' => null,
            'time_window' => '',
        ];
    }

    protected function rules(): array
    {
        return [
            'new.organization_id' => ['nullable', 'exists:organizations,id'],
            'new.equipment_id' => ['nullable', 'exists:equipment,id'],
            'new.category_id' => ['nullable', 'exists:work_order_categories,id'],
            'new.assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'new.priority' => ['required', Rule::in($this->priorityOptions)],
            'new.subject' => ['required', 'string', 'max:255'],
            'new.description' => ['nullable', 'string'],
            'new.scheduled_start_at' => ['nullable', 'date'],
            'new.scheduled_end_at' => ['nullable', 'date', 'after_or_equal:new.scheduled_start_at'],
            'new.time_window' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function createWorkOrder(): void
    {
        $user = auth()->user();

        if ($user->hasRole('client') && $user->organization_id) {
            $this->new['organization_id'] = $user->organization_id;
        }

        if (! $user->hasAnyRole(['admin', 'dispatch'])) {
            $this->new['assigned_to_user_id'] = null;
        }

        $this->validate();

        $status = $this->new['assigned_to_user_id'] ? 'assigned' : 'submitted';

        $assignedAt = $this->new['assigned_to_user_id'] ? now() : null;

        $workOrder = WorkOrder::create([
            'organization_id' => $this->new['organization_id'],
            'equipment_id' => $this->new['equipment_id'],
            'category_id' => $this->new['category_id'],
            'assigned_to_user_id' => $this->new['assigned_to_user_id'],
            'assigned_at' => $assignedAt,
            'requested_by_user_id' => $user->id,
            'priority' => $this->new['priority'],
            'status' => $status,
            'subject' => $this->new['subject'],
            'description' => $this->new['description'],
            'requested_at' => now(),
            'scheduled_start_at' => $this->new['scheduled_start_at'],
            'scheduled_end_at' => $this->new['scheduled_end_at'],
            'time_window' => $this->new['time_window'],
        ]);

        WorkOrderEvent::create([
            'work_order_id' => $workOrder->id,
            'user_id' => $user->id,
            'type' => 'created',
            'to_status' => $status,
            'note' => 'Work order created.',
            'meta' => [
                'assigned_to_user_id' => $this->new['assigned_to_user_id'],
            ],
        ]);

        app(AuditLogger::class)->log(
            'work_order.created',
            $workOrder,
            'Work order created.',
            ['status' => $status, 'assigned_to_user_id' => $this->new['assigned_to_user_id']]
        );

        app(AutomationService::class)->runForWorkOrder('work_order_created', $workOrder, ['status' => $status]);
        if ($this->new['assigned_to_user_id']) {
            app(AutomationService::class)->runForWorkOrder('work_order_assigned', $workOrder);
        }
        if ($this->new['priority'] === 'urgent') {
            app(AutomationService::class)->runForWorkOrder('work_order_priority_urgent', $workOrder);
        }

        if ($this->new['scheduled_start_at']) {
            Appointment::create([
                'work_order_id' => $workOrder->id,
                'assigned_to_user_id' => $this->new['assigned_to_user_id'],
                'scheduled_start_at' => $this->new['scheduled_start_at'],
                'scheduled_end_at' => $this->new['scheduled_end_at'] ?? $this->new['scheduled_start_at'],
                'time_window' => $this->new['time_window'],
                'status' => 'scheduled',
            ]);
        }

        session()->flash('status', 'Work order created.');
        $this->resetNew();
        $this->showCreate = false;
    }

    public function updateStatus(int $workOrderId, string $status): void
    {
        if (! in_array($status, $this->statusOptions, true)) {
            return;
        }

        $workOrder = WorkOrder::findOrFail($workOrderId);
        $previousStatus = $workOrder->status;

        $updates = ['status' => $status];
        if ($status === 'assigned' && ! $workOrder->assigned_at) {
            $updates['assigned_at'] = now();
        }
        if ($status === 'in_progress' && ! $workOrder->started_at) {
            $updates['started_at'] = now();
        }
        if ($status === 'completed' && ! $workOrder->completed_at) {
            $updates['completed_at'] = now();
        }
        if ($status === 'canceled' && ! $workOrder->canceled_at) {
            $updates['canceled_at'] = now();
        }

        $workOrder->update($updates);

        if ($previousStatus !== $status) {
            WorkOrderEvent::create([
                'work_order_id' => $workOrder->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'from_status' => $previousStatus,
                'to_status' => $status,
            ]);

            app(AuditLogger::class)->log(
                'work_order.status_changed',
                $workOrder,
                'Work order status updated.',
                ['from' => $previousStatus, 'to' => $status]
            );

            app(AutomationService::class)->runForWorkOrder('work_order_status_changed', $workOrder, [
                'from_status' => $previousStatus,
                'to_status' => $status,
            ]);
        }
    }

    public function assignTo(int $workOrderId, ?int $userId): void
    {
        $userId = $userId ?: null;
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $previousUserId = $workOrder->assigned_to_user_id;

        $updates = [
            'assigned_to_user_id' => $userId,
            'status' => $workOrder->status,
        ];

        if ($userId && $workOrder->status === 'submitted') {
            $updates['status'] = 'assigned';
        }

        if ($userId && ! $workOrder->assigned_at) {
            $updates['assigned_at'] = now();
        }

        $workOrder->update($updates);

        if ($previousUserId !== $userId) {
            WorkOrderEvent::create([
                'work_order_id' => $workOrder->id,
                'user_id' => auth()->id(),
                'type' => 'assignment',
                'note' => $userId ? 'Assigned technician.' : 'Unassigned technician.',
                'meta' => [
                    'assigned_to_user_id' => $userId,
                ],
            ]);

            app(AuditLogger::class)->log(
                'work_order.assignment_changed',
                $workOrder,
                'Work order assignment updated.',
                ['assigned_to_user_id' => $userId]
            );

            if ($userId) {
                app(AutomationService::class)->runForWorkOrder('work_order_assigned', $workOrder);
            }
        }
    }

    public function render()
    {
        $user = auth()->user();

        $query = WorkOrder::query()->with(['organization.serviceAgreement', 'equipment', 'assignedTo']);

        if ($user->hasRole('technician')) {
            $query->where('assigned_to_user_id', $user->id);
        } elseif ($user->hasRole('client')) {
            $query->where('organization_id', $user->organization_id);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';
            $query->where(function ($builder) use ($search) {
                $builder->where('subject', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        $workOrders = $query->latest()->paginate(10);
        $slaTargets = $this->slaTargets();
        $slaSummaries = $this->buildSlaSummaries($workOrders->items(), $slaTargets);

        $organizations = Organization::orderBy('name')->get();
        $categories = WorkOrderCategory::orderBy('name')->get();
        $technicians = User::role('technician')->orderBy('name')->get();

        $equipment = Equipment::query()
            ->when($user->hasRole('client'), fn ($builder) => $builder->where('organization_id', $user->organization_id))
            ->orderBy('name')
            ->get();

        return view('livewire.work-orders.index', [
            'workOrders' => $workOrders,
            'organizations' => $organizations,
            'categories' => $categories,
            'technicians' => $technicians,
            'equipment' => $equipment,
            'user' => $user,
            'slaSummaries' => $slaSummaries,
        ]);
    }

    private function slaTargets(): array
    {
        $settings = SystemSetting::where('group', 'sla')
            ->pluck('value', 'key')
            ->toArray();

        $standard = $this->asInteger($settings['standard_response_minutes'] ?? 240);
        $high = $this->asInteger($settings['high_response_minutes'] ?? 180);
        $urgent = $this->asInteger($settings['urgent_response_minutes'] ?? 60);

        return [
            'standard' => $standard,
            'high' => $high,
            'urgent' => $urgent,
        ];
    }

        private function buildSlaSummaries(array $workOrders, array $targets): array
    {
        $summaries = [];

        foreach ($workOrders as $workOrder) {
            $requestedAt = $workOrder->requested_at ?? $workOrder->created_at;
            $assignedAt = $workOrder->assigned_at;

            if (! $requestedAt) {
                $summaries[$workOrder->id] = [
                    'status' => 'n/a',
                    'response_minutes' => null,
                    'target_minutes' => null,
                ];
                continue;
            }

            $reference = $assignedAt ?? Carbon::now();
            $responseMinutes = $requestedAt->diffInMinutes($reference);

            $agreementTarget = $workOrder->organization?->serviceAgreement?->response_time_minutes;
            $target = $agreementTarget ?? ($targets[$workOrder->priority] ?? null);

            $status = 'n/a';
            if ($target) {
                $threshold = (int) ceil($target * 0.8);
                if ($responseMinutes >= $target) {
                    $status = 'breached';
                } elseif (! $assignedAt && $responseMinutes >= $threshold) {
                    $status = 'at_risk';
                } else {
                    $status = 'on_track';
                }
            }

            $summaries[$workOrder->id] = [
                'status' => $status,
                'response_minutes' => $responseMinutes,
                'target_minutes' => $target,
            ];
        }

        return $summaries;
    }

    private function asInteger(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_array($value)) {
            return (int) ($value['value'] ?? 0);
        }

        return 0;
    }
}
