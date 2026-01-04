<?php

namespace App\Livewire\Customer;

use App\Models\Equipment;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Portal extends Component
{
    use WithPagination;

    public string $activeTab = 'overview';
    public string $requestSearch = '';
    public string $requestStatus = '';
    public string $equipmentSearch = '';

    // New request form
    public bool $showRequestForm = false;
    public array $newRequest = [
        'equipment_id' => null,
        'priority' => 'standard',
        'subject' => '',
        'description' => '',
        'preferred_date' => '',
        'preferred_time' => '',
    ];

    protected $queryString = [
        'activeTab' => ['except' => 'overview'],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::DASHBOARD_VIEW), 403);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function startNewRequest(): void
    {
        $this->resetNewRequest();
        $this->showRequestForm = true;
    }

    public function cancelRequest(): void
    {
        $this->showRequestForm = false;
        $this->resetNewRequest();
    }

    public function submitRequest(): void
    {
        $user = auth()->user();
        abort_unless($user?->can(PermissionCatalog::WORK_ORDERS_CREATE), 403);

        $this->validate([
            'newRequest.subject' => ['required', 'string', 'max:255'],
            'newRequest.description' => ['required', 'string', 'max:2000'],
            'newRequest.priority' => ['required', 'in:standard,high,urgent'],
            'newRequest.equipment_id' => ['nullable', 'exists:equipment,id'],
            'newRequest.preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'newRequest.preferred_time' => ['nullable', 'in:morning,afternoon,evening,anytime'],
        ]);

        $workOrder = WorkOrder::create([
            'organization_id' => $user->organization_id,
            'equipment_id' => $this->newRequest['equipment_id'] ?: null,
            'requested_by_user_id' => $user->id,
            'priority' => $this->newRequest['priority'],
            'status' => 'submitted',
            'subject' => $this->newRequest['subject'],
            'description' => $this->newRequest['description'],
            'requested_at' => now(),
            'time_window' => $this->newRequest['preferred_time'] ?: null,
            'scheduled_start_at' => $this->newRequest['preferred_date']
                ? \Carbon\Carbon::parse($this->newRequest['preferred_date'])->startOfDay()
                : null,
        ]);

        session()->flash('success', 'Service request #' . $workOrder->id . ' has been submitted successfully!');
        $this->cancelRequest();
        $this->activeTab = 'requests';
    }

    private function resetNewRequest(): void
    {
        $this->newRequest = [
            'equipment_id' => null,
            'priority' => 'standard',
            'subject' => '',
            'description' => '',
            'preferred_date' => '',
            'preferred_time' => '',
        ];
    }

    public function render()
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;
        $isConsumer = $user->isConsumer();

        // Overview stats
        $stats = $this->buildStats($user, $organizationId, $isConsumer);

        // Recent/Active work orders
        $recentRequests = $this->getWorkOrdersQuery($user, $organizationId, $isConsumer)
            ->whereIn('status', ['submitted', 'assigned', 'in_progress'])
            ->orderByDesc('requested_at')
            ->limit(5)
            ->get();

        // Paginated requests
        $requests = $this->getWorkOrdersQuery($user, $organizationId, $isConsumer)
            ->when($this->requestSearch, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('subject', 'like', '%' . $this->requestSearch . '%')
                        ->orWhere('description', 'like', '%' . $this->requestSearch . '%');
                });
            })
            ->when($this->requestStatus, function (Builder $query) {
                $query->where('status', $this->requestStatus);
            })
            ->orderByDesc('requested_at')
            ->paginate(10);

        // Equipment
        $equipment = Equipment::query()
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->when($isConsumer, fn($q) => $q->where('assigned_user_id', $user->id))
            ->when($this->equipmentSearch, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->equipmentSearch . '%')
                        ->orWhere('serial_number', 'like', '%' . $this->equipmentSearch . '%');
                });
            })
            ->orderBy('name')
            ->paginate(12);

        // Equipment for dropdown
        $equipmentOptions = Equipment::query()
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->when($isConsumer, fn($q) => $q->where('assigned_user_id', $user->id))
            ->orderBy('name')
            ->get();

        return view('livewire.customer.portal', [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'requests' => $requests,
            'equipment' => $equipment,
            'equipmentOptions' => $equipmentOptions,
        ]);
    }

    private function buildStats($user, $organizationId, bool $isConsumer): array
    {
        $query = $this->getWorkOrdersQuery($user, $organizationId, $isConsumer);

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->whereIn('status', ['submitted', 'assigned', 'in_progress'])->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'this_month' => (clone $query)->whereMonth('requested_at', now()->month)->count(),
        ];
    }

    private function getWorkOrdersQuery($user, $organizationId, bool $isConsumer): Builder
    {
        return WorkOrder::query()
            ->with(['equipment', 'assignedTo'])
            ->when($organizationId && !$isConsumer, fn($q) => $q->where('organization_id', $organizationId))
            ->when($isConsumer, fn($q) => $q->where('requested_by_user_id', $user->id));
    }
}
