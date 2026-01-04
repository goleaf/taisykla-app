<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\Organization;
use App\Models\Part;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderEvent;
use App\Models\WorkOrderFeedback;
use App\Models\WorkOrderPart;
use App\Services\AuditLogger;
use App\Support\RoleCatalog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;

class Dashboard extends Component
{
    public array $availabilityOptions = [
        'available' => 'Available',
        'traveling' => 'Traveling',
        'on_site' => 'On Site',
        'working' => 'Working',
        'on_break' => 'On Break',
        'off_duty' => 'Off Duty',
    ];

    private array $availabilityColors = [
        'available' => 'text-green-600',
        'traveling' => 'text-blue-600',
        'on_site' => 'text-indigo-600',
        'working' => 'text-orange-600',
        'on_break' => 'text-yellow-600',
        'off_duty' => 'text-gray-500',
        'offline' => 'text-gray-500',
        'unavailable' => 'text-yellow-600',
        'offline' => 'text-gray-500',
    ];

    public array $quickReplies = [];
    public ?string $emergencyAlertedAt = null;

    public function updateAvailability(string $status): void
    {
        if (!array_key_exists($status, $this->availabilityOptions)) {
            return;
        }

        $user = auth()->user();
        if (!$user) {
            return;
        }

        $user->update([
            'availability_status' => $status,
            'availability_updated_at' => now(),
            'last_seen_at' => now(),
        ]);

        app(AuditLogger::class)->log(
            'user.availability_updated',
            $user,
            'Availability updated.',
            ['status' => $status]
        );
    }

    public function render()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $roleKey = $this->roleKey($user);
        $primaryRole = $this->primaryRole($user);

        $summaryCards = $this->summaryCardsFor($roleKey, $user, $today);
        $sections = $this->sectionsFor($roleKey, $user, $today);
        $roles = $user ? $user->getRoleNames()->values()->all() : [];
        $permissionCount = $user ? $user->getAllPermissions()->count() : 0;
        $technicianData = $roleKey === 'technician' && $user
            ? $this->technicianDashboard($user, $today)
            : null;
        $dispatchData = $roleKey === 'dispatch' && $user
            ? $this->dispatchDashboard($user, $today)
            : null;
        $adminData = $roleKey === 'admin' && $user
            ? $this->adminDashboard($today)
            : null;

