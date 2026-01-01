<?php

namespace App\Livewire\Schedule;

use App\Models\Appointment;
use App\Models\User;
use App\Models\WorkOrder;
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
        $query = Appointment::query()->with(['workOrder', 'assignedTo']);

        if ($user->hasRole('technician')) {
            $query->where('assigned_to_user_id', $user->id);
        } elseif ($user->hasRole('client')) {
            $query->whereHas('workOrder', function ($builder) use ($user) {
                $builder->where('organization_id', $user->organization_id);
            });
        }

        if ($this->dateFilter) {
            $query->whereDate('scheduled_start_at', $this->dateFilter);
        }

        if ($this->technicianFilter) {
            $query->where('assigned_to_user_id', $this->technicianFilter);
        }

        $appointments = $query->orderBy('scheduled_start_at')->paginate(10);
        $technicians = User::role('technician')->orderBy('name')->get();
        $workOrders = WorkOrder::orderBy('subject')->get();

        return view('livewire.schedule.index', [
            'appointments' => $appointments,
            'technicians' => $technicians,
            'workOrders' => $workOrders,
            'user' => $user,
        ]);
    }
}
