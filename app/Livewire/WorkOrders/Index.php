<?php

namespace App\Livewire\WorkOrders;

use App\Models\Appointment;
use App\Models\Equipment;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderEvent;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

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
        }
    }

    public function assignTo(int $workOrderId, ?int $userId): void
    {
        $userId = $userId ?: null;
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $previousUserId = $workOrder->assigned_to_user_id;

        $updates = [
            'assigned_to_user_id' => $userId,
            'status' => $userId ? 'assigned' : $workOrder->status,
        ];

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
        }
    }

    public function render()
    {
        $user = auth()->user();

        $query = WorkOrder::query()->with(['organization', 'equipment', 'assignedTo']);

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
        ]);
    }
}
