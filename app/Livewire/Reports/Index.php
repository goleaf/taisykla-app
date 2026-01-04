<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use App\Models\Report;
use App\Models\ReportDashboard;
use App\Models\ReportDashboardWidget;
use App\Models\ReportExport;
use App\Models\ReportPermission;
use App\Models\ReportSchedule;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderFeedback;
use App\Services\ReportService;
use App\Services\AuditLogger;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Livewire\Component;

class Index extends Component
{
    public bool $showCreate = false;
    public bool $showPreview = false;
    public array $preview = [];
    public string $previewTitle = '';
    public array $newReport = [];
    public array $newSchedule = [];
    public ?int $scheduleReportId = null;
    public string $activeTab = 'overview';
    public array $newDashboard = [];
    public array $newWidget = [];
    public ?int $activeDashboardId = null;
    public array $dashboardData = [];
    public array $analytics = [];
    public array $permissionForm = [];

    public array $reportTypes = [
        'daily_summary' => 'Daily Work Summary',
        'weekly_productivity' => 'Weekly Productivity',
        'monthly_performance' => 'Monthly Performance',
        'technician_performance' => 'Technician Performance',
        'customer_satisfaction' => 'Customer Satisfaction',
        'customer_activity' => 'Customer Activity',
        'revenue' => 'Revenue',
        'cost_analysis' => 'Cost Analysis',
        'profitability' => 'Profitability',
        'accounts_receivable_aging' => 'Accounts Receivable Aging',
        'invoice_history' => 'Invoice History',
        'technician_utilization' => 'Technician Utilization',
        'first_time_fix' => 'First-Time Fix Rate',
        'response_time' => 'Response Time',
        'schedule_adherence' => 'Schedule Adherence',
        'sla_compliance' => 'SLA Compliance',
        'equipment_reliability' => 'Equipment Reliability',
        'maintenance_frequency' => 'Maintenance Frequency',
        'parts_usage' => 'Parts Usage',
        'lifecycle_analysis' => 'Lifecycle Analysis',
        'predictive_analytics' => 'Predictive Analytics',
        'custom' => 'Custom Report',
    ];

    public array $reportCategories = [
        'operational' => [
            'daily_summary',
            'weekly_productivity',
            'monthly_performance',
            'technician_performance',
            'technician_utilization',
            'first_time_fix',
            'response_time',
            'schedule_adherence',
        ],
        'financial' => [
            'revenue',
            'cost_analysis',
            'profitability',
            'accounts_receivable_aging',
            'invoice_history',
        ],
        'customer' => [
            'customer_satisfaction',
            'customer_activity',
            'sla_compliance',
        ],
        'equipment' => [
            'equipment_reliability',
            'maintenance_frequency',
            'parts_usage',
            'lifecycle_analysis',
        ],
        'predictive' => [
            'predictive_analytics',
        ],
    ];

    public array $dataSources = [
        'work_orders' => 'Work Orders',
        'invoices' => 'Invoices',
        'equipment' => 'Equipment',
        'parts' => 'Parts',
        'organizations' => 'Organizations',
        'payments' => 'Payments',
        'inventory_items' => 'Inventory Items',
        'technicians' => 'Technicians',
    ];

    public array $visualizationOptions = [
        'table' => 'Table',
        'bar' => 'Bar Chart',
        'line' => 'Line Chart',
        'pie' => 'Pie Chart',
        'area' => 'Area Chart',
        'scatter' => 'Scatter Plot',
        'heat' => 'Heat Map',
        'gauge' => 'Gauge',
    ];

    public array $dashboardTypes = [
        'executive' => 'Executive',
        'operations' => 'Operations',
        'technician' => 'Technician',
        'customer_success' => 'Customer Success',
        'financial' => 'Financial',
    ];

