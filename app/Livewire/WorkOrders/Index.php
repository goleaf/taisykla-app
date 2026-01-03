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
use App\Services\WorkOrderMessagingService;
use App\Support\PermissionCatalog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';
    public string $priorityFilter = 'all';
    public string $categoryFilter = '';
    public string $organizationFilter = '';
    public string $technicianFilter = '';
    public string $sortField = 'requested_at';
    public string $sortDirection = 'desc';
    public string $search = '';
    public bool $showForm = false;
    public array $form = [];
    public string $view = 'list';
    public bool $create = false;

    protected $queryString = [
        'view' => ['except' => 'list'],
        'create' => ['except' => false],
    ];

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

    public array $viewOptions = [
        'list',
        'calendar',
        'map',
        'board',
    ];

    public array $sortOptions = [
        'requested_at' => 'Requested date',
        'scheduled_start_at' => 'Scheduled date',
        'created_at' => 'Created date',
        'status' => 'Status',
        'priority' => 'Priority',
        'organization' => 'Customer name',
        'assigned_to_user_id' => 'Assigned technician',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::WORK_ORDERS_VIEW), 403);

        if (! in_array($this->view, $this->viewOptions, true)) {
            $this->view = 'list';
        }

        $this->resetForm();
        if ($this->create && $this->canCreate) {
            $this->view = 'list';
            $this->showForm = true;
        }
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOrganizationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTechnicianFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSortField(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    public function updatedView(): void
    {
        if (! in_array($this->view, $this->viewOptions, true)) {
            $this->view = 'list';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->statusFilter = 'all';
        $this->priorityFilter = 'all';
        $this->categoryFilter = '';
        $this->organizationFilter = '';
        $this->technicianFilter = '';
        $this->search = '';
        $this->sortField = 'requested_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $user = auth()->user();
        $this->form = [
            'organization_id' => $user?->organization_id,
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

    public function startCreate(): void
    {
        if (! $this->canCreate) {
            return;
        }

        $this->view = 'list';
        $this->resetForm();
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'form.organization_id' => ['nullable', 'exists:organizations,id'],
            'form.equipment_id' => ['nullable', 'exists:equipment,id'],
            'form.category_id' => ['nullable', 'exists:work_order_categories,id'],
            'form.assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'form.priority' => ['required', Rule::in($this->priorityOptions)],
            'form.subject' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.scheduled_start_at' => ['nullable', 'date'],
            'form.scheduled_end_at' => ['nullable', 'date', 'after_or_equal:form.scheduled_start_at'],
            'form.time_window' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function saveWorkOrder(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->canCreate) {
            return;
        }

        if ($user->isBusinessCustomer() && $user->organization_id) {
            $this->form['organization_id'] = $user->organization_id;
        }

        if (! $user->canAssignWorkOrders()) {
            $this->form['assigned_to_user_id'] = null;
        }

        $this->validate();

        $status = $this->form['assigned_to_user_id'] ? 'assigned' : 'submitted';
        $assignedAt = $this->form['assigned_to_user_id'] ? now() : null;

        $workOrder = WorkOrder::create([
            'organization_id' => $this->normalizeId($this->form['organization_id']),
            'equipment_id' => $this->normalizeId($this->form['equipment_id']),
            'category_id' => $this->normalizeId($this->form['category_id']),
            'assigned_to_user_id' => $this->normalizeId($this->form['assigned_to_user_id']),
            'assigned_at' => $assignedAt,
            'requested_by_user_id' => $user->id,
            'priority' => $this->form['priority'],
            'status' => $status,
            'subject' => $this->form['subject'],
            'description' => $this->form['description'],
            'requested_at' => now(),
            'scheduled_start_at' => $this->form['scheduled_start_at'],
            'scheduled_end_at' => $this->form['scheduled_end_at'],
            'time_window' => $this->form['time_window'],
        ]);

        WorkOrderEvent::create([
            'work_order_id' => $workOrder->id,
            'user_id' => $user->id,
            'type' => 'created',
            'to_status' => $status,
            'note' => 'Work order created.',
            'meta' => [
                'assigned_to_user_id' => $this->normalizeId($this->form['assigned_to_user_id']),
            ],
        ]);

        app(AuditLogger::class)->log(
            'work_order.created',
            $workOrder,
            'Work order created.',
            ['status' => $status, 'assigned_to_user_id' => $this->form['assigned_to_user_id']]
        );

        app(AutomationService::class)->runForWorkOrder('work_order_created', $workOrder, ['status' => $status]);
        if ($this->form['assigned_to_user_id']) {
            app(AutomationService::class)->runForWorkOrder('work_order_assigned', $workOrder);
        }
        if ($this->form['priority'] === 'urgent') {
            app(AutomationService::class)->runForWorkOrder('work_order_priority_urgent', $workOrder);
        }

        if ($this->form['scheduled_start_at']) {
            Appointment::create([
                'work_order_id' => $workOrder->id,
                'assigned_to_user_id' => $this->normalizeId($this->form['assigned_to_user_id']),
                'scheduled_start_at' => $this->form['scheduled_start_at'],
                'scheduled_end_at' => $this->form['scheduled_end_at'] ?? $this->form['scheduled_start_at'],
                'time_window' => $this->form['time_window'],
                'status' => 'scheduled',
            ]);
        }

        session()->flash('status', 'Work order created.');
        $this->resetForm();
        $this->showForm = false;
    }

    public function updateStatus(int $workOrderId, string $status): void
    {
        $user = auth()->user();
        if (! $user || ! $this->canUpdateStatus) {
            return;
        }

        if (! in_array($status, $this->statusOptions, true)) {
            return;
        }

        $workOrder = $this->findWorkOrderForUser($user, $workOrderId);
        if (! $workOrder) {
            return;
        }

        $previousStatus = $workOrder->status;
        $updates = $this->statusUpdates($workOrder, $status);

        $workOrder->update($updates);

        if ($previousStatus !== $status) {
            WorkOrderEvent::create([
                'work_order_id' => $workOrder->id,
                'user_id' => $user->id,
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

            $workOrder->loadMissing('assignedTo');
            $this->sendStatusUpdateMessage($workOrder, $user, $status);
        }
    }

    public function assignTo(int $workOrderId, ?int $userId): void
    {
        $actor = auth()->user();
        if (! $actor || ! $this->canAssign) {
            return;
        }

        $workOrder = $this->findWorkOrderForUser($actor, $workOrderId);
        if (! $workOrder) {
            return;
        }

        $userId = $this->normalizeId($userId);
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
            $assignedUser = $userId ? User::find($userId) : null;

            WorkOrderEvent::create([
                'work_order_id' => $workOrder->id,
                'user_id' => $actor->id,
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

            $message = $assignedUser
                ? $this->assignmentMessage($workOrder, $assignedUser)
                : 'Your request is awaiting technician assignment.';
            $this->postProgressMessage($workOrder, $actor, $message);
        }
    }

    public function render()
    {
        $user = auth()->user();
        $isBusinessCustomer = $user->isBusinessCustomer();
        $isConsumer = $user->isConsumer();
        $isClient = $user->isCustomer();

        $query = $this->workOrderQueryFor($user);

        if (! $isClient && $this->organizationFilter !== '') {
            $query->where('organization_id', $this->organizationFilter);
        }

        if ($this->categoryFilter !== '') {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->technicianFilter !== '' && $this->canAssign) {
            $query->where('assigned_to_user_id', $this->technicianFilter);
        }

        if ($this->search !== '') {
            $this->applySearch($query, $this->search);
        }

        $summary = $this->buildSummary(clone $query);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $viewQuery = clone $query;
        $this->applySort($viewQuery);
        $workOrders = $viewQuery->paginate(10);
        $calendarGroups = null;
        $mapOrders = null;
        $boardColumns = null;

        if ($this->view !== 'list') {
            $bulkQuery = clone $query;
            $bulkOrders = $bulkQuery->orderByDesc('requested_at')->orderByDesc('created_at')->get();

            if ($this->view === 'calendar') {
                $calendarGroups = $this->calendarGroups($bulkOrders);
            } elseif ($this->view === 'map') {
                $mapOrders = $this->mapOrders($bulkOrders);
            } elseif ($this->view === 'board') {
                $boardColumns = $this->boardColumns($bulkOrders);
            }
        }
        $slaTargets = $this->slaTargets();
        $slaSummaries = $this->buildSlaSummaries($workOrders->items(), $slaTargets);

        $organizations = $isClient ? collect() : Organization::orderBy('name')->get();
        $categories = WorkOrderCategory::orderBy('name')->get();
        $technicians = $this->canAssign
            ? User::role('technician')->orderBy('name')->get()
            : collect();

        $equipment = Equipment::query()
            ->when($isBusinessCustomer, fn ($builder) => $builder->where('organization_id', $user->organization_id))
            ->when($isConsumer, fn ($builder) => $builder->where('assigned_user_id', $user->id))
            ->when(! $isClient && $this->form['organization_id'], fn ($builder) => $builder->where('organization_id', $this->form['organization_id']))
            ->orderBy('name')
            ->get();

        return view('livewire.work-orders.index', [
            'workOrders' => $workOrders,
            'organizations' => $organizations,
            'categories' => $categories,
            'technicians' => $technicians,
            'equipment' => $equipment,
            'user' => $user,
            'isClient' => $isClient,
            'summary' => $summary,
            'slaSummaries' => $slaSummaries,
            'canCreate' => $this->canCreate,
            'canUpdateStatus' => $this->canUpdateStatus,
            'canAssign' => $this->canAssign,
            'calendarGroups' => $calendarGroups,
            'mapOrders' => $mapOrders,
            'boardColumns' => $boardColumns,
        ]);
    }

    public function getCanCreateProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canCreateWorkOrders();
    }

    public function getCanUpdateStatusProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canUpdateWorkOrders();
    }

    public function getCanAssignProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canAssignWorkOrders();
    }

    private function workOrderQueryFor(User $user): Builder
    {
        $query = WorkOrder::query()->with([
            'organization.serviceAgreement',
            'equipment',
            'assignedTo',
            'category',
            'requestedBy',
        ]);

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

    private function findWorkOrderForUser(User $user, int $workOrderId): ?WorkOrder
    {
        return $this->workOrderQueryFor($user)->whereKey($workOrderId)->first();
    }

    private function applySearch(Builder $query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $searchLike = '%' . $search . '%';

        $query->where(function (Builder $builder) use ($search, $searchLike) {
            $builder->where('subject', 'like', $searchLike)
                ->orWhere('description', 'like', $searchLike)
                ->orWhereHas('organization', function (Builder $orgBuilder) use ($searchLike) {
                    $orgBuilder->where('name', 'like', $searchLike);
                })
                ->orWhereHas('equipment', function (Builder $equipmentBuilder) use ($searchLike) {
                    $equipmentBuilder->where('name', 'like', $searchLike);
                })
                ->orWhereHas('assignedTo', function (Builder $userBuilder) use ($searchLike) {
                    $userBuilder->where('name', 'like', $searchLike);
                });

            if (is_numeric($search)) {
                $builder->orWhere('id', (int) $search);
            }
        });
    }

    private function applySort(Builder $query): void
    {
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        match ($this->sortField) {
            'organization' => $query->orderBy(
                Organization::select('name')
                    ->whereColumn('organizations.id', 'work_orders.organization_id'),
                $direction
            ),
            'assigned_to_user_id' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'work_orders.assigned_to_user_id'),
                $direction
            ),
            'scheduled_start_at' => $query->orderBy('scheduled_start_at', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            'status' => $query->orderBy('status', $direction),
            'priority' => $query->orderBy('priority', $direction),
            default => $query->orderBy('requested_at', $direction),
        };
    }

    private function calendarGroups($workOrders): array
    {
        return $workOrders
            ->groupBy(function (WorkOrder $workOrder) {
                $date = $workOrder->scheduled_start_at
                    ?? $workOrder->requested_at
                    ?? $workOrder->created_at;

                return $date?->toDateString() ?? 'Unscheduled';
            })
            ->map(function ($orders, $date) {
                return [
                    'date' => $date,
                    'items' => $orders->sortBy('scheduled_start_at')->values(),
                ];
            })
            ->values()
            ->all();
    }

    private function mapOrders($workOrders): array
    {
        return $workOrders->map(function (WorkOrder $workOrder) {
            $lat = $workOrder->location_latitude;
            $lng = $workOrder->location_longitude;
            $hasCoords = $lat !== null && $lng !== null;

            return [
                'id' => $workOrder->id,
                'subject' => $workOrder->subject,
                'status' => $workOrder->status,
                'priority' => $workOrder->priority,
                'organization' => $workOrder->organization?->name,
                'assigned' => $workOrder->assignedTo?->name,
                'address' => $workOrder->location_address,
                'location' => $workOrder->location_name,
                'lat' => $lat,
                'lng' => $lng,
                'has_coords' => $hasCoords,
                'map_url' => $hasCoords ? $this->mapPointUrl($lat, $lng) : null,
            ];
        })->all();
    }

    private function boardColumns($workOrders): array
    {
        $columns = [
            'submitted' => ['label' => 'Submitted', 'items' => collect()],
            'assigned' => ['label' => 'Assigned', 'items' => collect()],
            'in_progress' => ['label' => 'In Progress', 'items' => collect()],
            'on_hold' => ['label' => 'On Hold', 'items' => collect()],
            'awaiting_approval' => ['label' => 'Awaiting Approval', 'items' => collect()],
            'completed' => ['label' => 'Completed', 'items' => collect()],
            'closed' => ['label' => 'Closed', 'items' => collect()],
        ];

        foreach ($workOrders as $workOrder) {
            if ($workOrder->status === 'completed' && ! $workOrder->customer_signature_at) {
                $columns['awaiting_approval']['items']->push($workOrder);
                continue;
            }

            if (array_key_exists($workOrder->status, $columns)) {
                $columns[$workOrder->status]['items']->push($workOrder);
            }
        }

        foreach ($columns as $key => $column) {
            $columns[$key]['items'] = $column['items']->values();
        }

        return $columns;
    }

    private function mapPointUrl(float $lat, float $lng): string
    {
        return 'https://www.openstreetmap.org/?mlat=' . $lat . '&mlon=' . $lng . '#map=14/' . $lat . '/' . $lng;
    }

    private function buildSummary(Builder $query): array
    {
        $total = (clone $query)->count();
        $submitted = (clone $query)->where('status', 'submitted')->count();
        $assigned = (clone $query)->where('status', 'assigned')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();
        $onHold = (clone $query)->where('status', 'on_hold')->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $closed = (clone $query)->where('status', 'closed')->count();
        $canceled = (clone $query)->where('status', 'canceled')->count();
        $urgent = (clone $query)->where('priority', 'urgent')->count();

        return [
            'total' => $total,
            'active' => $submitted + $assigned + $inProgress + $onHold,
            'submitted' => $submitted,
            'assigned' => $assigned,
            'in_progress' => $inProgress,
            'on_hold' => $onHold,
            'completed' => $completed,
            'closed' => $closed,
            'canceled' => $canceled,
            'urgent' => $urgent,
        ];
    }

    private function statusUpdates(WorkOrder $workOrder, string $status): array
    {
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

        return $updates;
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

    private function normalizeId(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    private function postProgressMessage(WorkOrder $workOrder, User $actor, string $body): void
    {
        if (trim($body) === '') {
            return;
        }

        app(WorkOrderMessagingService::class)->postMessage($workOrder, $actor, $body);
    }

    private function sendStatusUpdateMessage(WorkOrder $workOrder, User $actor, string $status): void
    {
        $message = match ($status) {
            'assigned' => $this->assignmentMessage($workOrder, $workOrder->assignedTo),
            'in_progress' => $workOrder->arrived_at
                ? 'Technician has arrived on site and started service.'
                : 'Service is now in progress.',
            'on_hold' => $workOrder->on_hold_reason
                ? 'Your request is on hold. ' . $workOrder->on_hold_reason
                : 'Your request is on hold. We will follow up with next steps.',
            'completed' => 'Service has been completed. Please review the report and provide your approval.',
            'closed' => 'Your request has been closed. Thank you for working with us.',
            'canceled' => 'Your request has been canceled. Contact support if this is unexpected.',
            default => null,
        };

        if ($message) {
            $this->postProgressMessage($workOrder, $actor, $message);
        }
    }

    private function assignmentMessage(WorkOrder $workOrder, ?User $assignedUser): string
    {
        if (! $assignedUser) {
            return 'Your request has been assigned and is being scheduled.';
        }

        $scheduled = $workOrder->scheduled_start_at?->format('M d, H:i');
        $timeWindow = $workOrder->time_window;

        $message = 'Your request has been assigned to ' . $assignedUser->name . '.';
        if ($scheduled) {
            $message .= ' Scheduled for ' . $scheduled;
            if ($timeWindow) {
                $message .= ' (' . $timeWindow . ')';
            }
            $message .= '.';
        }

        return $message;
    }
}
