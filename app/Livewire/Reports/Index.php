<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use App\Models\Report;
use App\Models\ReportSchedule;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderFeedback;
use App\Services\ReportService;
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

    public array $reportTypes = [
        'daily_summary' => 'Daily Work Summary',
        'weekly_productivity' => 'Weekly Productivity',
        'monthly_performance' => 'Monthly Performance',
        'customer_satisfaction' => 'Customer Satisfaction',
        'revenue' => 'Revenue',
        'profitability' => 'Profitability',
        'accounts_receivable_aging' => 'Accounts Receivable Aging',
        'technician_utilization' => 'Technician Utilization',
        'first_time_fix' => 'First-Time Fix Rate',
        'response_time' => 'Response Time',
        'schedule_adherence' => 'Schedule Adherence',
        'equipment_reliability' => 'Equipment Reliability',
        'maintenance_frequency' => 'Maintenance Frequency',
        'parts_usage' => 'Parts Usage',
        'lifecycle_analysis' => 'Lifecycle Analysis',
        'custom' => 'Custom Report',
    ];

    public array $dataSources = [
        'work_orders' => 'Work Orders',
        'invoices' => 'Invoices',
        'equipment' => 'Equipment',
        'parts' => 'Parts',
        'technicians' => 'Technicians',
    ];

    public function mount(): void
    {
        $this->resetNewReport();
        $this->resetNewSchedule();
    }

    public function resetNewReport(): void
    {
        $this->newReport = [
            'name' => '',
            'report_type' => 'daily_summary',
            'data_source' => 'work_orders',
            'description' => '',
            'fields' => '',
            'filters' => '',
            'group_by' => '',
            'sort_by' => '',
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
            'recipients' => '',
            'is_active' => true,
        ];
    }

    public function createReport(): void
    {
        $this->validate([
            'newReport.name' => ['required', 'string', 'max:255'],
            'newReport.report_type' => ['required', 'string', 'max:50'],
            'newReport.data_source' => ['nullable', 'string', 'max:50'],
        ]);

        $filters = $this->decodeJsonField($this->newReport['filters'] ?? '');
        $groupBy = $this->parseListField($this->newReport['group_by'] ?? '');
        $sortBy = $this->parseSortField($this->newReport['sort_by'] ?? '');
        $fields = $this->parseListField($this->newReport['fields'] ?? '');

        Report::create([
            'name' => $this->newReport['name'],
            'report_type' => $this->newReport['report_type'],
            'data_source' => $this->newReport['report_type'] === 'custom' ? $this->newReport['data_source'] : null,
            'description' => $this->newReport['description'],
            'definition' => ['fields' => $fields],
            'filters' => $filters,
            'group_by' => $groupBy,
            'sort_by' => $sortBy,
            'is_public' => (bool) $this->newReport['is_public'],
            'created_by_user_id' => auth()->id(),
        ]);

        session()->flash('status', 'Report created.');
        $this->resetNewReport();
        $this->showCreate = false;
    }

    public function previewReport(int $reportId): void
    {
        $report = Report::findOrFail($reportId);
        $service = app(ReportService::class);
        $payload = $service->generateForReport($report);

        $this->preview = $payload;
        $this->previewTitle = $report->name;
        $this->showPreview = true;
    }

    public function previewTemplate(string $reportType): void
    {
        $service = app(ReportService::class);
        $payload = $service->generateByType($reportType);

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
        $this->scheduleReportId = $reportId;
        $this->resetNewSchedule();
    }

    public function createSchedule(): void
    {
        if (! $this->scheduleReportId) {
            return;
        }

        $this->validate([
            'newSchedule.frequency' => ['required', 'string', 'max:20'],
            'newSchedule.time_of_day' => ['nullable', 'string', 'max:10'],
        ]);

        ReportSchedule::create([
            'report_id' => $this->scheduleReportId,
            'frequency' => $this->newSchedule['frequency'],
            'day_of_week' => $this->newSchedule['frequency'] === 'weekly' ? $this->newSchedule['day_of_week'] : null,
            'day_of_month' => $this->newSchedule['frequency'] === 'monthly' ? $this->newSchedule['day_of_month'] : null,
            'time_of_day' => $this->newSchedule['time_of_day'],
            'recipients' => $this->parseListField($this->newSchedule['recipients']),
            'is_active' => (bool) $this->newSchedule['is_active'],
            'next_run_at' => now(),
        ]);

        $this->scheduleReportId = null;
        $this->resetNewSchedule();
        session()->flash('status', 'Report schedule saved.');
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

        $reports = Report::with([
            'createdBy',
            'schedules',
            'runs' => fn ($query) => $query->latest()->limit(1),
        ])->latest()->get();

        return view('livewire.reports.index', [
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'averageRating' => $averageRating,
            'statusCounts' => $statusCounts,
            'categoryCounts' => $categoryCounts,
            'reports' => $reports,
        ]);
    }
}
