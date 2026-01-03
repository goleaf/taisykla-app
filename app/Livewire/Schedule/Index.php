<?php

namespace App\Livewire\Schedule;

use App\Models\Appointment;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $dateFilter = '';
    public ?int $technicianFilter = null;
    public bool $showCreate = false;
    public array $new = [];

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::SCHEDULE_VIEW), 403);

        $this->dateFilter = now()->toDateString();
        $this->resetNew();
    }

    public function resetNew(): void
    {
        $this->new = [
            'work_order_id' => null,
            'assigned_to_user_id' => null,
            'scheduled_start_at' => null,
            'scheduled_end_at' => null,
            'time_window' => '',
            'status' => 'scheduled',
            'notes' => '',
        ];
    }

    public function createAppointment(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->canManageSchedule()) {
            return;
        }

        $this->validate([
            'new.work_order_id' => ['required', 'exists:work_orders,id'],
            'new.assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'new.scheduled_start_at' => ['required', 'date'],
            'new.scheduled_end_at' => ['nullable', 'date', 'after_or_equal:new.scheduled_start_at'],
            'new.time_window' => ['nullable', 'string', 'max:255'],
            'new.status' => ['required', 'string', 'max:50'],
            'new.notes' => ['nullable', 'string'],
        ]);

        Appointment::create($this->new);
        session()->flash('status', 'Appointment scheduled.');
        $this->resetNew();
        $this->showCreate = false;
    }

    public function render()
    {
        $user = auth()->user();
        $query = $this->appointmentQueryFor($user);

        if ($this->dateFilter) {
            $query->whereDate('scheduled_start_at', $this->dateFilter);
        }

        if ($this->technicianFilter) {
            $query->where('assigned_to_user_id', $this->technicianFilter);
        }

        $appointments = $query->orderBy('scheduled_start_at')->paginate(10);
        $technicians = $this->canManage
            ? User::role('technician')->orderBy('name')->get()
            : collect();
        $workOrders = $this->canManage
            ? WorkOrder::orderBy('subject')->get()
            : collect();

        return view('livewire.schedule.index', [
            'appointments' => $appointments,
            'technicians' => $technicians,
            'workOrders' => $workOrders,
            'user' => $user,
        ]);
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canManageSchedule();
    }

    private function appointmentQueryFor(User $user): Builder
    {
        $query = Appointment::query()->with(['workOrder', 'assignedTo']);

        if ($user->can(PermissionCatalog::SCHEDULE_VIEW_ALL)) {
            return $query;
        }

        $hasScope = false;
        $query->where(function (Builder $builder) use ($user, &$hasScope) {
            if ($user->can(PermissionCatalog::SCHEDULE_VIEW_ASSIGNED)) {
                $builder->orWhere('assigned_to_user_id', $user->id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::SCHEDULE_VIEW_ORG) && $user->organization_id) {
                $builder->orWhereHas('workOrder', function (Builder $workOrderBuilder) use ($user) {
                    $workOrderBuilder->where('organization_id', $user->organization_id);
                });
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::SCHEDULE_VIEW_OWN)) {
                $builder->orWhereHas('workOrder', function (Builder $workOrderBuilder) use ($user) {
                    $workOrderBuilder->where('requested_by_user_id', $user->id);
                });
                $hasScope = true;
            }
        });

        if (! $hasScope) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