    protected $queryString = ['activeTab'];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::REPORTS_VIEW), 403);

        $this->resetNewReport();
        $this->resetNewSchedule();
        $this->resetNewDashboard();
        $this->resetNewWidget();
        $this->resetPermissionForm();
    }

    public function resetNewReport(): void
    {
        $this->newReport = [
            'name' => '',
            'category' => 'operational',
            'report_type' => 'daily_summary',
            'data_source' => 'work_orders',
            'description' => '',
            'visualization' => 'table',
            'fields' => '',
            'filters' => '',
            'group_by' => '',
            'sort_by' => '',
            'calculated_fields' => '',
            'compare' => '',
            'share_roles' => '',
            'allowed_fields' => '',
            'share_can_edit' => false,
            'share_can_share' => false,
            'is_public' => false,
        ];
    }

    public function resetNewSchedule(): void
    {
        $this->newSchedule = [
            'frequency' => 'weekly',
            'day_of_week' => 1,
            'day_of_month' => 1,
            'time_of_day' => '08:00',
            'format' => 'csv',
            'timezone' => '',
            'recipients' => '',
            'delivery_channels' => '',
            'parameters' => '',
            'conditions' => '',
            'filters' => '',
            'is_active' => true,
        ];
    }

    public function resetNewDashboard(): void
    {
        $this->newDashboard = [
            'name' => '',
            'dashboard_type' => 'operations',
            'description' => '',
            'is_default' => false,
            'is_public' => false,
        ];
    }

    public function resetNewWidget(): void
    {
        $this->newWidget = [
            'title' => '',
            'widget_type' => 'kpi',
            'report_id' => null,
            'data_source' => '',
            'config' => '',
        ];
    }

    public function resetPermissionForm(): void
    {
        $this->permissionForm = [
            'report_id' => null,
            'role' => '',
            'allowed_fields' => '',
            'can_view' => true,
            'can_edit' => false,
            'can_share' => false,
        ];
    }

    public function updatedActiveTab(): void
    {
        if ($this->activeTab === 'builder') {
            $this->showCreate = true;
        }

        if ($this->activeTab === 'analytics') {
            $this->loadAnalytics();
        }
    }

    public function createReport(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::REPORTS_MANAGE)) {
            return;
        }

        $this->validate([
            'newReport.name' => ['required', 'string', 'max:255'],
            'newReport.report_type' => ['required', 'string', 'max:50'],
            'newReport.data_source' => ['nullable', 'string', 'max:50'],
            'newReport.category' => ['required', 'string', 'max:50'],
            'newReport.visualization' => ['required', 'string', 'max:50'],
        ]);

        $filters = $this->decodeJsonField($this->newReport['filters'] ?? '');
        $groupBy = $this->parseListField($this->newReport['group_by'] ?? '');
        $sortBy = $this->parseSortField($this->newReport['sort_by'] ?? '');
        $fields = $this->parseListField($this->newReport['fields'] ?? '');
        $calculatedFields = $this->decodeJsonField($this->newReport['calculated_fields'] ?? '') ?? [];
        $compare = $this->decodeJsonField($this->newReport['compare'] ?? '') ?? null;
        $shareRoles = $this->parseListField($this->newReport['share_roles'] ?? '');
        $allowedFields = $this->parseListField($this->newReport['allowed_fields'] ?? '');

        $report = Report::create([
            'name' => $this->newReport['name'],
            'report_type' => $this->newReport['report_type'],
            'category' => $this->newReport['category'],
            'data_source' => $this->newReport['report_type'] === 'custom' ? $this->newReport['data_source'] : null,
            'description' => $this->newReport['description'],
            'visualization' => $this->newReport['visualization'],
            'definition' => [
                'fields' => $fields,
                'calculated_fields' => $calculatedFields,
            ],
            'filters' => $filters,
            'group_by' => $groupBy,
            'sort_by' => $sortBy,
            'compare' => $compare,
            'is_public' => (bool) $this->newReport['is_public'],
            'created_by_user_id' => auth()->id(),
        ]);

        if ($report) {
            app(AuditLogger::class)->log(
                'report.created',
                $report,
                'Report created.',
                ['report_type' => $report->report_type]
            );

            foreach ($shareRoles as $role) {
                ReportPermission::create([
                    'report_id' => $report->id,
                    'role' => $role,
                    'can_view' => true,
                    'can_edit' => (bool) $this->newReport['share_can_edit'],
                    'can_share' => (bool) $this->newReport['share_can_share'],
                    'allowed_fields' => $allowedFields ?: null,
                ]);
            }
        }

        session()->flash('status', 'Report created.');
        $this->resetNewReport();
        $this->showCreate = false;
    }

    public function previewReport(int $reportId): void
    {
        $report = Report::findOrFail($reportId);
        $service = app(ReportService::class);
        $payload = $service->generateForReport($report, [], auth()->user());

        $this->preview = $payload;
        $this->previewTitle = $report->name;
        $this->showPreview = true;
    }

    public function previewTemplate(string $reportType): void
    {
        $service = app(ReportService::class);
        $payload = $service->generateByType($reportType, [], auth()->user());

        $this->preview = $payload;
        $this->previewTitle = $this->reportTypes[$reportType] ?? 'Report Preview';
        $this->showPreview = true;
    }

    public function clearPreview(): void
    {
        $this->preview = [];
        $this->previewTitle = '';
        $this->showPreview = false;
    }

    public function startSchedule(int $reportId): void
    {
        if (! auth()->user()?->can(PermissionCatalog::REPORTS_MANAGE)) {
            return;
        }

        $this->scheduleReportId = $reportId;
        $this->resetNewSchedule();
    }

    public function createSchedule(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::REPORTS_MANAGE)) {
            return;
        }

        if (! $this->scheduleReportId) {
            return;
        }

        $this->validate([
            'newSchedule.frequency' => ['required', 'string', 'max:20'],
            'newSchedule.time_of_day' => ['nullable', 'string', 'max:10'],
            'newSchedule.format' => ['required', 'string', 'max:10'],
            'newSchedule.timezone' => ['nullable', 'string', 'max:50'],
        ]);

        ReportSchedule::create([
            'report_id' => $this->scheduleReportId,
            'frequency' => $this->newSchedule['frequency'],
            'day_of_week' => $this->newSchedule['frequency'] === 'weekly' ? $this->newSchedule['day_of_week'] : null,
            'day_of_month' => $this->newSchedule['frequency'] === 'monthly' ? $this->newSchedule['day_of_month'] : null,
            'time_of_day' => $this->newSchedule['time_of_day'],
            'format' => $this->newSchedule['format'],
            'timezone' => $this->newSchedule['timezone'] ?: null,
            'recipients' => $this->parseListField($this->newSchedule['recipients']),
            'delivery_channels' => $this->parseListField($this->newSchedule['delivery_channels'] ?? ''),
            'parameters' => $this->decodeJsonField($this->newSchedule['parameters'] ?? ''),
            'conditions' => $this->decodeJsonField($this->newSchedule['conditions'] ?? ''),
            'filters' => $this->decodeJsonField($this->newSchedule['filters'] ?? ''),
            'is_active' => (bool) $this->newSchedule['is_active'],
            'next_run_at' => now(),
        ]);

        $this->scheduleReportId = null;
        $this->resetNewSchedule();
        session()->flash('status', 'Report schedule saved.');
    }

    public function createDashboard(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::REPORTS_MANAGE)) {
            return;
        }

        $this->validate([
            'newDashboard.name' => ['required', 'string', 'max:255'],
            'newDashboard.dashboard_type' => ['required', 'string', 'max:50'],
        ]);

        ReportDashboard::create([
            'name' => $this->newDashboard['name'],
            'dashboard_type' => $this->newDashboard['dashboard_type'],
            'description' => $this->newDashboard['description'],
            'is_default' => (bool) $this->newDashboard['is_default'],
            'is_public' => (bool) $this->newDashboard['is_public'],
            'created_by_user_id' => auth()->id(),
        ]);

        $this->resetNewDashboard();
        session()->flash('status', 'Dashboard created.');
    }

    public function selectDashboard(int $dashboardId): void
    {
        $this->activeDashboardId = $dashboardId;
        $this->resetNewWidget();
    }

    public function createWidget(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::REPORTS_MANAGE)) {
            return;
        }

        if (! $this->activeDashboardId) {
            return;
        }

        $config = $this->decodeJsonField($this->newWidget['config'] ?? '') ?? [];
        $maxSort = ReportDashboardWidget::where('dashboard_id', $this->activeDashboardId)->max('sort_order') ?? 0;

        ReportDashboardWidget::create([
            'dashboard_id' => $this->activeDashboardId,
            'title' => $this->newWidget['title'] ?: 'Widget',
            'widget_type' => $this->newWidget['widget_type'] ?: 'kpi',
            'report_id' => $this->newWidget['report_id'] ?: null,
            'data_source' => $this->newWidget['data_source'] ?: null,
            'config' => $config,
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        $this->resetNewWidget();
        session()->flash('status', 'Widget added.');
    }

    public function reorderWidgets(array $orderedIds): void
    {
        if (! auth()->user()?->can(PermissionCatalog::REPORTS_MANAGE)) {
            return;
        }

        foreach ($orderedIds as $index => $widgetId) {
            ReportDashboardWidget::where('id', $widgetId)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function savePermission(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::REPORTS_MANAGE)) {
            return;
        }

        $this->validate([
            'permissionForm.report_id' => ['required', 'integer', 'exists:reports,id'],
            'permissionForm.role' => ['required', 'string'],
        ]);

        $allowedFields = $this->parseListField($this->permissionForm['allowed_fields'] ?? '');

        ReportPermission::create([
            'report_id' => $this->permissionForm['report_id'],
            'role' => $this->permissionForm['role'],
            'can_view' => (bool) $this->permissionForm['can_view'],
            'can_edit' => (bool) $this->permissionForm['can_edit'],
            'can_share' => (bool) $this->permissionForm['can_share'],
            'allowed_fields' => $allowedFields ?: null,
        ]);

        $this->resetPermissionForm();
        session()->flash('status', 'Report permission saved.');
    }

    public function loadAnalytics(): void
    {
        $service = app(ReportService::class);
        $this->analytics = $service->generateByType('predictive_analytics', [], auth()->user());
    }

    private function decodeJsonField(string $value): ?array
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    private function parseListField(string|array $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $value = trim($value);
        if ($value === '') {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->toArray();
    }

    private function parseSortField(string|array $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $value = trim($value);
        if ($value === '') {
            return [];
        }

        return collect(explode(',', $value))
            ->map(function ($item) {
                $parts = array_map('trim', explode(':', $item));
                return [
                    'field' => $parts[0] ?? null,
                    'direction' => $parts[1] ?? 'asc',
                ];
            })
            ->filter(fn ($sort) => $sort['field'])
            ->values()
            ->toArray();
    }

    public function render()
    {
        $totalRevenue = Invoice::whereNotNull('paid_at')->sum('total');
        $monthlyRevenue = Invoice::whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('total');
        $averageRating = WorkOrderFeedback::avg('rating') ?? 0;

        $statusCounts = WorkOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $categoryCounts = WorkOrderCategory::withCount('workOrders')
            ->orderByDesc('work_orders_count')
            ->take(5)
            ->get();

        $reportQuery = Report::with([
            'createdBy',
            'schedules',
            'runs' => fn ($query) => $query->latest()->limit(1),
            'permissions',
        ])->latest();

        if (! $this->canManage) {
            $user = auth()->user();
            $roles = $user?->roles->pluck('name') ?? collect();
            $permittedIds = ReportPermission::query()
                ->where('can_view', true)
                ->where(function ($query) use ($user, $roles) {
                    if ($user) {
                        $query->where('user_id', $user->id);
                    }
                    if ($roles->isNotEmpty()) {
                        $query->orWhereIn('role', $roles);
                    }
                })
                ->pluck('report_id');

            $reportQuery->where(function ($query) use ($user, $permittedIds) {
                $query->where('is_public', true);
                if ($user) {
                    $query->orWhere('created_by_user_id', $user->id);
                }
                if ($permittedIds->isNotEmpty()) {
                    $query->orWhereIn('id', $permittedIds);
                }
            });
        }

        $reports = $reportQuery->get();

        $dashboards = collect();
        $dashboardData = [];
        if ($this->activeTab === 'dashboards') {
            $dashboardQuery = ReportDashboard::with(['widgets.report', 'createdBy'])
                ->orderByDesc('is_default')
                ->orderBy('name');
            if (! $this->canManage) {
                $dashboardQuery->where(function ($query) {
                    $query->where('is_public', true)
                        ->orWhere('created_by_user_id', auth()->id());
                });
            }
            $dashboards = $dashboardQuery->get();

            if ($this->activeDashboardId) {
                $dashboard = $dashboards->firstWhere('id', $this->activeDashboardId);
                if ($dashboard) {
                    $service = app(ReportService::class);
                    foreach ($dashboard->widgets as $widget) {
                        if (! $widget->report) {
                            continue;
                        }

                        $data = $service->generateForReport($widget->report, $dashboard->filters ?? [], auth()->user());
                        $dashboardData[$widget->id] = $data;
                    }
                }
            }
        }

        $exports = collect();
        if ($this->activeTab === 'exports') {
            $exportQuery = ReportExport::with(['report', 'requestedBy'])
                ->latest();
            if (! $this->canManage) {
                $exportQuery->where('requested_by_user_id', auth()->id());
            }
            $exports = $exportQuery->limit(25)->get();
        }

        $schedules = collect();
        if ($this->activeTab === 'schedules') {
            $scheduleQuery = ReportSchedule::with('report')->latest();
            if ($reports->isNotEmpty()) {
                $scheduleQuery->whereIn('report_id', $reports->pluck('id'));
            }
            $schedules = $scheduleQuery->limit(25)->get();
        }

        $permissions = collect();
        if ($this->activeTab === 'permissions') {
            $permissions = ReportPermission::with('report')->latest()->limit(50)->get();
        }

        if ($this->activeTab === 'analytics' && $this->analytics === []) {
            $this->loadAnalytics();
        }

        return view('livewire.reports.index', [
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'averageRating' => $averageRating,
            'statusCounts' => $statusCounts,
            'categoryCounts' => $categoryCounts,
            'reports' => $reports,
            'dashboards' => $dashboards,
            'dashboardData' => $dashboardData,
            'exports' => $exports,
            'schedules' => $schedules,
            'permissions' => $permissions,
            'roles' => RoleCatalog::all(),
            'canManage' => $this->canManage,
            'canExport' => $this->canExport,
            'analytics' => $this->analytics,
        ]);
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(PermissionCatalog::REPORTS_MANAGE);
    }

    public function getCanExportProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(PermissionCatalog::REPORTS_EXPORT);
    }
}
