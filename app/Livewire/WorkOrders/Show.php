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

        $this->workOrder->update(['status' => $this->status]);
        $this->workOrder->refresh();
    }

    public function assignTechnician(): void
    {
        $this->workOrder->update([
            'assigned_to_user_id' => $this->assignedToUserId,
            'status' => $this->assignedToUserId ? 'assigned' : $this->workOrder->status,
        ]);

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
