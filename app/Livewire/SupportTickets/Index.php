<?php

namespace App\Livewire\SupportTickets;

use App\Models\Organization;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showCreate = false;
    public array $new = [];

    protected $paginationTheme = 'tailwind';

    public array $statusOptions = ['open', 'in_review', 'resolved'];

    public function mount(): void
    {
        $this->resetNew();
    }

    public function resetNew(): void
    {
        $this->new = [
            'organization_id' => null,
            'work_order_id' => null,
            'assigned_to_user_id' => null,
            'priority' => 'standard',
            'subject' => '',
            'description' => '',
        ];
    }

    public function createTicket(): void
    {
        $user = auth()->user();

        if ($user->hasRole('client') && $user->organization_id) {
            $this->new['organization_id'] = $user->organization_id;
        }

        $this->validate([
            'new.organization_id' => ['nullable', 'exists:organizations,id'],
            'new.work_order_id' => ['nullable', 'exists:work_orders,id'],
            'new.assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'new.priority' => ['required', 'string', 'max:50'],
            'new.subject' => ['required', 'string', 'max:255'],
            'new.description' => ['nullable', 'string'],
        ]);

        SupportTicket::create([
            'organization_id' => $this->new['organization_id'],
            'work_order_id' => $this->new['work_order_id'],
            'submitted_by_user_id' => $user->id,
            'assigned_to_user_id' => $this->new['assigned_to_user_id'],
            'priority' => $this->new['priority'],
            'subject' => $this->new['subject'],
            'description' => $this->new['description'],
            'status' => 'open',
        ]);

        session()->flash('status', 'Support ticket created.');
        $this->resetNew();
        $this->showCreate = false;
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        if (! in_array($status, $this->statusOptions, true)) {
            return;
        }

        $ticket = SupportTicket::findOrFail($ticketId);
        $ticket->update([
            'status' => $status,
            'resolved_at' => $status === 'resolved' ? now() : null,
        ]);
    }

    public function render()
    {
        $user = auth()->user();
        $query = SupportTicket::query()->with(['organization', 'workOrder', 'assignedTo']);

        if ($user->hasRole('client')) {
            $query->where('organization_id', $user->organization_id);
        }

        $tickets = $query->latest()->paginate(10);
        $organizations = Organization::orderBy('name')->get();
        $workOrders = WorkOrder::orderBy('subject')->get();
        $supportManagers = User::role('support')->orderBy('name')->get();

        return view('livewire.support-tickets.index', [
            'tickets' => $tickets,
            'organizations' => $organizations,
            'workOrders' => $workOrders,
            'supportManagers' => $supportManagers,
            'user' => $user,
        ]);
    }
}