        return view('livewire.dashboard', [
            'user' => $user,
            'roleKey' => $roleKey,
            'roleLabel' => RoleCatalog::label($primaryRole ?? $roleKey),
            'todayLabel' => now()->toDayDateTimeString(),
            'availability' => $this->availabilityData($user),
            'summaryCards' => $summaryCards,
            'sections' => $sections,
            'roles' => $roles,
            'permissionCount' => $permissionCount,
            'technicianData' => $technicianData,
            'dispatchData' => $dispatchData,
            'adminData' => $adminData,
        ]);
    }

    private function roleKey(?User $user): string
    {
        if (!$user) {
            return 'user';
        }

        if ($user->hasRole(RoleCatalog::ADMIN)) {
            return RoleCatalog::ADMIN;
        }

        if (
            $user->hasAnyRole([
                RoleCatalog::OPERATIONS_MANAGER,
                RoleCatalog::DISPATCH,
                RoleCatalog::INVENTORY_SPECIALIST,
                RoleCatalog::BILLING_SPECIALIST,
            ])
        ) {
            return RoleCatalog::DISPATCH;
        }

        if ($user->hasRole(RoleCatalog::TECHNICIAN)) {
            return RoleCatalog::TECHNICIAN;
        }

        if ($user->hasAnyRole([RoleCatalog::SUPPORT, RoleCatalog::QA_MANAGER])) {
            return RoleCatalog::SUPPORT;
        }

        if ($user->isBusinessCustomer()) {
            return RoleCatalog::CLIENT;
        }

        if ($user->isConsumer()) {
            return RoleCatalog::CONSUMER;
        }

        if ($user->isReadOnly()) {
            return RoleCatalog::GUEST;
        }

        return 'user';
    }

    private function primaryRole(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        foreach ([
            RoleCatalog::ADMIN,
            RoleCatalog::OPERATIONS_MANAGER,
            RoleCatalog::DISPATCH,
            RoleCatalog::TECHNICIAN,
            RoleCatalog::INVENTORY_SPECIALIST,
            RoleCatalog::QA_MANAGER,
            RoleCatalog::BILLING_SPECIALIST,
            RoleCatalog::SUPPORT,
            RoleCatalog::BUSINESS_ADMIN,
            RoleCatalog::BUSINESS_USER,
            RoleCatalog::CLIENT,
            RoleCatalog::CONSUMER,
            RoleCatalog::GUEST,
        ] as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }

    private function availabilityData(?User $user): array
    {
        if (!$user) {
            return ['show' => false];
        }

        $show = $user->canViewSchedule();
        if (!$show) {
            return ['show' => false];
        }

        $status = $user->availability_status ?? 'available';
        $normalizedStatus = match ($status) {
            'offline' => 'off_duty',
            'unavailable' => 'on_break',
            default => $status,
        };

        return [
            'show' => true,
            'status' => $normalizedStatus,
            'label' => $this->availabilityOptions[$normalizedStatus] ?? 'Off Duty',
            'color' => $this->availabilityColors[$normalizedStatus] ?? 'text-gray-500',
            'updated' => $user->availability_updated_at?->diffForHumans() ?? 'just now',
        ];
    }

    private function summaryCardsFor(string $roleKey, User $user, Carbon $today): array
    {
        return match ($roleKey) {
            RoleCatalog::ADMIN => [
                $this->card('Work Orders', WorkOrder::count()),
                $this->card('Open Work Orders', WorkOrder::whereIn('status', $this->openStatuses())->count()),
                $this->card('Organizations', Organization::count()),
                $this->card('Technicians', User::role('technician')->count()),
                $this->card('Revenue This Month', $this->formatCurrency(
                    Invoice::whereNotNull('paid_at')
                        ->where('paid_at', '>=', $today->copy()->startOfMonth())
                        ->sum('total')
                ), 'Paid invoices'),
            ],
            RoleCatalog::DISPATCH => [
                $this->card('Queue', WorkOrder::where('status', 'submitted')->count(), 'Submitted'),
                $this->card('Unassigned', WorkOrder::whereNull('assigned_to_user_id')
                    ->whereIn('status', ['submitted', 'assigned'])
                    ->count()),
                $this->card('Today\'s Appointments', Appointment::whereDate('scheduled_start_at', $today)->count()),
                $this->card('Overdue', WorkOrder::whereIn('status', ['assigned', 'in_progress', 'on_hold'])
                    ->whereNotNull('scheduled_end_at')
                    ->where('scheduled_end_at', '<', now())
                    ->count()),
            ],
            RoleCatalog::TECHNICIAN => [
                $this->card('Assigned Today', Appointment::whereDate('scheduled_start_at', $today)
                    ->where('assigned_to_user_id', $user->id)
                    ->count()),
                $this->card('Open Work Orders', WorkOrder::where('assigned_to_user_id', $user->id)
                    ->whereIn('status', ['assigned', 'in_progress', 'on_hold'])
                    ->count()),
            ],
            RoleCatalog::SUPPORT => [
                $this->card('Open Tickets', SupportTicket::where('status', 'open')->count()),
                $this->card('In Review', SupportTicket::where('status', 'in_review')->count()),
                $this->card('Completed Work Orders', WorkOrder::where('status', 'completed')->count()),
            ],
            RoleCatalog::CONSUMER, RoleCatalog::GUEST => [
                $this->card('My Work Orders', WorkOrder::where('requested_by_user_id', $user->id)->count()),
                $this->card('Open Requests', WorkOrder::where('requested_by_user_id', $user->id)
                    ->whereIn('status', $this->openStatuses())
                    ->count()),
                $this->card('My Equipment', Equipment::where('assigned_user_id', $user->id)->count()),
                $this->card('Open Invoices', Invoice::whereHas('workOrder', function ($builder) use ($user) {
                        $builder->where('requested_by_user_id', $user->id);
                    })
                    ->whereIn('status', ['sent', 'overdue'])
                    ->count()),
            ],
            default => [
                $this->card('Active Work Orders', WorkOrder::where('organization_id', $user->organization_id)
                    ->whereIn('status', $this->openStatuses())
                    ->count()),
                $this->card('Equipment', Equipment::where('organization_id', $user->organization_id)->count()),
                $this->card('Open Invoices', Invoice::where('organization_id', $user->organization_id)
                    ->whereIn('status', ['sent', 'overdue'])
                    ->count()),
            ],
        };
    }

    private function sectionsFor(string $roleKey, User $user, Carbon $today): array
    {
        return match ($roleKey) {
            RoleCatalog::ADMIN => [
                $this->section(
                    'Recent Work Orders',
                    $this->workOrderItems(WorkOrder::latest()->with(['organization', 'assignedTo'])->take(6)->get()),
                    'No recent work orders.',
                    $this->action(route('work-orders.index'))
                ),
                $this->section(
                    'Today\'s Appointments',
                    $this->appointmentItems(Appointment::whereDate('scheduled_start_at', $today)
                        ->with(['workOrder', 'assignedTo'])
                        ->orderBy('scheduled_start_at')
                        ->take(6)
                        ->get()),
                    'No appointments scheduled today.',
                    $this->action(route('schedule.index'))
                ),
                $this->section(
                    'Recent Messages',
                    $this->threadItems(MessageThread::latest()->with('workOrder')->take(5)->get()),
                    'No recent messages.',
                    $this->action(route('messages.index'))
                ),
            ],
            RoleCatalog::DISPATCH => [
                $this->section(
                    'Work Order Queue',
                    $this->workOrderItems(WorkOrder::whereIn('status', ['submitted', 'assigned'])
                        ->with(['organization', 'assignedTo'])
                        ->orderBy('priority')
                        ->latest()
                        ->take(8)
                        ->get()),
                    'No work orders in the queue.',
                    $this->action(route('work-orders.index'))
                ),
                $this->section(
                    'Today\'s Appointments',
                    $this->appointmentItems(Appointment::whereDate('scheduled_start_at', $today)
                        ->with(['workOrder', 'assignedTo'])
                        ->orderBy('scheduled_start_at')
                        ->take(8)
                        ->get()),
                    'No appointments scheduled today.',
                    $this->action(route('schedule.index'))
                ),
            ],
            RoleCatalog::TECHNICIAN => [
                $this->section(
                    'My Appointments',
                    $this->appointmentItems(Appointment::where('assigned_to_user_id', $user->id)
                        ->orderBy('scheduled_start_at')
                        ->take(6)
                        ->get()),
                    'No appointments assigned yet.',
                    $this->action(route('schedule.index'))
                ),
                $this->section(
                    'My Work Orders',
                    $this->workOrderItems(WorkOrder::where('assigned_to_user_id', $user->id)
                        ->latest()
                        ->take(6)
                        ->get()),
                    'No assigned work orders yet.',
                    $this->action(route('work-orders.index'))
                ),
            ],
            RoleCatalog::SUPPORT => [
                $this->section(
                    'Support Tickets',
                    $this->ticketItems(SupportTicket::latest()->take(6)->get()),
                    'No recent tickets.',
                    $this->action(route('support-tickets.index'))
                ),
                $this->section(
                    'Recently Completed Work Orders',
                    $this->workOrderItems(WorkOrder::where('status', 'completed')
                        ->latest()
                        ->take(6)
                        ->get()),
                    'No completed work orders yet.',
                    $this->action(route('work-orders.index'))
                ),
            ],
            RoleCatalog::CONSUMER, RoleCatalog::GUEST => [
                $this->section(
                    'My Work Orders',
                    $this->workOrderItems(WorkOrder::where('requested_by_user_id', $user->id)
                        ->latest()
                        ->take(6)
                        ->get()),
                    'No work orders yet.',
                    $this->action(route('work-orders.index'))
                ),
                $this->section(
                    'My Equipment',
                    $this->equipmentItems(Equipment::where('assigned_user_id', $user->id)
                        ->latest()
                        ->take(6)
                        ->get()),
                    'No equipment recorded yet.',
                    $this->action(route('equipment.index'))
                ),
                $this->section(
                    'Recent Invoices',
                    $this->invoiceItems(Invoice::whereHas('workOrder', function ($builder) use ($user) {
                            $builder->where('requested_by_user_id', $user->id);
                        })
                        ->latest()
                        ->take(6)
                        ->get()),
                    'No invoices yet.',
                    $this->action(route('billing.index'))
                ),
            ],
            default => [
                $this->section(
                    'My Work Orders',
                    $this->workOrderItems(WorkOrder::where('organization_id', $user->organization_id)
                        ->latest()
                        ->take(6)
                        ->get()),
                    'No work orders yet.',
                    $this->action(route('work-orders.index'))
                ),
                $this->section(
                    'My Equipment',
                    $this->equipmentItems(Equipment::where('organization_id', $user->organization_id)
                        ->latest()
                        ->take(6)
                        ->get()),
                    'No equipment recorded yet.',
                    $this->action(route('equipment.index'))
                ),
                $this->section(
                    'Recent Invoices',
                    $this->invoiceItems(Invoice::where('organization_id', $user->organization_id)
                        ->latest()
                        ->take(6)
                        ->get()),
                    'No invoices yet.',
                    $this->action(route('billing.index'))
                ),
                $this->section(
                    'Recent Messages',
                    $this->threadItems(MessageThread::where('organization_id', $user->organization_id)
                        ->latest()
                        ->take(5)
                        ->get()),
                    'No recent messages.',
                    $this->action(route('messages.index'))
                ),
            ],
        };
    }

    public function sendQuickReply(int $threadId): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        if (!$this->threadExistsForUser($threadId, $user->id)) {
            return;
        }

        $body = trim($this->quickReplies[$threadId] ?? '');
        if ($body === '') {
            $this->addError('quickReplies.' . $threadId, 'Message is required.');
            return;
        }

        if (Str::length($body) > 2000) {
            $this->addError('quickReplies.' . $threadId, 'Message is too long.');
            return;
        }

        $thread = MessageThread::find($threadId);

        Message::create([
            'thread_id' => $threadId,
            'user_id' => $user->id,
            'sender_id' => $user->id,
            'subject' => $thread?->subject,
            'body' => $body,
            'timestamp' => now(),
            'message_type' => $thread?->type ?? 'direct',
            'channel' => 'in_app',
            'related_work_order_id' => $thread?->work_order_id,
        ]);

        MessageThread::whereKey($threadId)->update(['updated_at' => now()]);

        MessageThreadParticipant::where('thread_id', $threadId)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        $this->quickReplies[$threadId] = '';
    }

    public function reservePart(int $partId, int $quantity = 1): void
    {
        $user = auth()->user();
        if (!$user || $quantity < 1) {
            return;
        }

        $item = InventoryItem::query()
            ->where('part_id', $partId)
            ->orderByRaw('(quantity - reserved_quantity) desc')
            ->first();

        if (!$item) {
            $this->addError('reservePart', 'No inventory found for this part.');
            return;
        }

        $available = (int) $item->quantity - (int) $item->reserved_quantity;
        if ($available <= 0) {
            $this->addError('reservePart', 'No available stock for this part.');
            return;
        }

        $reserveQty = min($available, $quantity);
        $item->update([
            'reserved_quantity' => $item->reserved_quantity + $reserveQty,
        ]);

        InventoryTransaction::create([
            'part_id' => $partId,
            'location_id' => $item->location_id,
            'user_id' => $user->id,
            'type' => 'reserve',
            'quantity' => $reserveQty,
            'note' => 'Reserved from technician dashboard.',
        ]);
    }

    private function technicianDashboard(User $user, Carbon $today): array
    {
        $appointments = Appointment::query()
            ->whereDate('scheduled_start_at', $today)
            ->where('assigned_to_user_id', $user->id)
            ->with([
                'workOrder.organization.serviceAgreement',
                'workOrder.requestedBy',
                'workOrder.category',
                'workOrder.equipment',
                'workOrder.parts.part',
                'workOrder.attachments',
            ])
            ->orderBy('scheduled_start_at')
            ->get();

        $workOrders = $appointments->map->workOrder->filter();
        $workOrderIds = $workOrders->pluck('id')->unique()->values()->all();

        $currentWorkOrder = WorkOrder::query()
            ->where('assigned_to_user_id', $user->id)
            ->where('status', 'in_progress')
            ->with(['organization', 'category'])
            ->orderByDesc('started_at')
            ->first();

        $timeSummary = $this->technicianTimeSummary($workOrders, $currentWorkOrder);
        $routeStops = $this->buildRouteStops($user, $appointments);
        $messages = $this->recentMessageThreads($user, 6);
        $parts = $this->technicianPartsSnapshot($workOrderIds, $today);

        return [
            'appointments' => $appointments,
            'currentWorkOrder' => $currentWorkOrder,
            'timeSummary' => $timeSummary,
            'routeStops' => $routeStops,
            'messages' => $messages,
            'parts' => $parts,
        ];
    }

    private function dispatchDashboard(User $user, Carbon $today): array
    {
        $queueOrders = WorkOrder::query()
            ->whereIn('status', ['submitted', 'assigned'])
            ->whereNull('assigned_to_user_id')
            ->with(['organization.serviceAgreement', 'category', 'requestedBy'])
            ->get();

        $orgIds = $queueOrders->pluck('organization_id')->filter()->unique()->values()->all();
        $orgRequestCounts = $orgIds
            ? WorkOrder::query()
                ->whereIn('organization_id', $orgIds)
                ->where('created_at', '>=', $today->copy()->subDays(90))
                ->selectRaw('organization_id, COUNT(*) as total')
                ->groupBy('organization_id')
                ->pluck('total', 'organization_id')
                ->toArray()
            : [];

        $queueItems = $queueOrders->map(function (WorkOrder $order) use ($orgRequestCounts) {
            $waitingMinutes = $order->requested_at
                ? $order->requested_at->diffInMinutes(now())
                : ($order->created_at?->diffInMinutes(now()) ?? 0);
            $slaMinutes = $order->organization?->serviceAgreement?->response_time_minutes;
            $historyCount = $order->organization_id ? (int) ($orgRequestCounts[$order->organization_id] ?? 0) : 0;

            return [
                'order' => $order,
                'waiting_minutes' => $waitingMinutes,
                'sla_minutes' => $slaMinutes,
                'history_count' => $historyCount,
                'priority_score' => $this->priorityScore($order, $waitingMinutes, $slaMinutes, $historyCount),
            ];
        })->sortByDesc('priority_score')->values();

        $technicians = User::role('technician')->orderBy('name')->get();
        $technicianIds = $technicians->pluck('id')->all();
        $appointments = Appointment::query()
            ->whereDate('scheduled_start_at', $today)
            ->whereIn('assigned_to_user_id', $technicianIds)
            ->with(['workOrder', 'assignedTo'])
            ->orderBy('scheduled_start_at')
            ->get();
        $appointmentsByTech = $appointments->groupBy('assigned_to_user_id');

        $activeOrders = WorkOrder::query()
            ->whereIn('assigned_to_user_id', $technicianIds)
            ->whereIn('status', ['assigned', 'in_progress', 'on_hold'])
            ->with('appointments')
            ->get()
            ->groupBy('assigned_to_user_id');

        $recentCompleted = WorkOrder::query()
            ->whereIn('assigned_to_user_id', $technicianIds)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $today->copy()->subDays(30))
            ->get(['assigned_to_user_id', 'estimated_minutes', 'labor_minutes', 'started_at', 'completed_at']);

        $durationStats = $recentCompleted->groupBy('assigned_to_user_id')->map(function ($orders) {
            $actual = [];
            $estimated = [];

            foreach ($orders as $order) {
                if ($order->labor_minutes !== null) {
                    $actual[] = (int) $order->labor_minutes;
                } elseif ($order->started_at && $order->completed_at) {
                    $actual[] = $order->started_at->diffInMinutes($order->completed_at);
                }

                if ($order->estimated_minutes !== null) {
                    $estimated[] = (int) $order->estimated_minutes;
                }
            }

            return [
                'avg_actual' => $actual === [] ? null : (int) round(array_sum($actual) / count($actual)),
                'avg_estimated' => $estimated === [] ? null : (int) round(array_sum($estimated) / count($estimated)),
            ];
        });

        $overdueAppointments = $appointments->filter(function (Appointment $appointment) {
            if (!$appointment->scheduled_end_at) {
                return false;
            }

            $status = $appointment->workOrder?->status;
            return $appointment->scheduled_end_at->isPast() && !in_array($status, ['completed', 'closed', 'canceled'], true);
        });

        $technicianCards = $technicians->map(function (User $tech) use ($appointmentsByTech, $activeOrders, $durationStats, $overdueAppointments) {
            $todayAppointments = $appointmentsByTech->get($tech->id, collect());
            $activeForTech = $activeOrders->get($tech->id, collect());
            $hasOverdue = $overdueAppointments->contains(fn(Appointment $appointment) => $appointment->assigned_to_user_id === $tech->id);

            $status = $this->technicianStatus($tech, $activeForTech, $todayAppointments, $hasOverdue);
            $scheduledMinutes = $todayAppointments->sum(function (Appointment $appointment) {
                if ($appointment->scheduled_start_at && $appointment->scheduled_end_at) {
                    return $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at);
                }

                return $appointment->workOrder?->estimated_minutes ?? 0;
            });

            $utilization = $scheduledMinutes > 0 ? min(100, (int) round($scheduledMinutes / 480 * 100)) : 0;
            $duration = $durationStats->get($tech->id, ['avg_actual' => null, 'avg_estimated' => null]);
            $mapUrl = $tech->current_latitude && $tech->current_longitude
                ? $this->mapPointUrl((float) $tech->current_latitude, (float) $tech->current_longitude)
                : null;

            return [
                'user' => $tech,
                'status' => $status,
                'appointments' => $todayAppointments,
                'utilization' => $utilization,
                'avg_actual' => $duration['avg_actual'],
                'avg_estimated' => $duration['avg_estimated'],
                'scheduled_minutes' => $scheduledMinutes,
                'has_overdue' => $hasOverdue,
                'map_url' => $mapUrl,
            ];
        });

        $metrics = $this->dispatchMetrics($today, $technicianCards);
        $heatMap = $this->dispatchHeatMap($queueOrders);
        $alerts = $this->dispatchAlerts($queueItems, $overdueAppointments);

        return [
            'queue' => $queueItems,
            'technicians' => $technicianCards,
            'metrics' => $metrics,
            'heatMap' => $heatMap,
            'appointments' => $appointments,
            'alerts' => $alerts,
        ];
    }

    private function adminDashboard(Carbon $today): array
    {
        $systemHealth = [
            'uptime' => $this->systemUptime(),
            'db_ms' => $this->databaseResponseMs(),
            'storage' => $this->storageUsage(),
            'queue_backlog' => $this->tableCount('jobs'),
            'failed_jobs' => $this->tableCount('failed_jobs'),
            'sessions' => $this->tableCount('sessions'),
        ];

        $roleCounts = collect(RoleCatalog::all())
            ->mapWithKeys(fn(string $role) => [$role => User::role($role)->count()])
            ->toArray();

        $recentUsers = User::orderByDesc('created_at')->take(6)->get();
        $inactiveUsers = User::where('is_active', false)->count();
        $staleUsers = User::where(function ($query) use ($today) {
            $query->whereNull('last_seen_at')
                ->orWhere('last_seen_at', '<', $today->copy()->subDays(90));
        })->count();

        $passwordResets = Schema::hasTable('password_reset_tokens')
            ? DB::table('password_reset_tokens')->where('created_at', '>=', $today->copy()->subDays(7))->count()
            : 0;

        $mfaEnabled = User::where('mfa_enabled', true)->count();

        $business = [
            'revenue_month' => Invoice::whereNotNull('paid_at')
                ->where('paid_at', '>=', $today->copy()->startOfMonth())
                ->sum('total'),
            'jobs_week' => WorkOrder::where('created_at', '>=', $today->copy()->subDays(7))->count(),
            'jobs_week_prior' => WorkOrder::whereBetween('created_at', [
                $today->copy()->subDays(14),
                $today->copy()->subDays(7),
            ])->count(),
            'new_customers' => Organization::where('created_at', '>=', $today->copy()->startOfMonth())->count(),
            'inactive_customers' => Organization::whereIn('status', ['inactive', 'canceled'])->count(),
            'avg_revenue_per_job' => Invoice::avg('total'),
        ];

        $backupAt = SystemSetting::where('group', 'backup')
            ->where('key', 'last_run_at')
            ->value('value');
        if (is_array($backupAt)) {
            $backupAt = $backupAt['value'] ?? null;
        }

        $auditCount = Schema::hasTable('audit_logs')
            ? DB::table('audit_logs')->where('created_at', '>=', $today->copy()->subDay())->count()
            : 0;

        $compliance = [
            'backup_last_run' => $backupAt,
            'audit_events' => $auditCount,
            'mfa_coverage' => $mfaEnabled,
            'user_count' => User::count(),
        ];

        $quickLinks = [
            ['label' => 'User Administration', 'route' => route('settings.index')],
            ['label' => 'System Reports', 'route' => route('reports.index')],
            ['label' => 'Support Escalations', 'route' => route('support-tickets.index')],
            ['label' => 'Inventory Overview', 'route' => route('inventory.index')],
            ['label' => 'Audit Logs', 'route' => route('reports.index')],
        ];

        return [
            'systemHealth' => $systemHealth,
            'roleCounts' => $roleCounts,
            'recentUsers' => $recentUsers,
            'inactiveUsers' => $inactiveUsers,
            'staleUsers' => $staleUsers,
            'passwordResets' => $passwordResets,
            'mfaEnabled' => $mfaEnabled,
            'business' => $business,
            'compliance' => $compliance,
            'quickLinks' => $quickLinks,
        ];
    }

    private function openStatuses(): array
    {
        return ['submitted', 'assigned', 'in_progress', 'on_hold'];
    }

    private function card(string $label, mixed $value, ?string $subtext = null): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'subtext' => $subtext,
        ];
    }

    private function section(string $title, array $items, string $empty, ?array $action = null): array
    {
        return [
            'title' => $title,
            'items' => $items,
            'empty' => $empty,
            'action' => $action,
        ];
    }

    private function action(string $href): array
    {
        return [
            'label' => 'View all',
            'href' => $href,
        ];
    }

    private function item(string $title, string $meta, ?string $href = null, array $badges = []): array
    {
        return [
            'title' => $title,
            'meta' => $meta,
            'href' => $href,
            'badges' => $badges,
        ];
    }

    private function badge(string $label, string $class): array
    {
        return [
            'label' => $label,
            'class' => $class,
        ];
    }

    private function workOrderItems($orders): array
    {
        return $orders->map(function (WorkOrder $order) {
            $metaParts = [];
            if ($order->organization?->name) {
                $metaParts[] = $order->organization->name;
            }
            if ($order->assignedTo?->name) {
                $metaParts[] = 'Assigned: ' . $order->assignedTo->name;
            }
            if ($order->scheduled_start_at) {
                $metaParts[] = 'Scheduled: ' . $order->scheduled_start_at->format('M d, H:i');
            }

            $meta = $metaParts === [] ? 'No additional details.' : implode(' • ', $metaParts);

            return $this->item(
                '#' . $order->id . ' ' . $order->subject,
                $meta,
                route('work-orders.show', $order),
                [
                    $this->badge($this->labelize($order->status), $this->statusBadgeClass($order->status)),
                    $this->badge(ucfirst($order->priority), $this->priorityBadgeClass($order->priority)),
                ]
            );
        })->all();
    }

    private function appointmentItems($appointments): array
    {
        return $appointments->map(function (Appointment $appointment) {
            $title = $appointment->workOrder?->subject ?? 'Work Order';
            $metaParts = [];
            if ($appointment->scheduled_start_at) {
                $metaParts[] = $appointment->scheduled_start_at->format('M d, H:i');
            }
            if ($appointment->assignedTo?->name) {
                $metaParts[] = $appointment->assignedTo->name;
            }
            if ($appointment->time_window) {
                $metaParts[] = $appointment->time_window;
            }
            $meta = $metaParts === [] ? 'No scheduled time yet.' : implode(' • ', $metaParts);
            $href = $appointment->workOrder ? route('work-orders.show', $appointment->workOrder) : null;

            return $this->item($title, $meta, $href);
        })->all();
    }

    private function ticketItems($tickets): array
    {
        return $tickets->map(function (SupportTicket $ticket) {
            $meta = $this->labelize($ticket->status) . ' • ' . ucfirst($ticket->priority);

            return $this->item($ticket->subject, $meta, route('support-tickets.index'), [
                $this->badge($this->labelize($ticket->status), $this->statusBadgeClass($ticket->status)),
            ]);
        })->all();
    }

    private function equipmentItems($equipment): array
    {
        return $equipment->map(function (Equipment $item) {
            $metaParts = [
                $item->type,
                $this->labelize($item->status),
            ];

            if ($item->location_name) {
                $metaParts[] = $item->location_name;
            }

            return $this->item($item->name, implode(' • ', $metaParts), route('equipment.index'), [
                $this->badge($this->labelize($item->status), $this->statusBadgeClass($item->status)),
            ]);
        })->all();
    }

    private function invoiceItems($invoices): array
    {
        return $invoices->map(function (Invoice $invoice) {
            $meta = $this->labelize($invoice->status) . ' • ' . $this->formatCurrency($invoice->total);

            return $this->item('Invoice #' . $invoice->id, $meta, route('billing.index'), [
                $this->badge($this->labelize($invoice->status), $this->statusBadgeClass($invoice->status)),
            ]);
        })->all();
    }

    private function threadItems($threads): array
    {
        return $threads->map(function (MessageThread $thread) {
            $title = $thread->subject ?: 'Conversation';
            $metaParts = ['Updated ' . $thread->updated_at?->diffForHumans()];
            if ($thread->work_order_id) {
                $metaParts[] = 'WO #' . $thread->work_order_id;
            }

            return $this->item($title, implode(' • ', $metaParts), route('messages.index'));
        })->all();
    }

    private function labelize(?string $value): string
    {
        if (!$value) {
            return 'Unknown';
        }

        return Str::title(str_replace('_', ' ', $value));
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'submitted' => 'bg-gray-100 text-gray-700',
            'assigned' => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-indigo-100 text-indigo-700',
            'on_hold' => 'bg-yellow-100 text-yellow-700',
            'completed', 'closed' => 'bg-green-100 text-green-700',
            'canceled' => 'bg-red-100 text-red-700',
            'urgent' => 'bg-red-100 text-red-700',
            'high' => 'bg-orange-100 text-orange-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    private function priorityBadgeClass(string $priority): string
    {
        return match ($priority) {
            'urgent' => 'bg-red-100 text-red-700',
            'high' => 'bg-orange-100 text-orange-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    private function formatCurrency(mixed $value): string
    {
        return '$' . number_format((float) $value, 2);
    }

    private function technicianTimeSummary($workOrders, ?WorkOrder $currentWorkOrder): array
    {
        $laborMinutes = 0;
        $travelMinutes = 0;

        foreach ($workOrders as $order) {
            $travelMinutes += (int) ($order->travel_minutes ?? 0);

            if ($order->labor_minutes !== null) {
                $laborMinutes += (int) $order->labor_minutes;
                continue;
            }

            if ($order->started_at) {
                $end = $order->completed_at ?? now();
                $laborMinutes += $order->started_at->diffInMinutes($end);
            }
        }

        $current = null;
        if ($currentWorkOrder && $currentWorkOrder->started_at) {
            $current = [
                'id' => $currentWorkOrder->id,
                'subject' => $currentWorkOrder->subject,
                'started_at' => $currentWorkOrder->started_at,
                'elapsed_minutes' => $currentWorkOrder->started_at->diffInMinutes(now()),
                'estimated_minutes' => $currentWorkOrder->estimated_minutes,
            ];
        }

        return [
            'labor_minutes' => $laborMinutes,
            'travel_minutes' => $travelMinutes,
            'billable_minutes' => $laborMinutes,
            'break_minutes' => null,
            'current' => $current,
        ];
    }


    private function buildRouteStops(User $user, $appointments): array
    {
        $originLat = $user->current_latitude;
        $originLng = $user->current_longitude;

        return $appointments->values()->map(function (Appointment $appointment, int $index) use ($originLat, $originLng) {
            $order = $appointment->workOrder;
            $lat = $order?->location_latitude;
            $lng = $order?->location_longitude;
            $hasCoords = $lat !== null && $lng !== null && $originLat !== null && $originLng !== null;

            return [
                'sequence' => $index + 1,
                'label' => $order?->location_name ?: ($order?->organization?->name ?? 'Service stop'),
                'address' => $order?->location_address,
                'time' => $appointment->scheduled_start_at?->format('H:i'),
                'travel_minutes' => $order?->travel_minutes,
                'map_url' => $hasCoords ? $this->mapRouteUrl($originLat, $originLng, $lat, $lng) : null,
                'has_coords' => $hasCoords,
                'lat' => $lat,
                'lng' => $lng,
                'priority' => $order?->priority ?? 'standard',
            ];
        })->all();
    }


    private function recentMessageThreads(User $user, int $limit): array
    {
        $threads = MessageThread::query()
            ->whereHas('participants', function ($builder) use ($user) {
                $builder->where('user_id', $user->id);
            })
            ->with([
                'participants',
                'workOrder',
                'messages' => function ($builder) {
                    $builder->latest()->limit(1)->with('user');
                },
            ])
            ->orderByDesc('updated_at')
            ->take($limit)
            ->get();

        $threads->each(function (MessageThread $thread) use ($user) {
            $thread->setAttribute('is_unread', $this->threadIsUnread($thread, $user->id));
        });

        return $threads->all();
    }

    private function technicianPartsSnapshot(array $workOrderIds, Carbon $today): array
    {
        $neededRows = collect();
        $neededPartIds = [];

        if ($workOrderIds !== []) {
            $parts = WorkOrderPart::query()
                ->whereIn('work_order_id', $workOrderIds)
                ->with('part')
                ->get();

            $neededRows = $parts->groupBy('part_id')->map(function ($rows) {
                $part = $rows->first()->part;

                return [
                    'part_id' => $part?->id,
                    'name' => $part?->name ?? 'Part',
                    'sku' => $part?->sku,
                    'quantity' => $rows->sum('quantity'),
                ];
            })->values();

            $neededPartIds = $parts->pluck('part_id')->unique()->values()->all();
        }

        $common = WorkOrderPart::query()
            ->selectRaw('part_id, SUM(quantity) as total_qty')
            ->whereHas('workOrder', function ($builder) use ($today) {
                $builder->where('created_at', '>=', $today->copy()->subDays(30));
            })
            ->groupBy('part_id')
            ->orderByDesc('total_qty')
            ->limit(6)
            ->get();

        $commonPartIds = $common->pluck('part_id')->unique()->values()->all();
        $allPartIds = array_values(array_unique(array_merge($neededPartIds, $commonPartIds)));

        $partsById = $allPartIds === []
            ? collect()
            : Part::query()->whereIn('id', $allPartIds)->get()->keyBy('id');

        $inventory = $allPartIds === []
            ? collect()
            : InventoryItem::query()
                ->whereIn('part_id', $allPartIds)
                ->selectRaw('part_id, SUM(quantity) as quantity, SUM(reserved_quantity) as reserved')
                ->groupBy('part_id')
                ->get()
                ->keyBy('part_id');

        $commonParts = $common->map(function ($row) use ($inventory, $partsById) {
            $part = $partsById->get($row->part_id);
            $stock = $inventory->get($row->part_id);
            $available = $stock ? (int) $stock->quantity - (int) $stock->reserved : 0;

            return [
                'part_id' => $row->part_id,
                'name' => $part?->name ?? 'Part',
                'sku' => $part?->sku,
                'usage' => (int) $row->total_qty,
                'available' => $available,
                'reorder_level' => $part?->reorder_level,
            ];
        })->all();

        $neededParts = $neededRows->map(function ($row) use ($inventory) {
            $stock = $inventory->get($row['part_id']);
            $available = $stock ? (int) $stock->quantity - (int) $stock->reserved : 0;
            $row['available'] = $available;
            return $row;
        })->all();

        return [
            'needed' => $neededParts,
            'common' => $commonParts,
        ];
    }

    private function priorityScore(WorkOrder $order, int $waitingMinutes, ?int $slaMinutes, int $historyCount): int
    {
        $score = match ($order->priority) {
            'urgent' => 90,
            'high' => 70,
            default => 50,
        };

        $score += min(40, (int) round($waitingMinutes / 5));

        if ($slaMinutes) {
            if ($waitingMinutes >= $slaMinutes) {
                $score += 30;
            } elseif ($waitingMinutes >= (int) ($slaMinutes * 0.75)) {
                $score += 15;
            }
        }

        $score += min(20, $historyCount);

        return $score;
    }

    private function technicianStatus(User $tech, $activeOrders, $appointments, bool $hasOverdue): array
    {
        if ($tech->availability_status === 'offline') {
            return ['label' => 'Off duty', 'class' => 'bg-gray-100 text-gray-700'];
        }

        if ($hasOverdue) {
            return ['label' => 'Overdue', 'class' => 'bg-red-100 text-red-700'];
        }

        $working = $activeOrders->firstWhere('status', 'in_progress');
        if ($working) {
            return ['label' => 'Working', 'class' => 'bg-orange-100 text-orange-700'];
        }

        if ($appointments->isNotEmpty()) {
            $next = $appointments->first();
            if ($next?->scheduled_start_at && $next->scheduled_start_at->isFuture()) {
                return ['label' => 'Traveling', 'class' => 'bg-blue-100 text-blue-700'];
            }

            return ['label' => 'Scheduled', 'class' => 'bg-indigo-100 text-indigo-700'];
        }

        return ['label' => 'Available', 'class' => 'bg-green-100 text-green-700'];
    }

    private function dispatchMetrics(Carbon $today, $technicianCards): array
    {
        $completedToday = WorkOrder::whereIn('status', ['completed', 'closed'])
            ->where('completed_at', '>=', $today)
            ->count();

        $inProgress = WorkOrder::where('status', 'in_progress')->count();

        $avgResponse = WorkOrder::query()
            ->whereNotNull('assigned_at')
            ->where('assigned_at', '>=', $today->copy()->subDays(7))
            ->get(['requested_at', 'created_at', 'assigned_at'])
            ->map(function (WorkOrder $order) {
                $requested = $order->requested_at ?? $order->created_at;
                return $requested ? $requested->diffInMinutes($order->assigned_at) : null;
            })
            ->filter()
            ->avg();

        $avgCompletion = WorkOrder::query()
            ->whereNotNull('assigned_at')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $today->copy()->subDays(7))
            ->get(['assigned_at', 'completed_at'])
            ->map(fn(WorkOrder $order) => $order->assigned_at->diffInMinutes($order->completed_at))
            ->filter()
            ->avg();

        $satisfaction = WorkOrderFeedback::where('created_at', '>=', $today->copy()->subDays(7))
            ->avg('rating');

        $utilization = $technicianCards->isNotEmpty()
            ? (int) round($technicianCards->avg('utilization'))
            : 0;

        return [
            'completed_today' => $completedToday,
            'in_progress' => $inProgress,
            'avg_response_minutes' => $avgResponse ? (int) round($avgResponse) : null,
            'avg_completion_minutes' => $avgCompletion ? (int) round($avgCompletion) : null,
            'satisfaction' => $satisfaction ? round($satisfaction, 1) : null,
            'utilization' => $utilization,
        ];
    }

    private function dispatchHeatMap($queueOrders): array
    {
        return $queueOrders
            ->groupBy(fn(WorkOrder $order) => $order->location_name ?: ($order->organization?->name ?? 'Unknown'))
            ->map(fn($orders, $label) => ['label' => $label, 'count' => $orders->count()])
            ->sortByDesc('count')
            ->values()
            ->take(6)
            ->all();
    }

    private function dispatchAlerts($queueItems, $overdueAppointments): array
    {
        $alerts = [];

        $urgent = $queueItems->filter(fn($item) => $item['order']->priority === 'urgent');
        if ($urgent->isNotEmpty()) {
            $alerts[] = [
                'label' => 'Urgent requests in queue',
                'detail' => $urgent->count() . ' urgent requests awaiting assignment.',
                'severity' => 'high',
            ];
        }

        if ($overdueAppointments->isNotEmpty()) {
            $alerts[] = [
                'label' => 'Overdue appointments',
                'detail' => $overdueAppointments->count() . ' appointments are running past the scheduled end time.',
                'severity' => 'high',
            ];
        }

        $slaBreaches = $queueItems->filter(function ($item) {
            if (!$item['sla_minutes']) {
                return false;
            }

            return $item['waiting_minutes'] >= $item['sla_minutes'];
        });

        if ($slaBreaches->isNotEmpty()) {
            $alerts[] = [
                'label' => 'SLA response windows exceeded',
                'detail' => $slaBreaches->count() . ' requests have exceeded SLA response time.',
                'severity' => 'high',
            ];
        }

        $nearBreaches = $queueItems->filter(function ($item) {
            if (!$item['sla_minutes']) {
                return false;
            }

            return $item['waiting_minutes'] >= (int) ($item['sla_minutes'] * 0.75);
        });

        if ($nearBreaches->isNotEmpty()) {
            $alerts[] = [
                'label' => 'SLA risk alerts',
                'detail' => $nearBreaches->count() . ' requests are approaching SLA deadlines.',
                'severity' => 'medium',
            ];
        }

        $openComplaints = SupportTicket::whereIn('status', ['open', 'in_review'])
            ->where('priority', 'high')
            ->count();

        if ($openComplaints > 0) {
            $alerts[] = [
                'label' => 'Customer complaints',
                'detail' => $openComplaints . ' high-priority support tickets need review.',
                'severity' => 'medium',
            ];
        }

        if ($alerts === []) {
            $alerts[] = [
                'label' => 'All clear',
                'detail' => 'No exceptions detected in the last update window.',
                'severity' => 'low',
            ];
        }

        return $alerts;
    }

    private function mapRouteUrl(float $originLat, float $originLng, float $destLat, float $destLng): string
    {
        $origin = $originLat . ',' . $originLng;
        $destination = $destLat . ',' . $destLng;

        return 'https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=' . $origin . ';' . $destination;
    }

    private function mapPointUrl(float $lat, float $lng): string
    {
        return 'https://www.openstreetmap.org/?mlat=' . $lat . '&mlon=' . $lng . '#map=14/' . $lat . '/' . $lng;
    }

    private function systemUptime(): string
    {
        $startedAt = Cache::rememberForever('system_started_at', fn() => now()->toDateTimeString());
        return Carbon::parse($startedAt)->diffForHumans();
    }

    private function databaseResponseMs(): ?int
    {
        try {
            $start = microtime(true);
            DB::select('select 1');
            $elapsed = (microtime(true) - $start) * 1000;
            return (int) round($elapsed);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function storageUsage(): array
    {
        $path = storage_path();
        $total = @disk_total_space($path);
        $free = @disk_free_space($path);

        if (!$total || !$free) {
            return [
                'total' => null,
                'used' => null,
                'free' => null,
                'percent' => null,
            ];
        }

        $used = $total - $free;
        $percent = (int) round(($used / $total) * 100);

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percent' => $percent,
        ];
    }

    private function tableCount(string $table): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return (int) DB::table($table)->count();
    }

    private function threadExistsForUser(int $threadId, int $userId): bool
    {
        return MessageThread::whereKey($threadId)
            ->whereHas('participants', function ($builder) use ($userId) {
                $builder->where('user_id', $userId);
            })
            ->exists();
    }

    private function threadIsUnread(MessageThread $thread, int $userId): bool
    {
        $participant = $thread->participants->firstWhere('user_id', $userId);
        $lastMessage = $thread->messages->first();

        if (!$lastMessage) {
            return false;
        }

        if (!$participant?->last_read_at) {
            return true;
        }

        return $participant->last_read_at->lt($lastMessage->created_at);
    }
}
