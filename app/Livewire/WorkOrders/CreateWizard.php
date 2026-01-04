<?php

namespace App\Livewire\WorkOrders;

use App\Models\Appointment;
use App\Models\Equipment;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderEvent;
use App\Services\AutomationService;
use App\Services\AuditLogger;
use App\Support\PermissionCatalog;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateWizard extends Component
{
    use WithFileUploads;

    // Wizard State
    public int $currentStep = 1;
    public int $totalSteps = 6;
    public bool $isSubmitting = false;

    // Step 1: Customer Selection
    public string $customerSearch = '';
    public ?int $selectedOrganizationId = null;

    // Step 2: Equipment Selection
    public string $equipmentSearch = '';
    public string $equipmentLocationFilter = '';
    public string $equipmentTypeFilter = '';
    public string $equipmentStatusFilter = '';
    public ?int $selectedEquipmentId = null;

    // Step 3: Problem Description
    public string $subject = '';
    public string $description = '';
    public ?int $categoryId = null;
    public string $problemTemplate = '';
    public array $issueMedia = [];

    // Step 4: Priority & Scheduling
    public string $priority = 'standard';
    public string $scheduledDate = '';
    public string $timeWindowPreset = 'morning';
    public string $scheduledTime = '';
    public string $scheduledEndTime = '';
    public string $specialInstructions = '';
    public array $selectedAccessRequirements = [];

    // Step 5: Assignment
    public ?int $assignedTechnicianId = null;

    // Step 6: Review & Submit
    public bool $termsAccepted = false;

    public array $stepTitles = [
        1 => 'Customer',
        2 => 'Equipment',
        3 => 'Problem',
        4 => 'Schedule',
        5 => 'Assignment',
        6 => 'Review',
    ];

    public array $priorityOptions = [
        'standard' => ['label' => 'Standard', 'sla' => '4 hrs response', 'cost' => 'Base rate', 'icon' => '📋'],
        'high' => ['label' => 'High', 'sla' => '2 hrs response', 'cost' => '+15%', 'icon' => '⚡'],
        'urgent' => ['label' => 'Urgent', 'sla' => '1 hr response', 'cost' => '+35%', 'icon' => '🔥'],
    ];

    public array $accessRequirementOptions = [
        'badge_required' => 'Badge required at entrance',
        'escort_required' => 'Escort required onsite',
        'parking_pass' => 'Parking pass needed',
        'after_hours' => 'After-hours access only',
        'security_check' => 'Security check-in required',
        'confined_space' => 'Confined space permit',
        'ladder_required' => 'Ladder access needed',
        'ppe_required' => 'PPE required',
        'key_pickup' => 'Key pickup required',
    ];

    public array $problemTemplates = [
        'no_power' => [
            'label' => 'No power / system down',
            'subject' => 'Equipment not powering on',
            'description' => 'Unit fails to start. Checked outlet and breaker; no visible damage. Error lights flashing after power cycle.',
        ],
        'leak' => [
            'label' => 'Leak / water damage',
            'subject' => 'Leak observed around equipment',
            'description' => 'Customer reports visible leaking near the unit. Leak appears after operation cycle. Floor area is damp.',
        ],
        'noise' => [
            'label' => 'Unusual noise',
            'subject' => 'Unusual noise from equipment',
            'description' => 'Customer hears intermittent rattling and vibration during operation. Noise increases under load.',
        ],
        'performance' => [
            'label' => 'Performance drop',
            'subject' => 'Equipment performance degradation',
            'description' => 'System performance has decreased over the last week. Output is below expected levels and alarms are intermittent.',
        ],
        'error_code' => [
            'label' => 'Error code displayed',
            'subject' => 'Error code on display',
            'description' => 'Equipment is showing an error code on display. Please specify error code in description.',
        ],
        'maintenance' => [
            'label' => 'Routine maintenance',
            'subject' => 'Scheduled maintenance required',
            'description' => 'Equipment is due for routine maintenance as per service schedule.',
        ],
    ];

    protected $listeners = ['confirmSubmit'];

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless($user?->canCreateWorkOrders(), 403);

        // Pre-fill organization for business customers
        if ($user->isBusinessCustomer() && $user->organization_id) {
            $this->selectedOrganizationId = $user->organization_id;
        }

        // Default scheduled date to tomorrow
        $this->scheduledDate = Carbon::tomorrow()->format('Y-m-d');
    }

    public function updatedProblemTemplate(): void
    {
        if ($this->problemTemplate === '' || !isset($this->problemTemplates[$this->problemTemplate])) {
            return;
        }

        $template = $this->problemTemplates[$this->problemTemplate];
        if ($this->subject === '') {
            $this->subject = $template['subject'];
        }
        if ($this->description === '') {
            $this->description = $template['description'];
        }
    }

    public function updatedSelectedOrganizationId(): void
    {
        $this->selectedEquipmentId = null;
        $this->equipmentSearch = '';
        $this->equipmentLocationFilter = '';
        $this->equipmentTypeFilter = '';
        $this->equipmentStatusFilter = '';
    }

    public function nextStep(): void
    {
        $this->validateStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > $this->totalSteps) {
            return;
        }

        // Allow going back without validation
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
            return;
        }

        // Validate all steps up to the target
        for ($i = $this->currentStep; $i < $step; $i++) {
            $this->currentStep = $i;
            try {
                $this->validateStep();
            } catch (\Illuminate\Validation\ValidationException $e) {
                return;
            }
        }

        $this->currentStep = $step;
    }

    public function selectCustomer(int $organizationId): void
    {
        $this->selectedOrganizationId = $organizationId;
    }

    public function selectEquipment(int $equipmentId): void
    {
        $this->selectedEquipmentId = $equipmentId;
    }

    public function selectPriority(string $priority): void
    {
        if (array_key_exists($priority, $this->priorityOptions)) {
            $this->priority = $priority;
        }
    }

    public function toggleAccessRequirement(string $key): void
    {
        if (in_array($key, $this->selectedAccessRequirements, true)) {
            $this->selectedAccessRequirements = array_values(array_diff($this->selectedAccessRequirements, [$key]));
        } else {
            $this->selectedAccessRequirements[] = $key;
        }
    }

    public function selectTechnician(?int $technicianId): void
    {
        $this->assignedTechnicianId = $technicianId;
    }

    public function submit(): void
    {
        $this->validateStep();

        // Dispatch confirmation modal
        $this->dispatch('show-confirmation');
    }

    public function confirmSubmit(): void
    {
        $user = auth()->user();

        if (!$user || !$user->canCreateWorkOrders()) {
            session()->flash('error', 'You do not have permission to create work orders.');
            return;
        }

        $this->isSubmitting = true;

        try {
            // Build scheduled times
            $scheduledStart = null;
            $scheduledEnd = null;

            if ($this->scheduledDate) {
                $defaultTime = match ($this->timeWindowPreset) {
                    'afternoon' => '13:00',
                    default => '09:00',
                };
                $startTime = $this->scheduledTime ?: $defaultTime;
                $scheduledStart = Carbon::parse($this->scheduledDate . ' ' . $startTime);

                if ($this->scheduledEndTime) {
                    $scheduledEnd = Carbon::parse($this->scheduledDate . ' ' . $this->scheduledEndTime);
                }
            }

            $timeWindow = match ($this->timeWindowPreset) {
                'morning' => 'Morning',
                'afternoon' => 'Afternoon',
                'specific' => $this->scheduledTime . ($this->scheduledEndTime ? '-' . $this->scheduledEndTime : ''),
                default => '',
            };

            $status = $this->assignedTechnicianId ? 'assigned' : 'submitted';
            $assignedAt = $this->assignedTechnicianId ? now() : null;

            $workOrder = WorkOrder::create([
                'organization_id' => $this->selectedOrganizationId,
                'equipment_id' => $this->selectedEquipmentId,
                'category_id' => $this->categoryId,
                'assigned_to_user_id' => $this->assignedTechnicianId,
                'assigned_at' => $assignedAt,
                'requested_by_user_id' => $user->id,
                'priority' => $this->priority,
                'status' => $status,
                'subject' => $this->subject,
                'description' => $this->description,
                'requested_at' => now(),
                'scheduled_start_at' => $scheduledStart,
                'scheduled_end_at' => $scheduledEnd,
                'time_window' => $timeWindow,
                'customer_preference_notes' => $this->specialInstructions,
            ]);

            // Log creation event
            WorkOrderEvent::create([
                'work_order_id' => $workOrder->id,
                'user_id' => $user->id,
                'type' => 'created',
                'to_status' => $status,
                'note' => 'Work order created via wizard.',
                'meta' => [
                    'assigned_to_user_id' => $this->assignedTechnicianId,
                    'access_requirements' => $this->selectedAccessRequirements,
                    'special_instructions' => $this->specialInstructions,
                    'problem_template' => $this->problemTemplate ?: null,
                    'attachment_count' => count($this->issueMedia),
                ],
            ]);

            // Audit log
            app(AuditLogger::class)->log(
                'work_order.created',
                $workOrder,
                'Work order created via wizard.',
                ['status' => $status, 'assigned_to_user_id' => $this->assignedTechnicianId]
            );

            // Run automations
            app(AutomationService::class)->runForWorkOrder('work_order_created', $workOrder, ['status' => $status]);
            if ($this->assignedTechnicianId) {
                app(AutomationService::class)->runForWorkOrder('work_order_assigned', $workOrder);
            }
            if ($this->priority === 'urgent') {
                app(AutomationService::class)->runForWorkOrder('work_order_priority_urgent', $workOrder);
            }

            // Create appointment if scheduled
            if ($scheduledStart) {
                Appointment::create([
                    'work_order_id' => $workOrder->id,
                    'assigned_to_user_id' => $this->assignedTechnicianId,
                    'scheduled_start_at' => $scheduledStart,
                    'scheduled_end_at' => $scheduledEnd ?? $scheduledStart,
                    'time_window' => $timeWindow,
                    'status' => 'scheduled',
                ]);
            }

            // Upload media files
            foreach ($this->issueMedia as $media) {
                $path = $media->storePublicly('work-orders/' . $workOrder->id . '/intake', 'public');

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

            session()->flash('status', 'Work order #' . $workOrder->id . ' created successfully!');

            $this->redirect(route('work-orders.show', $workOrder), navigate: true);

        } finally {
            $this->isSubmitting = false;
        }
    }

    public function removeMedia(int $index): void
    {
        if (isset($this->issueMedia[$index])) {
            unset($this->issueMedia[$index]);
            $this->issueMedia = array_values($this->issueMedia);
        }
    }

    protected function validateStep(): void
    {
        $rules = $this->stepRules();

        if ($rules !== []) {
            $this->validate($rules);
        }
    }

    protected function stepRules(): array
    {
        $user = auth()->user();
        $isClient = $user?->isCustomer() ?? false;

        return match ($this->currentStep) {
            1 => [
                'selectedOrganizationId' => [
                    Rule::requiredIf(!$isClient),
                    'nullable',
                    'exists:organizations,id',
                ],
            ],
            2 => [
                'selectedEquipmentId' => [
                    'nullable',
                    'exists:equipment,id',
                ],
            ],
            3 => [
                'subject' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:2000'],
                'categoryId' => ['required', 'exists:work_order_categories,id'],
                'issueMedia' => ['array', 'max:10'],
                'issueMedia.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov', 'max:10240'],
            ],
            4 => [
                'priority' => ['required', Rule::in(array_keys($this->priorityOptions))],
                'scheduledDate' => ['required', 'date', 'after_or_equal:today'],
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
                'assignedTechnicianId' => ['nullable', 'exists:users,id'],
            ],
            6 => [
                'termsAccepted' => ['accepted'],
            ],
            default => [],
        };
    }

    public function render()
    {
        $user = auth()->user();
        $isClient = $user?->isCustomer() ?? false;
        $canAssign = $user?->canAssignWorkOrders() ?? false;

        // Get organizations for step 1
        $organizations = Organization::query()
            ->with('serviceAgreement')
            ->when($this->customerSearch, fn($q) => $q->where('name', 'like', '%' . $this->customerSearch . '%'))
            ->orderBy('name')
            ->limit(20)
            ->get();

        // Get selected organization details
        $selectedOrganization = $this->selectedOrganizationId
            ? Organization::with('serviceAgreement')->find($this->selectedOrganizationId)
            : null;

        // Get recent service history for selected organization
        $recentHistory = $selectedOrganization
            ? WorkOrder::where('organization_id', $selectedOrganization->id)
                ->with(['category', 'assignedTo'])
                ->latest('requested_at')
                ->limit(5)
                ->get()
            : collect();

        // Get equipment for step 2
        $equipmentQuery = Equipment::query()
            ->with(['category', 'warranties' => fn($q) => $q->where('end_date', '>=', now())])
            ->when(
                $isClient && $user->isBusinessCustomer(),
                fn($q) => $q->where('organization_id', $user->organization_id)
            )
            ->when(
                $isClient && $user->isConsumer(),
                fn($q) => $q->where('assigned_user_id', $user->id)
            )
            ->when(
                !$isClient && $this->selectedOrganizationId,
                fn($q) => $q->where('organization_id', $this->selectedOrganizationId)
            );

        $equipmentAll = $equipmentQuery->get();

        // Apply equipment filters
        $equipment = $equipmentAll
            ->when($this->equipmentSearch, fn($c) => $c->filter(
                fn($e) => str_contains(strtolower($e->name), strtolower($this->equipmentSearch))
            ))
            ->when($this->equipmentLocationFilter, fn($c) => $c->filter(
                fn($e) => $e->location_name === $this->equipmentLocationFilter
            ))
            ->when($this->equipmentTypeFilter, fn($c) => $c->filter(
                fn($e) => $e->type === $this->equipmentTypeFilter
            ))
            ->when($this->equipmentStatusFilter, fn($c) => $c->filter(
                fn($e) => $e->status === $this->equipmentStatusFilter
            ))
            ->values();

        // Get filter options from equipment
        $equipmentLocations = $equipmentAll->pluck('location_name')->filter()->unique()->sort()->values();
        $equipmentTypes = $equipmentAll->pluck('type')->filter()->unique()->sort()->values();
        $equipmentStatuses = $equipmentAll->pluck('status')->filter()->unique()->sort()->values();

        // Calculate equipment health scores
        $equipmentMetrics = $equipment->mapWithKeys(function (Equipment $item) {
            $lastService = $item->last_service_at;
            $days = $lastService ? $lastService->diffInDays(Carbon::now()) : null;
            $health = $days === null ? null : max(45, 100 - ($days * 2));
            $hasWarranty = $item->warranties->isNotEmpty();

            return [
                $item->id => [
                    'last_service' => $lastService,
                    'health_score' => $health,
                    'has_warranty' => $hasWarranty,
                    'warranty_end' => $hasWarranty ? $item->warranties->first()?->end_date : null,
                ]
            ];
        });

        // Get selected equipment
        $selectedEquipment = $this->selectedEquipmentId
            ? Equipment::with(['category', 'warranties'])->find($this->selectedEquipmentId)
            : null;

        // Get categories for step 3
        $categories = WorkOrderCategory::where('is_active', true)->orderBy('name')->get();

        // Get availability for step 4
        $availabilityDays = $this->getAvailabilityDays();

        // Get technicians for step 5
        $technicians = $canAssign
            ? User::role('technician')
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect();

        // Calculate technician match scores
        $technicianMatches = $technicians->mapWithKeys(function (User $tech) {
            $score = min(98, 70 + ((strlen($tech->name) * 3) % 25));
            $isAvailable = ($tech->availability_status ?? 'unknown') === 'available';

            return [
                $tech->id => [
                    'score' => $score,
                    'is_available' => $isAvailable,
                    'availability' => $tech->availability_status ?? 'unknown',
                    'skills' => $tech->skills ?? [],
                ]
            ];
        });

        // Get recommended technician
        $recommendedTechnician = $technicians->sortByDesc(
            fn($t) =>
            ($technicianMatches[$t->id]['is_available'] ? 100 : 0) + $technicianMatches[$t->id]['score']
        )->first();

        // Calculate cost estimate
        $estimatedCost = $this->calculateEstimatedCost();

        return view('livewire.work-orders.create-wizard', [
            'organizations' => $organizations,
            'selectedOrganization' => $selectedOrganization,
            'recentHistory' => $recentHistory,
            'equipment' => $equipment,
            'equipmentLocations' => $equipmentLocations,
            'equipmentTypes' => $equipmentTypes,
            'equipmentStatuses' => $equipmentStatuses,
            'equipmentMetrics' => $equipmentMetrics,
            'selectedEquipment' => $selectedEquipment,
            'categories' => $categories,
            'availabilityDays' => $availabilityDays,
            'technicians' => $technicians,
            'technicianMatches' => $technicianMatches,
            'recommendedTechnician' => $recommendedTechnician,
            'estimatedCost' => $estimatedCost,
            'isClient' => $isClient,
            'canAssign' => $canAssign,
        ]);
    }

    protected function getAvailabilityDays(): array
    {
        $start = Carbon::today();
        $end = Carbon::today()->addDays(13)->endOfDay();

        $scheduled = WorkOrder::query()
            ->whereBetween('scheduled_start_at', [$start, $end])
            ->get()
            ->groupBy(fn(WorkOrder $order) => $order->scheduled_start_at?->toDateString() ?? '');

        return collect(range(0, 13))->map(function (int $offset) use ($start, $scheduled) {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();
            $count = $scheduled->get($key, collect())->count();

            $status = match (true) {
                $count >= 8 => 'full',
                $count >= 5 => 'limited',
                $count >= 1 => 'available',
                default => 'open',
            };

            return [
                'date' => $key,
                'label' => $date->format('D, M j'),
                'day' => $date->format('l'),
                'day_short' => $date->format('D'),
                'day_num' => $date->format('j'),
                'month' => $date->format('M'),
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'slots' => match ($status) {
                    'full' => 'Full',
                    'limited' => 'Limited',
                    'available' => 'Available',
                    default => 'Open',
                },
                'status' => $status,
                'count' => $count,
            ];
        })->all();
    }

    protected function calculateEstimatedCost(): array
    {
        $baseLabor = match ($this->priority) {
            'urgent' => 350,
            'high' => 240,
            default => 160,
        };

        $tripFee = 45;
        $attachmentFee = count($this->issueMedia) * 5;

        $subtotal = $baseLabor + $tripFee + $attachmentFee;
        $tax = round($subtotal * 0.21, 2);
        $total = $subtotal + $tax;

        return [
            'labor' => $baseLabor,
            'trip_fee' => $tripFee,
            'attachments' => $attachmentFee,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'formatted' => '€' . number_format($total, 2),
        ];
    }
}
