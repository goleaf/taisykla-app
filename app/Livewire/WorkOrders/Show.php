<?php

namespace App\Livewire\WorkOrders;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderEvent;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Show extends Component
{
    public WorkOrder $workOrder;
    public string $status = '';
    public ?int $assignedToUserId = null;
    public string $note = '';

    public array $statusOptions = [
        'submitted',
        'assigned',
        'in_progress',
        'on_hold',
        'completed',
        'closed',
        'canceled',
    ];

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder->load([
            'organization',
            'equipment',
            'assignedTo',
            'requestedBy',
            'appointments',
            'parts.part',
            'feedback',
            'events.user',
        ]);
        $this->status = $this->workOrder->status;
        $this->assignedToUserId = $this->workOrder->assigned_to_user_id;
    }

    public function updateStatus(): void
    {
        $this->validate([
            'status' => ['required', Rule::in($this->statusOptions)],
        ]);

        $previousStatus = $this->workOrder->status;

        $updates = ['status' => $this->status];
        if ($this->status === 'assigned' && ! $this->workOrder->assigned_at) {
            $updates['assigned_at'] = now();
        }
        if ($this->status === 'in_progress' && ! $this->workOrder->started_at) {
            $updates['started_at'] = now();
        }
        if ($this->status === 'completed' && ! $this->workOrder->completed_at) {
            $updates['completed_at'] = now();
        }
        if ($this->status === 'canceled' && ! $this->workOrder->canceled_at) {
            $updates['canceled_at'] = now();
        }

        $this->workOrder->update($updates);

        if ($previousStatus !== $this->status) {
            WorkOrderEvent::create([
                'work_order_id' => $this->workOrder->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'from_status' => $previousStatus,
                'to_status' => $this->status,
            ]);
        }
        $this->workOrder->refresh();
    }

    public function assignTechnician(): void
    {
        $previousUserId = $this->workOrder->assigned_to_user_id;
        $updates = [
            'assigned_to_user_id' => $this->assignedToUserId,
            'status' => $this->assignedToUserId ? 'assigned' : $this->workOrder->status,
        ];

        if ($this->assignedToUserId && ! $this->workOrder->assigned_at) {
            $updates['assigned_at'] = now();
        }

        $this->workOrder->update($updates);

        if ($previousUserId !== $this->assignedToUserId) {
            WorkOrderEvent::create([
                'work_order_id' => $this->workOrder->id,
                'user_id' => auth()->id(),
                'type' => 'assignment',
                'note' => $this->assignedToUserId ? 'Assigned technician.' : 'Unassigned technician.',
                'meta' => [
                    'assigned_to_user_id' => $this->assignedToUserId,
                ],
            ]);
        }

        $this->workOrder->refresh();
    }

    public function addNote(): void
    {
        $this->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'note' => $this->note,
        ]);

        $this->note = '';
        $this->workOrder->refresh();
    }

    public function render()
    {
        $technicians = User::role('technician')->orderBy('name')->get();

        return view('livewire.work-orders.show', [
            'technicians' => $technicians,
        ]);
    }
}
