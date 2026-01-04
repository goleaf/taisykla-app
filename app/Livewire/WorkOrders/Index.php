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
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

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
    public int $wizardStep = 1;
    public string $customerSearch = '';
    public string $equipmentSearch = '';
    public string $equipmentLocationFilter = '';
    public string $equipmentTypeFilter = '';
    public string $equipmentStatusFilter = '';
    public string $problemTemplate = '';
    public array $issueMedia = [];
    public string $scheduledDate = '';
    public string $scheduledTime = '';
    public string $scheduledEndTime = '';
    public string $timeWindowPreset = 'morning';
    public string $specialInstructions = '';
    public array $selectedAccessRequirements = [];
    public bool $termsAccepted = false;
    public array $selected = [];
    public string $bulkAction = '';
    public ?int $bulkTechnicianId = null;
    public string $bulkPriority = '';
    public string $calendarView = 'week';

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
        $this->resetWizardState();
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

    public function resetWizardState(): void
    {
        $this->wizardStep = 1;
        $this->customerSearch = '';
        $this->equipmentSearch = '';
        $this->equipmentLocationFilter = '';
        $this->equipmentTypeFilter = '';
        $this->equipmentStatusFilter = '';
        $this->problemTemplate = '';
        $this->issueMedia = [];
        $this->scheduledDate = '';
        $this->scheduledTime = '';
        $this->scheduledEndTime = '';
        $this->timeWindowPreset = 'morning';
        $this->specialInstructions = '';
        $this->selectedAccessRequirements = [];
        $this->termsAccepted = false;
    }

    public function startCreate(): void
    {
        if (! $this->canCreate) {
            return;
        }

        $this->view = 'list';
        $this->resetForm();
        $this->resetWizardState();
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->resetWizardState();
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
            'scheduledDate' => ['nullable', 'date'],
            'scheduledTime' => ['nullable', 'date_format:H:i'],
            'scheduledEndTime' => ['nullable', 'date_format:H:i', 'after:scheduledTime'],
            'timeWindowPreset' => ['nullable', Rule::in(['morning', 'afternoon', 'specific'])],
            'specialInstructions' => ['nullable', 'string', 'max:1000'],
            'selectedAccessRequirements' => ['array'],
            'issueMedia' => ['array'],
            'issueMedia.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov', 'max:10240'],
            'termsAccepted' => ['accepted'],
        ];
    }

    public function nextStep(): void
    {
        $this->validateStep($this->wizardStep);

        if ($this->wizardStep < 6) {
            $this->wizardStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->wizardStep > 1) {
            $this->wizardStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 6) {
            return;
        }

        $this->wizardStep = $step;
    }

    public function updatedProblemTemplate(): void
    {
        if ($this->problemTemplate === '') {
            return;
        }

        $templates = $this->problemTemplates();
        if (! array_key_exists($this->problemTemplate, $templates)) {
            return;
        }

        $template = $templates[$this->problemTemplate];
        if (empty($this->form['subject'])) {
            $this->form['subject'] = $template['subject'];
        }
        if (empty($this->form['description'])) {
            $this->form['description'] = $template['description'];
        }
        if (! $this->form['category_id'] && $template['category_id']) {
            $this->form['category_id'] = $template['category_id'];
        }
    }

    public function updatedFormOrganizationId(): void
    {
        $this->form['equipment_id'] = null;
        $this->equipmentSearch = '';
        $this->equipmentLocationFilter = '';
        $this->equipmentTypeFilter = '';
        $this->equipmentStatusFilter = '';
    }

    public function updatedBulkAction(): void
    {
        $this->bulkTechnicianId = null;
        $this->bulkPriority = '';
    }

    public function sortBy(string $field): void
    {
        if (! array_key_exists($field, $this->sortOptions)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function bulkApply(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->canUpdateStatus) {
            return;
        }

        $this->resetErrorBag('bulk');

        if ($this->bulkAction === '') {
            $this->addError('bulk', 'Choose a bulk action.');
            return;
        }

        if ($this->selected === []) {
            $this->addError('bulk', 'Select at least one work order.');
            return;
        }

        $workOrders = $this->workOrderQueryFor($user)
            ->whereIn('id', $this->selected)
            ->get();

        if ($workOrders->isEmpty()) {
            $this->addError('bulk', 'No matching work orders found for your selection.');
            return;
        }

        if ($this->bulkAction === 'assign') {
            if (! $this->canAssign) {
                $this->addError('bulk', 'You do not have permission to assign work orders.');
                return;
            }
            $assignedUserId = $this->normalizeId($this->bulkTechnicianId);

            foreach ($workOrders as $order) {
                $previousUserId = $order->assigned_to_user_id;
                $updates = [
                    'assigned_to_user_id' => $assignedUserId,
                    'status' => $order->status,
                ];
                if ($assignedUserId && $order->status === 'submitted') {
                    $updates['status'] = 'assigned';
                }
                if ($assignedUserId && ! $order->assigned_at) {
                    $updates['assigned_at'] = now();
                }
                $order->update($updates);

                if ($previousUserId !== $assignedUserId) {
                    WorkOrderEvent::create([
                        'work_order_id' => $order->id,
                        'user_id' => $user->id,
                        'type' => 'assignment',
                        'note' => $assignedUserId ? 'Assigned technician.' : 'Unassigned technician.',
                        'meta' => ['assigned_to_user_id' => $assignedUserId],
                    ]);
                }
            }
            session()->flash('status', 'Assigned technician for ' . $workOrders->count() . ' work orders.');
        } elseif ($this->bulkAction === 'priority') {
            if (! in_array($this->bulkPriority, $this->priorityOptions, true)) {
                $this->addError('bulk', 'Choose a valid priority.');
                return;
            }
            foreach ($workOrders as $order) {
                $previousPriority = $order->priority;
                $order->update(['priority' => $this->bulkPriority]);
                if ($previousPriority !== $this->bulkPriority) {
                    WorkOrderEvent::create([
                        'work_order_id' => $order->id,
                        'user_id' => $user->id,
                        'type' => 'priority_change',
                        'note' => 'Priority changed to ' . $this->bulkPriority . '.',
                    ]);
                }
            }
            session()->flash('status', 'Updated priority for ' . $workOrders->count() . ' work orders.');
        } elseif ($this->bulkAction === 'export') {
            session()->flash('status', 'Export queued for ' . $workOrders->count() . ' work orders.');
        }

        $this->selected = [];
        $this->bulkAction = '';
        $this->bulkTechnicianId = null;
        $this->bulkPriority = '';
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function saveWorkOrder(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->canCreate) {
            return;
        }

        $this->syncSchedulingFields();
        $this->validate(array_merge($this->rules(), $this->stepRules(6)));

        if ($user->isBusinessCustomer() && $user->organization_id) {
            $this->form['organization_id'] = $user->organization_id;
        }

        if (! $user->canAssignWorkOrders()) {
            $this->form['assigned_to_user_id'] = null;
        }

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
                'access_requirements' => $this->selectedAccessRequirements,
                'special_instructions' => $this->specialInstructions,
                'problem_template' => $this->problemTemplate ?: null,
                'attachment_count' => count($this->issueMedia),
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

        foreach ($this->issueMedia as $media) {
            $path = $media->storePublicly('work-orders/'.$workOrder->id.'/intake', 'public');

            $workOrder->attachments()->create([
                'uploaded_by_user_id' => $user->id,
                'label' => 'Issue attachment',
                'file_name' => $media->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $media->getSize(),
                'mime_type' => $media->getMimeType(),
                'kind' => 'issue',
            ]);
        }

        session()->flash('status', 'Work order created.');
        $this->resetForm();
        $this->resetWizardState();
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
        $categories = app(\App\Services\ReferenceDataService::class)->getAllWorkOrderCategories();
        $technicians = $this->canAssign
            ? User::role('technician')->orderBy('name')->get()
            : collect();

        $equipment = Equipment::query()
            ->when($isBusinessCustomer, fn ($builder) => $builder->where('organization_id', $user->organization_id))
            ->when($isConsumer, fn ($builder) => $builder->where('assigned_user_id', $user->id))
            ->when(! $isClient && $this->form['organization_id'], fn ($builder) => $builder->where('organization_id', $this->form['organization_id']))
            ->orderBy('name')
            ->get();

        $customerOptions = Organization::query()
            ->with('serviceAgreement')
            ->when($this->customerSearch !== '', function (Builder $builder) {
                $builder->where('name', 'like', '%' . $this->customerSearch . '%');
            })
            ->orderBy('name')
            ->get();

        $selectedOrganization = $this->form['organization_id']
            ? Organization::with('serviceAgreement')->find($this->form['organization_id'])
            : null;

        $recentServiceHistory = $selectedOrganization
            ? WorkOrder::query()
                ->where('organization_id', $selectedOrganization->id)
                ->latest('requested_at')
                ->limit(3)
                ->get()
            : collect();

        $wizardEquipment = $equipment->filter(function (Equipment $item) {
            if ($this->equipmentSearch !== '' && ! str_contains(strtolower($item->name), strtolower($this->equipmentSearch))) {
                return false;
            }
            if ($this->equipmentLocationFilter !== '' && $item->location_name !== $this->equipmentLocationFilter) {
                return false;
            }
            if ($this->equipmentTypeFilter !== '' && $item->type !== $this->equipmentTypeFilter) {
                return false;
            }
            if ($this->equipmentStatusFilter !== '' && $item->status !== $this->equipmentStatusFilter) {
                return false;
            }

            return true;
        })->values();

        $equipmentLocations = $equipment->pluck('location_name')->filter()->unique()->sort()->values();
        $equipmentTypes = $equipment->pluck('type')->filter()->unique()->sort()->values();
        $equipmentStatuses = $equipment->pluck('status')->filter()->unique()->sort()->values();

        $equipmentMetrics = $wizardEquipment->mapWithKeys(function (Equipment $item) {
            $lastService = $item->last_service_at;
            $days = $lastService ? $lastService->diffInDays(Carbon::now()) : null;
            $health = $days === null ? null : max(45, 100 - ($days * 2));

            return [$item->id => [
                'last_service' => $lastService,
                'health_score' => $health,
            ]];
        });

        $priorityDetails = [
            'standard' => ['label' => 'Standard', 'sla' => '4 hrs response', 'cost' => 'Base rate'],
            'high' => ['label' => 'High', 'sla' => '2 hrs response', 'cost' => '+15%'],
            'urgent' => ['label' => 'Urgent', 'sla' => '1 hr response', 'cost' => '+35%'],
        ];

        $requiredSkills = $this->requiredSkillsForCategory($categories->firstWhere('id', $this->form['category_id']));
        $technicianMatches = $technicians->mapWithKeys(function (User $technician) use ($requiredSkills) {
            $score = min(98, 70 + ((strlen($technician->name) * 3) % 25));

            return [$technician->id => [
                'score' => $score,
                'skills' => $requiredSkills,
                'availability' => $technician->availability_status ?? 'unknown',
            ]];
        });
        $recommendedTechnician = $technicians->firstWhere('availability_status', 'available') ?? $technicians->first();

        $availabilityDays = $this->availabilityDays();
        $estimatedCost = $this->estimateCost();

        return view('livewire.work-orders.index', [
            'workOrders' => $workOrders,
            'organizations' => $organizations,
            'customerOptions' => $customerOptions,
            'selectedOrganization' => $selectedOrganization,
            'recentServiceHistory' => $recentServiceHistory,
            'categories' => $categories,
            'technicians' => $technicians,
            'equipment' => $equipment,
            'wizardEquipment' => $wizardEquipment,
            'equipmentLocations' => $equipmentLocations,
            'equipmentTypes' => $equipmentTypes,
            'equipmentStatuses' => $equipmentStatuses,
            'equipmentMetrics' => $equipmentMetrics,
            'priorityDetails' => $priorityDetails,
            'problemTemplates' => $this->problemTemplates(),
            'accessOptions' => $this->accessRequirementOptions(),
            'availabilityDays' => $availabilityDays,
            'recommendedTechnician' => $recommendedTechnician,
            'technicianMatches' => $technicianMatches,
            'estimatedCost' => $estimatedCost,
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
                $count = $orders->count();
                $capacity = match (true) {
                    $count >= 6 => 'over',
                    $count >= 4 => 'tight',
                    $count >= 1 => 'open',
                    default => 'empty',
                };

                return [
                    'date' => $date,
                    'items' => $orders->sortBy('scheduled_start_at')->values(),
                    'count' => $count,
                    'capacity' => $capacity,
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

    private function validateStep(int $step): void
    {
        $rules = $this->stepRules($step);
        if ($rules === []) {
            return;
        }

        $this->validate($rules);
    }

    private function stepRules(int $step): array
    {
        return match ($step) {
            1 => [
                'form.organization_id' => [
                    Rule::requiredIf(! $this->isClientUser()),
                    'exists:organizations,id',
                ],
            ],
            2 => [
                'form.equipment_id' => [
                    Rule::requiredIf($this->equipmentRequiresSelection()),
                    'nullable',
                    'exists:equipment,id',
                ],
            ],
            3 => [
                'form.subject' => ['required', 'string', 'max:255'],
                'form.description' => ['required', 'string', 'max:2000'],
                'form.category_id' => ['required', 'exists:work_order_categories,id'],
                'issueMedia' => ['array'],
                'issueMedia.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov', 'max:10240'],
            ],
            4 => [
                'form.priority' => ['required', Rule::in($this->priorityOptions)],
                'scheduledDate' => ['required', 'date'],
                'timeWindowPreset' => ['required', Rule::in(['morning', 'afternoon', 'specific'])],
                'scheduledTime' => [
                    Rule::requiredIf($this->timeWindowPreset === 'specific'),
                    'nullable',
                    'date_format:H:i',
                ],
                'scheduledEndTime' => ['nullable', 'date_format:H:i', 'after:scheduledTime'],
                'specialInstructions' => ['nullable', 'string', 'max:1000'],
                'selectedAccessRequirements' => ['array'],
            ],
            5 => [
                'form.assigned_to_user_id' => ['nullable', 'exists:users,id'],
            ],
            6 => [
                'termsAccepted' => ['accepted'],
            ],
            default => [],
        };
    }

    private function isClientUser(): bool
    {
        $user = auth()->user();

        return $user ? $user->isCustomer() : false;
    }

    private function equipmentRequiresSelection(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $query = Equipment::query();

        if ($user->isBusinessCustomer() && $user->organization_id) {
            $query->where('organization_id', $user->organization_id);
        } elseif ($user->isConsumer()) {
            $query->where('assigned_user_id', $user->id);
        } elseif ($this->form['organization_id']) {
            $query->where('organization_id', $this->form['organization_id']);
        }

        return $query->exists();
    }

    private function syncSchedulingFields(): void
    {
        $this->form['time_window'] = $this->formatTimeWindow();

        if (! $this->scheduledDate) {
            $this->form['scheduled_start_at'] = null;
            $this->form['scheduled_end_at'] = null;
            return;
        }

        $defaultTime = match ($this->timeWindowPreset) {
            'afternoon' => '13:00',
            default => '09:00',
        };

        $startTime = $this->scheduledTime ?: $defaultTime;
        $scheduledStart = Carbon::parse($this->scheduledDate . ' ' . $startTime);
        $scheduledEnd = $this->scheduledEndTime
            ? Carbon::parse($this->scheduledDate . ' ' . $this->scheduledEndTime)
            : null;

        $this->form['scheduled_start_at'] = $scheduledStart;
        $this->form['scheduled_end_at'] = $scheduledEnd;
    }

    private function formatTimeWindow(): string
    {
        return match ($this->timeWindowPreset) {
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
            'specific' => $this->scheduledTime
                ? ($this->scheduledEndTime ? $this->scheduledTime . '-' . $this->scheduledEndTime : $this->scheduledTime)
                : 'Specific',
            default => '',
        };
    }

    private function availabilityDays(): array
    {
        $start = Carbon::today();
        $end = Carbon::today()->addDays(6)->endOfDay();

        $scheduled = WorkOrder::query()
            ->whereBetween('scheduled_start_at', [$start, $end])
            ->get()
            ->groupBy(fn (WorkOrder $order) => $order->scheduled_start_at?->toDateString() ?? '');

        return collect(range(0, 6))->map(function (int $offset) use ($start, $scheduled) {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();
            $count = $scheduled->get($key, collect())->count();
            $status = match (true) {
                $count >= 6 => 'full',
                $count >= 4 => 'limited',
                $count >= 1 => 'available',
                default => 'open',
            };

            return [
                'date' => $key,
                'label' => $date->format('D, M j'),
                'slots' => match ($status) {
                    'full' => 'Full',
                    'limited' => 'Limited',
                    'available' => 'Available',
                    default => 'Open',
                },
                'status' => $status,
            ];
        })->all();
    }

    private function estimateCost(): string
    {
        $base = match ($this->form['priority'] ?? 'standard') {
            'urgent' => 350,
            'high' => 240,
            default => 160,
        };

        $attachments = count($this->issueMedia);
        $estimate = $base + ($attachments * 10);

        return '$' . number_format($estimate, 2);
    }

    private function problemTemplates(): array
    {
        return [
            'no_power' => [
                'label' => 'No power / system down',
                'subject' => 'Equipment not powering on',
                'description' => 'Unit fails to start. Checked outlet and breaker; no visible damage. Error lights flashing after power cycle.',
                'category_id' => null,
            ],
            'leak' => [
                'label' => 'Leak / water damage',
                'subject' => 'Leak observed around equipment',
                'description' => 'Customer reports visible leaking near the unit. Leak appears after operation cycle. Floor area is damp.',
                'category_id' => null,
            ],
            'noise' => [
                'label' => 'Unusual noise',
                'subject' => 'Unusual noise from equipment',
                'description' => 'Customer hears intermittent rattling and vibration during operation. Noise increases under load.',
                'category_id' => null,
            ],
            'performance' => [
                'label' => 'Performance drop',
                'subject' => 'Equipment performance degradation',
                'description' => 'System performance has decreased over the last week. Output is below expected levels and alarms are intermittent.',
                'category_id' => null,
            ],
        ];
    }

    private function accessRequirementOptions(): array
    {
        return [
            'badge_required' => 'Badge required at entrance',
            'escort_required' => 'Escort required onsite',
            'parking_pass' => 'Parking pass needed',
            'after_hours' => 'After-hours access only',
            'security_check' => 'Security check-in required',
            'confined_space' => 'Confined space permit',
            'ladder_required' => 'Ladder access needed',
        ];
    }

    private function requiredSkillsForCategory(?WorkOrderCategory $category): array
    {
        if (! $category) {
            return ['General Service'];
        }

        $name = strtolower($category->name);

        return match (true) {
            str_contains($name, 'electrical') => ['Electrical', 'Diagnostics', 'Safety lockout'],
            str_contains($name, 'hvac') => ['HVAC', 'Refrigerant', 'Controls'],
            str_contains($name, 'plumbing') => ['Plumbing', 'Leak detection', 'Valve repair'],
            str_contains($name, 'network') => ['Networking', 'Hardware', 'Cabling'],
            default => ['General Service', 'Diagnostics'],
        };
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
