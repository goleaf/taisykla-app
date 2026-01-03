<?php

namespace App\Livewire\SupportTickets;

use App\Models\Organization;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
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
        abort_unless(auth()->user()?->can(PermissionCatalog::SUPPORT_VIEW), 403);

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
        if (! $user || ! $user->can(PermissionCatalog::SUPPORT_CREATE)) {
            return;
        }

        if ($user->isBusinessCustomer() && $user->organization_id) {
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
            'assigned_to_user_id' => $user->can(PermissionCatalog::SUPPORT_ASSIGN)
                ? $this->new['assigned_to_user_id']
                : null,
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
        if (! auth()->user()?->can(PermissionCatalog::SUPPORT_MANAGE)) {
            return;
        }

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
        $query = $this->ticketQueryFor($user);

        $tickets = $query->latest()->paginate(10);
        $organizations = $this->canManage
            ? Organization::orderBy('name')->get()
            : collect();
        $workOrders = $this->workOrderQueryFor($user)->orderBy('subject')->get();
        $supportManagers = User::permission(PermissionCatalog::SUPPORT_MANAGE)
            ->orderBy('name')
            ->get();

        return view('livewire.support-tickets.index', [
            'tickets' => $tickets,
            'organizations' => $organizations,
            'workOrders' => $workOrders,
            'supportManagers' => $supportManagers,
            'user' => $user,
            'canCreate' => $this->canCreate,
            'canManage' => $this->canManage,
            'canAssign' => $this->canAssign,
        ]);
    }

    public function getCanCreateProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canCreateSupportTickets();
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canManageSupportTickets();
    }

    public function getCanAssignProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canAssignSupportTickets();
    }

    private function ticketQueryFor(User $user): Builder
    {
        $query = SupportTicket::query()->with(['organization', 'workOrder', 'assignedTo']);

        if ($user->can(PermissionCatalog::SUPPORT_VIEW_ALL)) {
            return $query;
        }

        $hasScope = false;
        $query->where(function (Builder $builder) use ($user, &$hasScope) {
            if ($user->can(PermissionCatalog::SUPPORT_VIEW_ORG) && $user->organization_id) {
                $builder->orWhere('organization_id', $user->organization_id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::SUPPORT_VIEW_OWN)) {
                $builder->orWhere('submitted_by_user_id', $user->id);
                $hasScope = true;
            }
        });

        if (! $hasScope) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function workOrderQueryFor(User $user): Builder
    {
        $query = WorkOrder::query();

        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ALL)) {
            return $query;
        }

        $hasScope = false;
        $query->where(function (Builder $builder) use ($user, &$hasScope) {
            if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ASSIGNED)) {
                $builder->orWhere('assigned_to_user_id', $user->id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ORG) && $user->organization_id) {
                $builder->orWhere('organization_id', $user->organization_id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_OWN)) {
                $builder->orWhere('requested_by_user_id', $user->id);
                $hasScope = true;
            }
        });

        if (! $hasScope) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
