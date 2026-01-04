<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Part;
use App\Models\Report;
use App\Models\ReportPermission;
use App\Models\SystemSetting;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderFeedback;
use App\Models\WorkOrderPart;
use App\Models\User;
use App\Support\PermissionCatalog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    public function generateForReport(Report $report, array $overrides = [], ?User $viewer = null): array
    {
        $viewer = $viewer ?? auth()->user();

        if (! $this->canViewReport($report, $viewer)) {
            return [
                'columns' => [],
                'rows' => [],
                'meta' => [
                    'error' => 'Not authorized to view this report.',
                ],
            ];
        }

        $cacheTtl = (int) config('reporting.cache_ttl', 0);
        $cacheKey = $this->cacheKey($report, $overrides, $viewer);

        $generator = function () use ($report, $overrides, $viewer) {
            if ($report->report_type !== 'custom') {
                return $this->generateByType($report->report_type, $overrides, $viewer, $report);
            }

            return $this->generateCustom($report, $overrides, $viewer);
        };

        $payload = $cacheTtl > 0
            ? Cache::remember($cacheKey, $cacheTtl, $generator)
            : $generator();

        $payload = $this->applyComparison($payload, $report, $overrides, $viewer);

        return $this->applyFieldPermissions($payload, $report, $viewer);
    }

    public function generateByType(string $type, array $filters = [], ?User $viewer = null, ?Report $report = null): array
    {
        return match ($type) {
            'daily_summary' => $this->dailySummary($filters, $viewer),
            'weekly_productivity' => $this->weeklyProductivity($filters, $viewer),
            'monthly_performance' => $this->monthlyPerformance($filters, $viewer),
            'technician_performance' => $this->technicianPerformance($filters, $viewer),
            'customer_satisfaction' => $this->customerSatisfaction($filters, $viewer),
            'customer_activity' => $this->customerActivity($filters, $viewer),
            'revenue' => $this->revenueReport($filters, $viewer),
            'cost_analysis' => $this->costAnalysis($filters, $viewer),
            'profitability' => $this->profitabilityReport($filters, $viewer),
            'accounts_receivable_aging' => $this->accountsReceivableAging($filters, $viewer),
            'invoice_history' => $this->invoiceHistory($filters, $viewer),
            'technician_utilization' => $this->technicianUtilization($filters, $viewer),
            'first_time_fix' => $this->firstTimeFix($filters, $viewer),
            'response_time' => $this->responseTime($filters, $viewer),
            'schedule_adherence' => $this->scheduleAdherence($filters, $viewer),
            'sla_compliance' => $this->slaCompliance($filters, $viewer),
            'equipment_reliability' => $this->equipmentReliability($filters, $viewer),
            'maintenance_frequency' => $this->maintenanceFrequency($filters, $viewer),
            'parts_usage' => $this->partsUsage($filters, $viewer),
            'lifecycle_analysis' => $this->lifecycleAnalysis($filters, $viewer),
            'predictive_analytics' => $this->predictiveAnalytics($filters, $viewer),
            default => $this->dailySummary($filters, $viewer),
        };
    }

    private function generateCustom(Report $report, array $overrides = [], ?User $viewer = null): array
    {
        $definition = $report->definition ?? [];
        $fields = $this->parseList($definition['fields'] ?? []);
        $calculatedFields = $definition['calculated_fields'] ?? [];
        $limit = (int) ($definition['limit'] ?? ($overrides['limit'] ?? 200));
        $filters = $this->normalizeFilters($report->filters ?? []);
        $filters = array_merge($filters, $this->normalizeFilters($overrides));
        $filters = array_filter($filters, fn ($filter) => ! in_array($filter['field'] ?? '', ['start_date', 'end_date'], true));
        $groupBy = $this->parseList($report->group_by ?? []);
        $sortBy = $this->normalizeSort($report->sort_by ?? []);

        $query = $this->queryForSource($report->data_source ?? 'work_orders');
        $this->applyAccessScope($query, $report->data_source ?? 'work_orders', $viewer);
        $this->applyFilters($query, $filters);

        if ($groupBy !== []) {
            $query->select($groupBy);
            $query->selectRaw('COUNT(*) as total');
            $query->groupBy($groupBy);
        } elseif ($fields !== []) {
            $query->select($fields);
        }

        foreach ($sortBy as $sort) {
            $query->orderBy($sort['field'], $sort['direction']);
        }

        $rows = $query->limit($limit)->get();
        $columns = $groupBy !== []
            ? array_merge($groupBy, ['total'])
            : ($fields !== [] ? $fields : array_keys($rows->first()?->getAttributes() ?? []));

        $resultRows = $rows->map(fn ($row) => $this->rowToArray($row, $columns))->toArray();
        $resultColumns = $columns;

        if ($calculatedFields !== []) {
            [$resultColumns, $resultRows] = $this->applyCalculatedFields(
                $resultColumns,
                $resultRows,
                $calculatedFields
            );
        }

        return [
            'columns' => $resultColumns,
            'rows' => $resultRows,
            'meta' => [
                'source' => $report->data_source,
                'count' => count($resultRows),
                'visualization' => $report->visualization,
                'limit' => $limit,
            ],
        ];
    }

    private function dailySummary(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->startOfDay(), now()->endOfDay());

        $baseQuery = WorkOrder::query()
            ->whereBetween('completed_at', [$start, $end]);
        $this->applyAccessScope($baseQuery, 'work_orders', $viewer);

        $orders = (clone $baseQuery)
            ->with(['assignedTo', 'organization'])
            ->orderByDesc('completed_at')
            ->limit(200)
            ->get();

        $columns = ['Work Order', 'Subject', 'Status', 'Technician', 'Customer', 'Completed At', 'Total Cost'];

        $rows = $orders->map(function (WorkOrder $order) {
            return [
                'Work Order' => $order->id,
                'Subject' => $order->subject,
                'Status' => $order->status,
                'Technician' => $order->assignedTo?->name ?? '—',
                'Customer' => $order->organization?->name ?? '—',
                'Completed At' => $order->completed_at?->toDateTimeString() ?? '—',
                'Total Cost' => number_format((float) $order->total_cost, 2, '.', ''),
            ];
        });

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $priorityCounts = (clone $baseQuery)
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $avgCompletionMinutes = (clone $baseQuery)
            ->whereNotNull('started_at')
            ->selectRaw("AVG((julianday(completed_at) - julianday(started_at)) * 24 * 60) as avg_minutes")
            ->value('avg_minutes');

        $technicianCounts = (clone $baseQuery)
            ->whereNotNull('assigned_to_user_id')
            ->selectRaw('assigned_to_user_id, COUNT(*) as total')
            ->groupBy('assigned_to_user_id')
            ->get();

        $technicianNames = User::whereIn('id', $technicianCounts->pluck('assigned_to_user_id'))
            ->pluck('name', 'id');

        $technicianProductivity = $technicianCounts->map(fn ($row) => [
            'technician' => $technicianNames[$row->assigned_to_user_id] ?? 'Unassigned',
            'completed_jobs' => (int) $row->total,
        ])->values()->toArray();

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'count' => $orders->count(),
                'total_cost' => $orders->sum('total_cost'),
                'status_counts' => $statusCounts,
                'priority_counts' => $priorityCounts,
                'avg_completion_minutes' => $avgCompletionMinutes ? number_format((float) $avgCompletionMinutes, 1) : '0.0',
                'technician_productivity' => $technicianProductivity,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function weeklyProductivity(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(7)->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('assigned_to_user_id')
            ->select('assigned_to_user_id')
            ->selectRaw('COUNT(*) as completed_jobs')
            ->selectRaw('AVG(labor_minutes) as avg_labor_minutes')
            ->selectRaw('SUM(labor_minutes) as total_labor_minutes')
            ->groupBy('assigned_to_user_id');
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $rows = $query->get();

        $technicianNames = User::whereIn('id', $rows->pluck('assigned_to_user_id'))
            ->pluck('name', 'id');

        $columns = ['Technician', 'Completed Jobs', 'Avg Labor Minutes', 'Total Labor Minutes'];

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => [
                'Technician' => $technicianNames[$row->assigned_to_user_id] ?? 'Unassigned',
                'Completed Jobs' => (int) $row->completed_jobs,
                'Avg Labor Minutes' => $row->avg_labor_minutes ? number_format((float) $row->avg_labor_minutes, 1) : '0.0',
                'Total Labor Minutes' => (int) $row->total_labor_minutes,
            ])->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function monthlyPerformance(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(6)->startOfMonth(), now()->endOfMonth());

        $workOrdersQuery = WorkOrder::query()
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', completed_at) as month")
            ->selectRaw('COUNT(*) as completed_jobs')
            ->selectRaw('AVG(labor_minutes) as avg_labor_minutes')
            ->groupBy('month')
            ->orderBy('month');
        $this->applyAccessScope($workOrdersQuery, 'work_orders', $viewer);

        $workOrders = $workOrdersQuery->get()
            ->keyBy('month');

        $invoiceQuery = Invoice::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', paid_at) as month")
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('month');
        $this->applyAccessScope($invoiceQuery, 'invoices', $viewer);

        $revenues = $invoiceQuery->pluck('revenue', 'month');

        $ratingsQuery = WorkOrderFeedback::query()
            ->join('work_orders', 'work_order_feedback.work_order_id', '=', 'work_orders.id')
            ->whereBetween('work_order_feedback.created_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', work_order_feedback.created_at) as month")
            ->selectRaw('AVG(work_order_feedback.rating) as avg_rating')
            ->groupBy('month');
        $this->applyAccessScope($ratingsQuery, 'work_orders', $viewer, 'work_orders');

        $ratings = $ratingsQuery->pluck('avg_rating', 'month');

        $months = $this->monthRange($start, $end);
        $columns = ['Month', 'Completed Jobs', 'Avg Labor Minutes', 'Revenue', 'Avg Rating'];

        $rows = [];
        foreach ($months as $month) {
            $rows[] = [
                'Month' => $month,
                'Completed Jobs' => (int) ($workOrders[$month]->completed_jobs ?? 0),
                'Avg Labor Minutes' => isset($workOrders[$month])
                    ? number_format((float) $workOrders[$month]->avg_labor_minutes, 1)
                    : '0.0',
                'Revenue' => number_format((float) ($revenues[$month] ?? 0), 2, '.', ''),
                'Avg Rating' => number_format((float) ($ratings[$month] ?? 0), 1),
            ];
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function technicianPerformance(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(30)->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('assigned_to_user_id')
            ->select('assigned_to_user_id')
            ->selectRaw('COUNT(*) as completed_jobs')
            ->selectRaw('AVG(labor_minutes) as avg_labor_minutes')
            ->selectRaw('AVG(estimated_minutes) as avg_estimated_minutes')
            ->selectRaw('SUM(CASE WHEN labor_minutes > estimated_minutes THEN labor_minutes - estimated_minutes ELSE 0 END) as overtime_minutes')
            ->selectRaw('SUM(CASE WHEN is_warranty = 0 THEN labor_minutes ELSE 0 END) as billable_minutes')
            ->selectRaw('SUM(CASE WHEN is_warranty = 1 THEN labor_minutes ELSE 0 END) as non_billable_minutes')
            ->selectRaw('SUM(CASE WHEN scheduled_start_at IS NOT NULL AND arrived_at IS NOT NULL AND ABS((julianday(arrived_at) - julianday(scheduled_start_at)) * 24 * 60) <= 15 THEN 1 ELSE 0 END) as on_time_arrivals')
            ->selectRaw('SUM(CASE WHEN scheduled_start_at IS NOT NULL AND arrived_at IS NOT NULL THEN 1 ELSE 0 END) as arrival_count')
            ->groupBy('assigned_to_user_id');
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $rows = $query->get();

        $ratingRows = WorkOrderFeedback::query()
            ->join('work_orders', 'work_order_feedback.work_order_id', '=', 'work_orders.id')
            ->whereBetween('work_orders.completed_at', [$start, $end])
            ->select('work_orders.assigned_to_user_id')
            ->selectRaw('AVG(work_order_feedback.rating) as avg_rating')
            ->groupBy('work_orders.assigned_to_user_id');
        $this->applyAccessScope($ratingRows, 'work_orders', $viewer, 'work_orders');

        $ratings = $ratingRows->pluck('avg_rating', 'assigned_to_user_id');

        $firstFixStats = $this->firstFixStats($filters, $viewer);

        $technicianNames = User::whereIn('id', $rows->pluck('assigned_to_user_id'))
            ->pluck('name', 'id');

        $columns = [
            'Technician',
            'Completed Jobs',
            'Avg Duration (Min)',
            'Avg Estimate (Min)',
            'Variance (Min)',
            'First-Time Fix Rate',
            'Avg Rating',
            'On-Time Arrival',
            'Overtime Hours',
            'Billable Hours',
            'Non-Billable Hours',
        ];

        $mapped = $rows->map(function ($row) use ($technicianNames, $ratings, $firstFixStats) {
            $avgLabor = (float) ($row->avg_labor_minutes ?? 0);
            $avgEstimate = (float) ($row->avg_estimated_minutes ?? 0);
            $variance = $avgLabor - $avgEstimate;
            $firstFix = $firstFixStats[$row->assigned_to_user_id]['rate'] ?? 0;
            $arrivalRate = $row->arrival_count > 0
                ? ($row->on_time_arrivals / $row->arrival_count) * 100
                : 0;

            return [
                'Technician' => $technicianNames[$row->assigned_to_user_id] ?? 'Unassigned',
                'Completed Jobs' => (int) $row->completed_jobs,
                'Avg Duration (Min)' => number_format($avgLabor, 1),
                'Avg Estimate (Min)' => number_format($avgEstimate, 1),
                'Variance (Min)' => number_format($variance, 1),
                'First-Time Fix Rate' => number_format($firstFix, 1) . '%',
                'Avg Rating' => number_format((float) ($ratings[$row->assigned_to_user_id] ?? 0), 2),
                'On-Time Arrival' => number_format($arrivalRate, 1) . '%',
                'Overtime Hours' => number_format(((float) ($row->overtime_minutes ?? 0)) / 60, 1),
                'Billable Hours' => number_format(((float) ($row->billable_minutes ?? 0)) / 60, 1),
                'Non-Billable Hours' => number_format(((float) ($row->non_billable_minutes ?? 0)) / 60, 1),
            ];
        });

        return [
            'columns' => $columns,
            'rows' => $mapped->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function customerSatisfaction(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(3)->startOfDay(), now()->endOfDay());

        $baseQuery = WorkOrderFeedback::query()
            ->join('work_orders', 'work_order_feedback.work_order_id', '=', 'work_orders.id')
            ->whereBetween('work_order_feedback.created_at', [$start, $end]);
        $this->applyAccessScope($baseQuery, 'work_orders', $viewer, 'work_orders');

        $rows = (clone $baseQuery)
            ->select('work_orders.assigned_to_user_id')
            ->selectRaw('AVG(work_order_feedback.rating) as avg_rating')
            ->selectRaw('COUNT(*) as responses')
            ->groupBy('work_orders.assigned_to_user_id')
            ->get();

        $technicianNames = User::whereIn('id', $rows->pluck('assigned_to_user_id'))
            ->pluck('name', 'id');

        $serviceTypeRows = (clone $baseQuery)
            ->leftJoin('work_order_categories', 'work_orders.category_id', '=', 'work_order_categories.id')
            ->selectRaw('work_order_categories.name as category')
            ->selectRaw('AVG(work_order_feedback.rating) as avg_rating')
            ->selectRaw('COUNT(*) as responses')
            ->groupBy('work_order_categories.name')
            ->orderByDesc('responses')
            ->get()
            ->map(fn ($row) => [
                'service_type' => $row->category ?? 'Uncategorized',
                'avg_rating' => number_format((float) $row->avg_rating, 2),
                'responses' => (int) $row->responses,
            ])
            ->toArray();

        $distribution = (clone $baseQuery)
            ->selectRaw('work_order_feedback.rating as rating, COUNT(*) as total')
            ->groupBy('work_order_feedback.rating')
            ->pluck('total', 'rating')
            ->toArray();

        $trend = (clone $baseQuery)
            ->selectRaw("strftime('%Y-%m', work_order_feedback.created_at) as month")
            ->selectRaw('AVG(work_order_feedback.rating) as avg_rating')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => $row->month,
                'avg_rating' => number_format((float) $row->avg_rating, 2),
            ])
            ->toArray();

        $lowRatings = (clone $baseQuery)
            ->where('work_order_feedback.rating', '<=', 2)
            ->select('work_orders.id as work_order_id', 'work_order_feedback.rating', 'work_order_feedback.comments', 'work_order_feedback.created_at')
            ->orderByDesc('work_order_feedback.created_at')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'work_order_id' => $row->work_order_id,
                'rating' => (int) $row->rating,
                'comment' => $row->comments ?? '',
                'submitted_at' => $row->created_at?->toDateTimeString(),
            ])
            ->toArray();

        $columns = ['Technician', 'Avg Rating', 'Responses'];

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => [
                'Technician' => $technicianNames[$row->assigned_to_user_id] ?? 'Unassigned',
                'Avg Rating' => number_format((float) $row->avg_rating, 2),
                'Responses' => (int) $row->responses,
            ])->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'distribution' => $distribution,
                'trend' => $trend,
                'by_service_type' => $serviceTypeRows,
                'low_ratings' => $lowRatings,
            ],
        ];
    }

    private function customerActivity(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(6)->startOfDay(), now()->endOfDay());

        $activityQuery = WorkOrder::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('organization_id')
            ->select('organization_id')
            ->selectRaw('COUNT(*) as service_requests')
            ->selectRaw('MIN(created_at) as first_request')
            ->selectRaw('MAX(completed_at) as last_service')
            ->groupBy('organization_id');
        $this->applyAccessScope($activityQuery, 'work_orders', $viewer);

        $activity = $activityQuery->get();
        $organizationIds = $activity->pluck('organization_id')->filter()->unique()->values();

        $revenueQuery = Invoice::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->whereIn('organization_id', $organizationIds)
            ->select('organization_id')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('organization_id');
        $this->applyAccessScope($revenueQuery, 'invoices', $viewer);

        $revenues = $revenueQuery->pluck('revenue', 'organization_id');
        $organizations = Organization::whereIn('id', $organizationIds)->get()->keyBy('id');

        $months = max(1, $start->diffInMonths($end) + 1);
        $columns = [
            'Customer',
            'Service Requests',
            'Revenue',
            'Requests/Month',
            'Customer Since',
            'Last Service',
            'Churn Risk',
        ];

        $rows = $activity->map(function ($row) use ($organizations, $revenues, $months) {
            $org = $organizations[$row->organization_id] ?? null;
            $lastService = $row->last_service ? Carbon::parse($row->last_service) : null;
            $churnScore = $this->churnRiskScore($lastService);

            return [
                'Customer' => $org?->name ?? 'Unknown',
                'Service Requests' => (int) $row->service_requests,
                'Revenue' => number_format((float) ($revenues[$row->organization_id] ?? 0), 2, '.', ''),
                'Requests/Month' => number_format((float) ($row->service_requests / $months), 2),
                'Customer Since' => $row->first_request ? Carbon::parse($row->first_request)->toDateString() : '—',
                'Last Service' => $lastService?->toDateString() ?? '—',
                'Churn Risk' => $churnScore['label'],
            ];
        });

        $highRisk = $rows->filter(fn ($row) => $row['Churn Risk'] === 'High')->values()->toArray();

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'total_customers' => $rows->count(),
                'high_risk_customers' => $highRisk,
            ],
        ];
    }

    private function revenueReport(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(6)->startOfMonth(), now()->endOfMonth());

        $query = Invoice::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', paid_at) as month")
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('month')
            ->orderBy('month');
        $this->applyAccessScope($query, 'invoices', $viewer);

        $rows = $query->get();

        $columns = ['Month', 'Revenue'];
        $totalRevenue = $rows->sum('revenue');
        $averageRevenue = $rows->count() > 0 ? $totalRevenue / $rows->count() : 0;

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => [
                'Month' => $row->month,
                'Revenue' => number_format((float) $row->revenue, 2, '.', ''),
            ])->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'total_revenue' => number_format((float) $totalRevenue, 2, '.', ''),
                'average_monthly_revenue' => number_format((float) $averageRevenue, 2, '.', ''),
            ],
        ];
    }

    private function costAnalysis(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(6)->startOfMonth(), now()->endOfMonth());

        $laborRate = (float) config('reporting.costs.labor_rate_per_hour', 85);
        $travelRate = (float) config('reporting.costs.travel_rate_per_hour', 35);
        $overheadRate = (float) config('reporting.costs.overhead_rate', 0.15);

        $orderQuery = WorkOrder::query()
            ->whereBetween('completed_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', completed_at) as month")
            ->selectRaw('SUM(labor_minutes) as labor_minutes')
            ->selectRaw('SUM(travel_minutes) as travel_minutes')
            ->selectRaw('COUNT(*) as job_count')
            ->groupBy('month');
        $this->applyAccessScope($orderQuery, 'work_orders', $viewer);

        $orders = $orderQuery->get()->keyBy('month');

        $partsQuery = WorkOrderPart::query()
            ->join('work_orders', 'work_order_parts.work_order_id', '=', 'work_orders.id')
            ->whereBetween('work_orders.completed_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', work_orders.completed_at) as month")
            ->selectRaw('SUM(work_order_parts.quantity * work_order_parts.unit_cost) as parts_cost')
            ->groupBy('month');
        $this->applyAccessScope($partsQuery, 'work_orders', $viewer, 'work_orders');

        $partsCosts = $partsQuery->pluck('parts_cost', 'month');

        $months = $this->monthRange($start, $end);
        $columns = ['Month', 'Labor Cost', 'Parts Cost', 'Travel Cost', 'Overhead', 'Total Cost', 'Jobs', 'Cost per Job'];
        $rows = [];
        $totals = [
            'labor' => 0,
            'parts' => 0,
            'travel' => 0,
            'overhead' => 0,
            'total' => 0,
        ];

        foreach ($months as $month) {
            $laborMinutes = (float) ($orders[$month]->labor_minutes ?? 0);
            $travelMinutes = (float) ($orders[$month]->travel_minutes ?? 0);
            $jobs = (int) ($orders[$month]->job_count ?? 0);
            $partsCost = (float) ($partsCosts[$month] ?? 0);
            $laborCost = ($laborMinutes / 60) * $laborRate;
            $travelCost = ($travelMinutes / 60) * $travelRate;
            $overhead = ($laborCost + $partsCost + $travelCost) * $overheadRate;
            $total = $laborCost + $partsCost + $travelCost + $overhead;

            $totals['labor'] += $laborCost;
            $totals['parts'] += $partsCost;
            $totals['travel'] += $travelCost;
            $totals['overhead'] += $overhead;
            $totals['total'] += $total;

            $rows[] = [
                'Month' => $month,
                'Labor Cost' => number_format($laborCost, 2, '.', ''),
                'Parts Cost' => number_format($partsCost, 2, '.', ''),
                'Travel Cost' => number_format($travelCost, 2, '.', ''),
                'Overhead' => number_format($overhead, 2, '.', ''),
                'Total Cost' => number_format($total, 2, '.', ''),
                'Jobs' => $jobs,
                'Cost per Job' => $jobs > 0 ? number_format($total / $jobs, 2, '.', '') : '0.00',
            ];
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'total_labor_cost' => number_format($totals['labor'], 2, '.', ''),
                'total_parts_cost' => number_format($totals['parts'], 2, '.', ''),
                'total_travel_cost' => number_format($totals['travel'], 2, '.', ''),
                'total_overhead' => number_format($totals['overhead'], 2, '.', ''),
                'total_cost' => number_format($totals['total'], 2, '.', ''),
            ],
        ];
    }

    private function invoiceHistory(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(6)->startOfDay(), now()->endOfDay());

        $query = Invoice::query()
            ->with(['organization', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(200);
        $this->applyAccessScope($query, 'invoices', $viewer);

        $invoices = $query->get();

        $columns = ['Invoice', 'Customer', 'Status', 'Total', 'Sent At', 'Paid At', 'Days to Payment', 'Payment Method'];

        $rows = $invoices->map(function (Invoice $invoice) {
            $sentAt = $invoice->sent_at ?? $invoice->created_at;
            $paidAt = $invoice->paid_at;
            $daysToPayment = $paidAt && $sentAt ? $sentAt->diffInDays($paidAt) : null;
            $paymentMethod = $invoice->payments->last()?->method ?? '—';

            return [
                'Invoice' => $invoice->id,
                'Customer' => $invoice->organization?->name ?? '—',
                'Status' => ucfirst($invoice->status ?? 'unknown'),
                'Total' => number_format((float) $invoice->total, 2, '.', ''),
                'Sent At' => $sentAt?->toDateString() ?? '—',
                'Paid At' => $paidAt?->toDateString() ?? '—',
                'Days to Payment' => $daysToPayment !== null ? $daysToPayment : '—',
                'Payment Method' => $paymentMethod,
            ];
        });

        $statusCounts = $invoices->groupBy('status')->map->count()->toArray();
        $paymentMethodCounts = $invoices->flatMap(fn ($invoice) => [$invoice->payments->last()?->method ?? 'Unknown'])
            ->countBy()
            ->toArray();
        $avgDaysToPayment = $invoices->filter(fn ($invoice) => $invoice->paid_at && ($invoice->sent_at || $invoice->created_at))
            ->map(function ($invoice) {
                $sentAt = $invoice->sent_at ?? $invoice->created_at;
                return $sentAt?->diffInDays($invoice->paid_at) ?? 0;
            })
            ->avg();

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'status_counts' => $statusCounts,
                'payment_methods' => $paymentMethodCounts,
                'average_days_to_payment' => $avgDaysToPayment ? number_format((float) $avgDaysToPayment, 1) : '0.0',
            ],
        ];
    }

    private function profitabilityReport(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(3)->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->with(['invoices', 'parts', 'organization'])
            ->whereBetween('completed_at', [$start, $end])
            ->orderByDesc('completed_at')
            ->limit(50);
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $orders = $query->get();

        $columns = ['Work Order', 'Customer', 'Revenue', 'Parts Cost', 'Margin', 'Margin %'];

        $rows = $orders->map(function (WorkOrder $order) {
            $revenue = $order->invoices->sum('total');
            $partsCost = $order->parts->sum(fn ($part) => $part->quantity * $part->unit_cost);
            $margin = $revenue - $partsCost;
            $marginPercent = $revenue > 0 ? ($margin / $revenue) * 100 : 0;

            return [
                'Work Order' => $order->id,
                'Customer' => $order->organization?->name ?? '—',
                'Revenue' => number_format((float) $revenue, 2, '.', ''),
                'Parts Cost' => number_format((float) $partsCost, 2, '.', ''),
                'Margin' => number_format((float) $margin, 2, '.', ''),
                'Margin %' => number_format((float) $marginPercent, 1) . '%',
            ];
        });

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'count' => $orders->count(),
                'total_margin' => $rows->sum(fn ($row) => (float) str_replace(',', '', $row['Margin'])),
            ],
        ];
    }

    private function accountsReceivableAging(array $filters, ?User $viewer = null): array
    {
        $today = Carbon::today();

        $query = Invoice::query()
            ->whereNull('paid_at');
        $this->applyAccessScope($query, 'invoices', $viewer);

        $invoices = $query->get();

        $buckets = [
            '0-30' => ['count' => 0, 'total' => 0],
            '31-60' => ['count' => 0, 'total' => 0],
            '61-90' => ['count' => 0, 'total' => 0],
            '90+' => ['count' => 0, 'total' => 0],
        ];

        foreach ($invoices as $invoice) {
            $baseDate = $invoice->due_date ?? $invoice->sent_at ?? $invoice->created_at;
            $days = $baseDate ? $baseDate->diffInDays($today) : 0;
            $bucket = match (true) {
                $days <= 30 => '0-30',
                $days <= 60 => '31-60',
                $days <= 90 => '61-90',
                default => '90+',
            };

            $buckets[$bucket]['count'] += 1;
            $buckets[$bucket]['total'] += (float) $invoice->total;
        }

        $columns = ['Age Bucket', 'Invoices', 'Total Due'];
        $rows = collect($buckets)->map(fn ($value, $bucket) => [
            'Age Bucket' => $bucket,
            'Invoices' => $value['count'],
            'Total Due' => number_format((float) $value['total'], 2, '.', ''),
        ])->values();

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'total_due' => $rows->sum(fn ($row) => (float) $row['Total Due']),
            ],
        ];
    }

    private function technicianUtilization(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(30)->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('assigned_to_user_id')
            ->select('assigned_to_user_id')
            ->selectRaw('COUNT(*) as completed_jobs')
            ->selectRaw('SUM(labor_minutes) as total_labor')
            ->selectRaw('SUM(travel_minutes) as total_travel')
            ->groupBy('assigned_to_user_id');
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $rows = $query->get();

        $technicianNames = User::whereIn('id', $rows->pluck('assigned_to_user_id'))
            ->pluck('name', 'id');

        $columns = ['Technician', 'Completed Jobs', 'Labor Minutes', 'Travel Minutes', 'Utilization'];

        $mapped = $rows->map(function ($row) use ($technicianNames) {
            $labor = (int) $row->total_labor;
            $travel = (int) $row->total_travel;
            $total = $labor + $travel;
            $utilization = $total > 0 ? ($labor / $total) * 100 : 0;

            return [
                'Technician' => $technicianNames[$row->assigned_to_user_id] ?? 'Unassigned',
                'Completed Jobs' => (int) $row->completed_jobs,
                'Labor Minutes' => $labor,
                'Travel Minutes' => $travel,
                'Utilization' => number_format($utilization, 1) . '%',
            ];
        });

        return [
            'columns' => $columns,
            'rows' => $mapped->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function firstTimeFix(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(90)->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('equipment_id')
            ->orderBy('completed_at');
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $orders = $query->get(['id', 'equipment_id', 'assigned_to_user_id', 'completed_at']);

        $byEquipment = $orders->groupBy('equipment_id');
        $stats = [];

        foreach ($byEquipment as $equipmentOrders) {
            $equipmentOrders = $equipmentOrders->sortBy('completed_at')->values();
            $count = $equipmentOrders->count();

            for ($i = 0; $i < $count; $i++) {
                $current = $equipmentOrders[$i];
                $next = $equipmentOrders[$i + 1] ?? null;
                $isFirstFix = true;

                if ($next && $current->completed_at && $next->completed_at) {
                    $isFirstFix = $current->completed_at->diffInDays($next->completed_at) > 14;
                }

                $techId = $current->assigned_to_user_id ?? 0;
                $stats[$techId]['total'] = ($stats[$techId]['total'] ?? 0) + 1;
                $stats[$techId]['first_fix'] = ($stats[$techId]['first_fix'] ?? 0) + ($isFirstFix ? 1 : 0);
            }
        }

        $technicianNames = User::whereIn('id', array_keys($stats))
            ->pluck('name', 'id');

        $columns = ['Technician', 'Total Jobs', 'First-Time Fixes', 'Rate'];
        $rows = collect($stats)->map(function ($value, $techId) use ($technicianNames) {
            $rate = $value['total'] > 0 ? ($value['first_fix'] / $value['total']) * 100 : 0;

            return [
                'Technician' => $technicianNames[$techId] ?? 'Unassigned',
                'Total Jobs' => $value['total'],
                'First-Time Fixes' => $value['first_fix'],
                'Rate' => number_format($rate, 1) . '%',
            ];
        })->values();

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function responseTime(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(60)->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->whereBetween('requested_at', [$start, $end])
            ->select('priority')
            ->selectRaw("AVG((julianday(assigned_at) - julianday(requested_at)) * 24 * 60) as avg_assign_minutes")
            ->selectRaw("AVG((julianday(started_at) - julianday(assigned_at)) * 24 * 60) as avg_start_minutes")
            ->selectRaw("AVG((julianday(completed_at) - julianday(started_at)) * 24 * 60) as avg_complete_minutes")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('priority');
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $rows = $query->get();

        $columns = ['Priority', 'Avg Assign Minutes', 'Avg Start Minutes', 'Avg Completion Minutes', 'Requests'];

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => [
                'Priority' => ucfirst($row->priority),
                'Avg Assign Minutes' => number_format((float) ($row->avg_assign_minutes ?? 0), 1),
                'Avg Start Minutes' => number_format((float) ($row->avg_start_minutes ?? 0), 1),
                'Avg Completion Minutes' => number_format((float) ($row->avg_complete_minutes ?? 0), 1),
                'Requests' => (int) $row->total,
            ])->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function scheduleAdherence(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(30)->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->whereBetween('scheduled_start_at', [$start, $end])
            ->whereNotNull('scheduled_start_at')
            ->whereNotNull('started_at')
            ->getQuery();
        $ordersQuery = WorkOrder::query()->fromSub($query, 'work_orders');
        $this->applyAccessScope($ordersQuery, 'work_orders', $viewer);

        $orders = $ordersQuery->get([
            'assigned_to_user_id',
            'scheduled_start_at',
            'scheduled_end_at',
            'arrived_at',
            'started_at',
            'on_hold_reason',
        ]);

        $stats = [];
        $daily = [];
        $reasons = [];

        foreach ($orders as $order) {
            $techId = $order->assigned_to_user_id ?? 0;
            $arrival = $order->arrived_at ?? $order->started_at;
            $diff = $arrival && $order->scheduled_start_at
                ? $order->scheduled_start_at->diffInMinutes($arrival, false)
                : 0;
            $isOnTime = $diff <= 15;
            $withinWindow = $order->scheduled_end_at && $arrival
                ? $arrival->between($order->scheduled_start_at, $order->scheduled_end_at)
                : false;

            $stats[$techId]['total'] = ($stats[$techId]['total'] ?? 0) + 1;
            $stats[$techId]['total_diff'] = ($stats[$techId]['total_diff'] ?? 0) + abs($diff);
            $stats[$techId]['on_time'] = ($stats[$techId]['on_time'] ?? 0) + ($isOnTime ? 1 : 0);
            $stats[$techId]['within_window'] = ($stats[$techId]['within_window'] ?? 0) + ($withinWindow ? 1 : 0);

            if (! $isOnTime) {
                $reason = $order->on_hold_reason ?: 'Unspecified';
                $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
            }

            $dayKey = $order->scheduled_start_at?->toDateString() ?? 'unknown';
            $daily[$dayKey]['total'] = ($daily[$dayKey]['total'] ?? 0) + 1;
            $daily[$dayKey]['on_time'] = ($daily[$dayKey]['on_time'] ?? 0) + ($isOnTime ? 1 : 0);
        }

        $technicianNames = User::whereIn('id', array_keys($stats))
            ->pluck('name', 'id');

        $columns = ['Technician', 'Appointments', 'Avg Variance Minutes', 'On-Time Rate', 'Within Window'];
        $rows = collect($stats)->map(function ($value, $techId) use ($technicianNames) {
            $avgDiff = $value['total'] > 0 ? $value['total_diff'] / $value['total'] : 0;
            $rate = $value['total'] > 0 ? ($value['on_time'] / $value['total']) * 100 : 0;
            $windowRate = $value['total'] > 0 ? ($value['within_window'] / $value['total']) * 100 : 0;

            return [
                'Technician' => $technicianNames[$techId] ?? 'Unassigned',
                'Appointments' => $value['total'],
                'Avg Variance Minutes' => number_format($avgDiff, 1),
                'On-Time Rate' => number_format($rate, 1) . '%',
                'Within Window' => number_format($windowRate, 1) . '%',
            ];
        })->values();

        $dailyBreakdown = collect($daily)
            ->map(fn ($value, $day) => [
                'date' => $day,
                'total' => $value['total'] ?? 0,
                'on_time' => $value['on_time'] ?? 0,
                'late' => ($value['total'] ?? 0) - ($value['on_time'] ?? 0),
            ])
            ->values()
            ->toArray();

        arsort($reasons);

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'daily_breakdown' => $dailyBreakdown,
                'delay_reasons' => $reasons,
            ],
        ];
    }

    private function slaCompliance(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(30)->startOfDay(), now()->endOfDay());

        $targets = $this->slaTargets();
        $query = WorkOrder::query()
            ->with('organization.serviceAgreement')
            ->whereBetween('requested_at', [$start, $end]);
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $orders = $query->get(['id', 'priority', 'requested_at', 'assigned_at', 'organization_id']);

        $stats = [];

        foreach ($orders as $order) {
            $priority = $order->priority ?? 'standard';
            $target = $order->organization?->serviceAgreement?->response_time_minutes
                ?? ($targets[$priority] ?? $targets['standard']);
            $requestedAt = $order->requested_at ?? $order->created_at;
            $assignedAt = $order->assigned_at;

            if (! $requestedAt) {
                continue;
            }

            $responseMinutes = $requestedAt->diffInMinutes($assignedAt ?? now());
            $status = $this->slaStatus($responseMinutes, $target, $assignedAt !== null);

            $stats[$priority]['on_track'] = ($stats[$priority]['on_track'] ?? 0) + ($status === 'on_track' ? 1 : 0);
            $stats[$priority]['at_risk'] = ($stats[$priority]['at_risk'] ?? 0) + ($status === 'at_risk' ? 1 : 0);
            $stats[$priority]['breached'] = ($stats[$priority]['breached'] ?? 0) + ($status === 'breached' ? 1 : 0);
            $stats[$priority]['total'] = ($stats[$priority]['total'] ?? 0) + 1;
            $stats[$priority]['response_sum'] = ($stats[$priority]['response_sum'] ?? 0) + $responseMinutes;
        }

        $columns = ['Priority', 'On Track', 'At Risk', 'Breached', 'Total', 'Avg Response Minutes'];
        $rows = [];

        foreach ($stats as $priority => $data) {
            $avg = $data['total'] > 0 ? $data['response_sum'] / $data['total'] : 0;
            $rows[] = [
                'Priority' => ucfirst($priority),
                'On Track' => $data['on_track'] ?? 0,
                'At Risk' => $data['at_risk'] ?? 0,
                'Breached' => $data['breached'] ?? 0,
                'Total' => $data['total'] ?? 0,
                'Avg Response Minutes' => number_format($avg, 1),
            ];
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function equipmentReliability(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subYear()->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->join('equipment', 'work_orders.equipment_id', '=', 'equipment.id')
            ->whereBetween('work_orders.created_at', [$start, $end])
            ->select('equipment.type', 'equipment.model')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('equipment.type', 'equipment.model')
            ->orderByDesc('total')
            ->limit(15);
        $this->applyAccessScope($query, 'work_orders', $viewer, 'work_orders');

        $rows = $query->get();

        $manufacturerQuery = WorkOrder::query()
            ->join('equipment', 'work_orders.equipment_id', '=', 'equipment.id')
            ->whereBetween('work_orders.created_at', [$start, $end])
            ->select('equipment.manufacturer')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('equipment.manufacturer')
            ->orderByDesc('total')
            ->limit(10);
        $this->applyAccessScope($manufacturerQuery, 'work_orders', $viewer, 'work_orders');

        $manufacturerRows = $manufacturerQuery->get()
            ->map(fn ($row) => [
                'manufacturer' => $row->manufacturer ?? 'Unknown',
                'service_events' => (int) $row->total,
            ])
            ->toArray();

        $eventQuery = WorkOrder::query()
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('equipment_id')
            ->orderBy('completed_at');
        $this->applyAccessScope($eventQuery, 'work_orders', $viewer);

        $events = $eventQuery->get(['equipment_id', 'completed_at']);
        $byEquipment = $events->groupBy('equipment_id');
        $equipmentMap = Equipment::whereIn('id', $byEquipment->keys())->get()->keyBy('id');

        $mtbf = [];
        $ageBuckets = ['0-1' => 0, '1-3' => 0, '3-5' => 0, '5+' => 0];

        foreach ($byEquipment as $equipmentId => $entries) {
            $entries = $entries->sortBy('completed_at')->values();
            $intervals = [];

            for ($i = 1; $i < $entries->count(); $i++) {
                $prev = $entries[$i - 1]->completed_at;
                $current = $entries[$i]->completed_at;
                if ($prev && $current) {
                    $intervals[] = $prev->diffInDays($current);
                }
            }

            $avgInterval = $intervals !== [] ? array_sum($intervals) / count($intervals) : null;
            $equipment = $equipmentMap[$equipmentId] ?? null;
            $ageYears = $equipment?->purchase_date ? $equipment->purchase_date->diffInDays(now()) / 365 : null;

            if ($ageYears !== null) {
                $bucket = match (true) {
                    $ageYears < 1 => '0-1',
                    $ageYears < 3 => '1-3',
                    $ageYears < 5 => '3-5',
                    default => '5+',
                };
                $ageBuckets[$bucket] += 1;
            }

            if ($avgInterval !== null) {
                $mtbf[] = [
                    'equipment' => $equipment?->name ?? 'Unknown',
                    'type' => $equipment?->type ?? '—',
                    'avg_days_between' => number_format((float) $avgInterval, 1),
                    'events' => $entries->count(),
                ];
            }
        }

        $mtbf = collect($mtbf)->sortBy('avg_days_between')->take(10)->values()->toArray();

        $columns = ['Type', 'Model', 'Service Events'];

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => [
                'Type' => $row->type,
                'Model' => $row->model ?? '—',
                'Service Events' => (int) $row->total,
            ])->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'by_manufacturer' => $manufacturerRows,
                'age_distribution' => $ageBuckets,
                'mtbf' => $mtbf,
            ],
        ];
    }

    private function maintenanceFrequency(array $filters, ?User $viewer = null): array
    {
        $query = Equipment::query()
            ->withCount('workOrders')
            ->withMax('workOrders', 'completed_at')
            ->orderByDesc('work_orders_count')
            ->limit(20);
        $this->applyAccessScope($query, 'equipment', $viewer);

        $equipment = $query->get();

        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(6)->startOfDay(), now()->endOfDay());
        $maintenanceQuery = WorkOrder::query()
            ->leftJoin('work_order_categories', 'work_orders.category_id', '=', 'work_order_categories.id')
            ->whereBetween('work_orders.completed_at', [$start, $end]);
        $this->applyAccessScope($maintenanceQuery, 'work_orders', $viewer, 'work_orders');

        $totalJobs = (clone $maintenanceQuery)->count();
        $preventiveJobs = (clone $maintenanceQuery)
            ->where(function ($query) {
                $query->where('work_order_categories.name', 'like', '%preventive%')
                    ->orWhere('work_order_categories.name', 'like', '%pm%');
            })
            ->count();
        $reactiveJobs = max(0, $totalJobs - $preventiveJobs);

        $columns = ['Equipment', 'Type', 'Work Orders', 'Last Service'];

        $rows = $equipment->map(fn (Equipment $item) => [
            'Equipment' => $item->name,
            'Type' => $item->type,
            'Work Orders' => $item->work_orders_count,
            'Last Service' => $item->work_orders_max_completed_at
                ? Carbon::parse($item->work_orders_max_completed_at)->toDateString()
                : '—',
        ]);

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'count' => $equipment->count(),
                'preventive_jobs' => $preventiveJobs,
                'reactive_jobs' => $reactiveJobs,
                'preventive_ratio' => $totalJobs > 0 ? number_format(($preventiveJobs / $totalJobs) * 100, 1) . '%' : '0.0%',
            ],
        ];
    }

    private function partsUsage(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(90)->startOfDay(), now()->endOfDay());

        $query = WorkOrderPart::query()
            ->join('parts', 'work_order_parts.part_id', '=', 'parts.id')
            ->join('work_orders', 'work_order_parts.work_order_id', '=', 'work_orders.id')
            ->whereBetween('work_orders.completed_at', [$start, $end])
            ->select('parts.id as part_id', 'parts.name')
            ->selectRaw('SUM(work_order_parts.quantity) as total_used')
            ->selectRaw('SUM(work_order_parts.quantity * work_order_parts.unit_cost) as total_cost')
            ->groupBy('parts.id', 'parts.name')
            ->orderByDesc('total_used')
            ->limit(20);
        $this->applyAccessScope($query, 'work_orders', $viewer, 'work_orders');

        $rows = $query->get();

        $inventory = InventoryItem::query()
            ->whereIn('part_id', $rows->pluck('part_id'))
            ->select('part_id')
            ->selectRaw('SUM(quantity) as on_hand')
            ->groupBy('part_id')
            ->pluck('on_hand', 'part_id');

        $columns = ['Part', 'Units Used', 'Total Cost', 'On Hand', 'Turnover'];

        $mapped = $rows->map(function ($row) use ($inventory) {
            $onHand = (int) ($inventory[$row->part_id] ?? 0);
            $turnover = $onHand > 0 ? ($row->total_used / $onHand) : 0;

            return [
                'Part' => $row->name,
                'Units Used' => (int) $row->total_used,
                'Total Cost' => number_format((float) $row->total_cost, 2, '.', ''),
                'On Hand' => $onHand,
                'Turnover' => number_format((float) $turnover, 2),
            ];
        });

        $totalUsed = $rows->sum('total_used');
        $totalOnHand = $inventory->sum();

        return [
            'columns' => $columns,
            'rows' => $mapped->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'total_used' => (int) $totalUsed,
                'total_on_hand' => (int) $totalOnHand,
                'inventory_turnover' => $totalOnHand > 0 ? number_format($totalUsed / $totalOnHand, 2) : '0.00',
            ],
        ];
    }

    private function lifecycleAnalysis(array $filters, ?User $viewer = null): array
    {
        $query = Equipment::query()
            ->withCount('workOrders')
            ->withSum('workOrders', 'total_cost')
            ->orderByDesc('work_orders_sum_total_cost')
            ->limit(20);
        $this->applyAccessScope($query, 'equipment', $viewer);

        $equipment = $query->get();

        $columns = ['Equipment', 'Type', 'Age (Years)', 'Work Orders', 'Total Maintenance Cost'];

        $rows = $equipment->map(function (Equipment $item) {
            $age = $item->purchase_date
                ? number_format($item->purchase_date->diffInDays(now()) / 365, 1)
                : '—';

            return [
                'Equipment' => $item->name,
                'Type' => $item->type,
                'Age (Years)' => $age,
                'Work Orders' => $item->work_orders_count,
                'Total Maintenance Cost' => number_format((float) ($item->work_orders_sum_total_cost ?? 0), 2, '.', ''),
            ];
        });

        $replacementQuery = Equipment::query()
            ->whereNotNull('purchase_date')
            ->whereNotNull('expected_lifespan_months')
            ->orderByDesc('purchase_date')
            ->limit(15);
        $this->applyAccessScope($replacementQuery, 'equipment', $viewer);

        $replacementCandidates = $replacementQuery->get()
            ->filter(function (Equipment $item) {
                $ageMonths = $item->purchase_date?->diffInMonths(now()) ?? 0;
                $lifespan = (int) $item->expected_lifespan_months;
                return $lifespan > 0 && ($ageMonths / $lifespan) >= 0.9;
            })
            ->map(function (Equipment $item) {
                $ageMonths = $item->purchase_date?->diffInMonths(now()) ?? 0;
                return [
                    'equipment' => $item->name,
                    'type' => $item->type,
                    'age_months' => $ageMonths,
                    'lifespan_months' => (int) $item->expected_lifespan_months,
                ];
            })
            ->values()
            ->toArray();

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'count' => $equipment->count(),
                'replacement_candidates' => $replacementCandidates,
            ],
        ];
    }

    private function predictiveAnalytics(array $filters, ?User $viewer = null): array
    {
        $lookbackMonths = (int) config('reporting.predictive.lookback_months', 6);
        $forecastPeriods = (int) config('reporting.predictive.forecast_periods', 3);
        $end = now()->endOfMonth();
        $start = now()->subMonths(max(1, $lookbackMonths - 1))->startOfMonth();
        $months = $this->monthRange($start, $end);

        $revenueQuery = Invoice::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', paid_at) as month")
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('month');
        $this->applyAccessScope($revenueQuery, 'invoices', $viewer);

        $revenueMap = $revenueQuery->pluck('revenue', 'month');
        $revenueSeries = array_map(fn ($month) => (float) ($revenueMap[$month] ?? 0), $months);

        $workloadQuery = WorkOrder::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', created_at) as month")
            ->selectRaw('COUNT(*) as jobs')
            ->groupBy('month');
        $this->applyAccessScope($workloadQuery, 'work_orders', $viewer);

        $workloadMap = $workloadQuery->pluck('jobs', 'month');
        $workloadSeries = array_map(fn ($month) => (float) ($workloadMap[$month] ?? 0), $months);

        $partsQuery = WorkOrderPart::query()
            ->join('work_orders', 'work_order_parts.work_order_id', '=', 'work_orders.id')
            ->whereBetween('work_orders.completed_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', work_orders.completed_at) as month")
            ->selectRaw('SUM(work_order_parts.quantity) as parts_used')
            ->groupBy('month');
        $this->applyAccessScope($partsQuery, 'work_orders', $viewer, 'work_orders');

        $partsMap = $partsQuery->pluck('parts_used', 'month');
        $partsSeries = array_map(fn ($month) => (float) ($partsMap[$month] ?? 0), $months);

        $rows = [
            $this->buildForecastRow('Revenue', $revenueSeries, $forecastPeriods, 'currency'),
            $this->buildForecastRow('Workload (Jobs)', $workloadSeries, $forecastPeriods, 'count'),
            $this->buildForecastRow('Parts Consumption', $partsSeries, $forecastPeriods, 'count'),
        ];

        $equipmentRiskQuery = WorkOrder::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('equipment_id')
            ->select('equipment_id')
            ->selectRaw('COUNT(*) as incidents')
            ->groupBy('equipment_id')
            ->orderByDesc('incidents')
            ->limit(10);
        $this->applyAccessScope($equipmentRiskQuery, 'work_orders', $viewer);

        $equipmentRisk = $equipmentRiskQuery->get();
        $equipmentMap = Equipment::whereIn('id', $equipmentRisk->pluck('equipment_id'))->get()->keyBy('id');

        $equipmentRiskRows = $equipmentRisk->map(function ($row) use ($equipmentMap) {
            $equipment = $equipmentMap[$row->equipment_id] ?? null;
            $ageYears = $equipment?->purchase_date ? $equipment->purchase_date->diffInDays(now()) / 365 : 0;
            $riskScore = min(100, ($row->incidents * 8) + ($ageYears * 5));

            return [
                'equipment' => $equipment?->name ?? 'Unknown',
                'type' => $equipment?->type ?? '—',
                'incidents' => (int) $row->incidents,
                'age_years' => number_format((float) $ageYears, 1),
                'risk_score' => number_format((float) $riskScore, 1),
            ];
        })->toArray();

        $customerRisk = $this->customerChurnRisks($viewer);

        return [
            'columns' => ['Metric', 'Current', 'Forecast 1', 'Forecast 2', 'Forecast 3', 'Trend', 'Confidence', 'Anomaly'],
            'rows' => $rows,
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'series' => [
                    'months' => $months,
                    'revenue' => $revenueSeries,
                    'workload' => $workloadSeries,
                    'parts' => $partsSeries,
                ],
                'equipment_risk' => $equipmentRiskRows,
                'customer_churn_risk' => $customerRisk,
            ],
        ];
    }

    private function cacheKey(Report $report, array $overrides, ?User $viewer): string
    {
        $payload = [
            'report_id' => $report->id,
            'report_type' => $report->report_type,
            'definition' => $report->definition,
            'filters' => $report->filters,
            'group_by' => $report->group_by,
            'sort_by' => $report->sort_by,
            'compare' => $report->compare,
            'overrides' => $overrides,
            'viewer_id' => $viewer?->id,
            'updated_at' => $report->updated_at?->timestamp,
        ];

        return sprintf(
            '%s:%s',
            config('reporting.cache_prefix', 'reports'),
            md5(json_encode($payload))
        );
    }

    private function canViewReport(Report $report, ?User $viewer): bool
    {
        if (! $viewer) {
            return $report->is_public;
        }

        if (! $viewer->can(PermissionCatalog::REPORTS_VIEW)) {
            return false;
        }

        if ($viewer->can(PermissionCatalog::REPORTS_MANAGE)) {
            return true;
        }

        if ($report->is_public || $report->created_by_user_id === $viewer->id) {
            return true;
        }

        return ReportPermission::query()
            ->where('report_id', $report->id)
            ->where(function ($query) use ($viewer) {
                $query->where('user_id', $viewer->id)
                    ->orWhereIn('role', $viewer->roles->pluck('name'));
            })
            ->where('can_view', true)
            ->exists();
    }

    private function applyFieldPermissions(array $payload, Report $report, ?User $viewer): array
    {
        if (! $viewer) {
            return $payload;
        }

        $allowed = $this->allowedFieldsForReport($report, $viewer);
        if (! $allowed) {
            return $payload;
        }

        $columns = array_values(array_intersect($payload['columns'] ?? [], $allowed));
        $rows = collect($payload['rows'] ?? [])->map(function ($row) use ($columns) {
            return collect($row)
                ->only($columns)
                ->toArray();
        })->toArray();

        $payload['columns'] = $columns;
        $payload['rows'] = $rows;

        return $payload;
    }

    private function allowedFieldsForReport(Report $report, User $viewer): ?array
    {
        if ($viewer->can(PermissionCatalog::REPORTS_MANAGE)) {
            return null;
        }

        $permissions = ReportPermission::query()
            ->where('report_id', $report->id)
            ->where(function ($query) use ($viewer) {
                $query->where('user_id', $viewer->id)
                    ->orWhereIn('role', $viewer->roles->pluck('name'));
            })
            ->get();

        $allowed = $permissions
            ->pluck('allowed_fields')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        return $allowed !== [] ? $allowed : null;
    }

    private function applyAccessScope(Builder $query, string $source, ?User $viewer = null, ?string $prefix = null): void
    {
        if (! $viewer) {
            return;
        }

        $prefix = $prefix ? $prefix . '.' : '';

        if ($source === 'work_orders') {
            if ($viewer->can(PermissionCatalog::WORK_ORDERS_VIEW_ALL)) {
                return;
            }

            if ($viewer->can(PermissionCatalog::WORK_ORDERS_VIEW_ASSIGNED)) {
                $query->where($prefix . 'assigned_to_user_id', $viewer->id);
                return;
            }

            if ($viewer->can(PermissionCatalog::WORK_ORDERS_VIEW_ORG) && $viewer->organization_id) {
                $query->where($prefix . 'organization_id', $viewer->organization_id);
                return;
            }

            if ($viewer->can(PermissionCatalog::WORK_ORDERS_VIEW_OWN)) {
                $query->where($prefix . 'requested_by_user_id', $viewer->id);
                return;
            }

            if ($viewer->can(PermissionCatalog::WORK_ORDERS_VIEW) && $viewer->organization_id) {
                $query->where($prefix . 'organization_id', $viewer->organization_id);
                return;
            }

            $query->whereRaw('1 = 0');
            return;
        }

        if ($source === 'invoices') {
            if ($viewer->can(PermissionCatalog::BILLING_VIEW_ALL)) {
                return;
            }

            if (($viewer->can(PermissionCatalog::BILLING_VIEW_ORG) || $viewer->can(PermissionCatalog::BILLING_VIEW_OWN)) && $viewer->organization_id) {
                $query->where($prefix . 'organization_id', $viewer->organization_id);
                return;
            }

            if ($viewer->can(PermissionCatalog::BILLING_VIEW) && $viewer->organization_id) {
                $query->where($prefix . 'organization_id', $viewer->organization_id);
                return;
            }

            $query->whereRaw('1 = 0');
            return;
        }

        if ($source === 'equipment') {
            if ($viewer->can(PermissionCatalog::EQUIPMENT_VIEW_ALL)) {
                return;
            }

            if ($viewer->can(PermissionCatalog::EQUIPMENT_VIEW_ORG) && $viewer->organization_id) {
                $query->where($prefix . 'organization_id', $viewer->organization_id);
                return;
            }

            if ($viewer->can(PermissionCatalog::EQUIPMENT_VIEW_OWN)) {
                $query->where($prefix . 'assigned_user_id', $viewer->id);
                return;
            }

            if ($viewer->can(PermissionCatalog::EQUIPMENT_VIEW) && $viewer->organization_id) {
                $query->where($prefix . 'organization_id', $viewer->organization_id);
                return;
            }

            $query->whereRaw('1 = 0');
            return;
        }

        if ($source === 'parts') {
            if ($viewer->can(PermissionCatalog::INVENTORY_VIEW) || $viewer->can(PermissionCatalog::INVENTORY_MANAGE)) {
                return;
            }

            $query->whereRaw('1 = 0');
            return;
        }

        if ($source === 'organizations') {
            if ($viewer->organization_id) {
                $query->where($prefix . 'id', $viewer->organization_id);
            }
        }
    }

    private function applyCalculatedFields(array $columns, array $rows, array $calculatedFields): array
    {
        $definitions = [];

        foreach ($calculatedFields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $name = $field['name'] ?? null;
            $formula = $field['formula'] ?? null;
            if (! $name || ! $formula) {
                continue;
            }

            $definitions[] = [
                'name' => $name,
                'formula' => $formula,
                'format' => $field['format'] ?? null,
            ];
        }

        if ($definitions === []) {
            return [$columns, $rows];
        }

        foreach ($rows as $index => $row) {
            foreach ($definitions as $definition) {
                $value = $this->evaluateFormula($definition['formula'], $row);
                if ($definition['format'] === 'currency') {
                    $value = number_format($value, 2, '.', '');
                } elseif ($definition['format'] === 'percent') {
                    $value = number_format($value * 100, 2) . '%';
                }

                $rows[$index][$definition['name']] = $value;
            }
        }

        $columns = array_merge($columns, array_column($definitions, 'name'));

        return [$columns, $rows];
    }

    private function evaluateFormula(string $formula, array $row): float
    {
        $tokens = $this->tokenizeFormula($formula);
        $output = [];
        $stack = [];
        $precedence = ['+' => 1, '-' => 1, '*' => 2, '/' => 2];
        $previous = null;

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $output[] = (float) $token;
                $previous = $token;
                continue;
            }

            if ($this->isFormulaIdentifier($token)) {
                $value = data_get($row, $token, 0);
                $output[] = is_numeric($value) ? (float) $value : 0.0;
                $previous = $token;
                continue;
            }

            if ($token === '(') {
                $stack[] = $token;
                $previous = $token;
                continue;
            }

            if ($token === ')') {
                while ($stack !== [] && end($stack) !== '(') {
                    $output[] = array_pop($stack);
                }
                array_pop($stack);
                $previous = $token;
                continue;
            }

            if ($token === '-' && ($previous === null || in_array($previous, ['+', '-', '*', '/', '('], true))) {
                $output[] = 0.0;
            }

            while ($stack !== [] && isset($precedence[end($stack)]) && $precedence[end($stack)] >= $precedence[$token]) {
                $output[] = array_pop($stack);
            }
            $stack[] = $token;
            $previous = $token;
        }

        while ($stack !== []) {
            $output[] = array_pop($stack);
        }

        $eval = [];
        foreach ($output as $token) {
            if (is_numeric($token)) {
                $eval[] = (float) $token;
                continue;
            }

            $b = array_pop($eval);
            $a = array_pop($eval);

            $b = $b ?? 0.0;
            $a = $a ?? 0.0;

            $eval[] = match ($token) {
                '+' => $a + $b,
                '-' => $a - $b,
                '*' => $a * $b,
                '/' => $b != 0 ? $a / $b : 0.0,
                default => 0.0,
            };
        }

        return $eval !== [] ? (float) end($eval) : 0.0;
    }

    private function tokenizeFormula(string $formula): array
    {
        preg_match_all('/([A-Za-z_][A-Za-z0-9_\\.]*|\\d+\\.?\\d*|[\\+\\-\\*\\/\\(\\)])/', $formula, $matches);

        return $matches[0] ?? [];
    }

    private function isFormulaIdentifier(string $token): bool
    {
        return preg_match('/^[A-Za-z_][A-Za-z0-9_\\.]*$/', $token) === 1;
    }

    private function firstFixStats(array $filters, ?User $viewer = null): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(90)->startOfDay(), now()->endOfDay());

        $query = WorkOrder::query()
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('equipment_id')
            ->orderBy('completed_at');
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $orders = $query->get(['equipment_id', 'assigned_to_user_id', 'completed_at']);
        $byEquipment = $orders->groupBy('equipment_id');
        $stats = [];

        foreach ($byEquipment as $equipmentOrders) {
            $equipmentOrders = $equipmentOrders->sortBy('completed_at')->values();
            $count = $equipmentOrders->count();

            for ($i = 0; $i < $count; $i++) {
                $current = $equipmentOrders[$i];
                $next = $equipmentOrders[$i + 1] ?? null;
                $isFirstFix = true;

                if ($next && $current->completed_at && $next->completed_at) {
                    $isFirstFix = $current->completed_at->diffInDays($next->completed_at) > 14;
                }

                $techId = $current->assigned_to_user_id ?? 0;
                $stats[$techId]['total'] = ($stats[$techId]['total'] ?? 0) + 1;
                $stats[$techId]['first_fix'] = ($stats[$techId]['first_fix'] ?? 0) + ($isFirstFix ? 1 : 0);
            }
        }

        foreach ($stats as $techId => $value) {
            $stats[$techId]['rate'] = $value['total'] > 0 ? ($value['first_fix'] / $value['total']) * 100 : 0;
        }

        return $stats;
    }

    private function buildForecastRow(string $label, array $series, int $forecastPeriods, string $format): array
    {
        $current = $series !== [] ? end($series) : 0;
        $forecast = $this->forecastSeries($series, $forecastPeriods);

        return [
            'Metric' => $label,
            'Current' => $this->formatForecastValue((float) $current, $format),
            'Forecast 1' => $this->formatForecastValue((float) ($forecast[0] ?? 0), $format),
            'Forecast 2' => $this->formatForecastValue((float) ($forecast[1] ?? 0), $format),
            'Forecast 3' => $this->formatForecastValue((float) ($forecast[2] ?? 0), $format),
            'Trend' => $this->seriesTrend($series),
            'Confidence' => $this->confidenceLabel(count($series)),
            'Anomaly' => $this->detectAnomaly($series),
        ];
    }

    private function forecastSeries(array $series, int $periods): array
    {
        $count = count($series);
        if ($count === 0) {
            return array_fill(0, $periods, 0.0);
        }

        if ($count === 1) {
            return array_fill(0, $periods, (float) $series[0]);
        }

        $xSum = 0;
        $ySum = 0;
        $xySum = 0;
        $x2Sum = 0;

        foreach ($series as $index => $value) {
            $xSum += $index;
            $ySum += $value;
            $xySum += $index * $value;
            $x2Sum += $index * $index;
        }

        $denominator = ($count * $x2Sum) - ($xSum * $xSum);
        $slope = $denominator != 0 ? (($count * $xySum) - ($xSum * $ySum)) / $denominator : 0;
        $intercept = ($ySum - ($slope * $xSum)) / $count;

        $forecast = [];
        for ($i = 0; $i < $periods; $i++) {
            $x = $count + $i;
            $forecast[] = max(0, $intercept + ($slope * $x));
        }

        return $forecast;
    }

    private function seriesTrend(array $series): string
    {
        $count = count($series);
        if ($count < 2) {
            return 'Flat';
        }

        $last = (float) $series[$count - 1];
        $previous = (float) $series[$count - 2];
        $delta = $last - $previous;

        if (abs($delta) < 0.01) {
            return 'Flat';
        }

        return $delta > 0 ? 'Up' : 'Down';
    }

    private function confidenceLabel(int $points): string
    {
        if ($points >= 6) {
            return 'High';
        }

        if ($points >= 3) {
            return 'Medium';
        }

        return 'Low';
    }

    private function detectAnomaly(array $series): string
    {
        if (count($series) < 3) {
            return 'No';
        }

        $mean = array_sum($series) / count($series);
        $variance = array_sum(array_map(fn ($value) => pow($value - $mean, 2), $series)) / count($series);
        $stdDev = sqrt($variance);

        if ($stdDev == 0) {
            return 'No';
        }

        $latest = (float) end($series);
        $zScore = ($latest - $mean) / $stdDev;
        $threshold = (float) config('reporting.predictive.anomaly_zscore', 2.0);

        return abs($zScore) >= $threshold ? 'Yes' : 'No';
    }

    private function formatForecastValue(float $value, string $format): string
    {
        return match ($format) {
            'currency' => number_format($value, 2, '.', ''),
            default => number_format($value, 1, '.', ''),
        };
    }

    private function customerChurnRisks(?User $viewer = null): array
    {
        $query = WorkOrder::query()
            ->whereNotNull('organization_id')
            ->select('organization_id')
            ->selectRaw('MAX(completed_at) as last_service')
            ->groupBy('organization_id');
        $this->applyAccessScope($query, 'work_orders', $viewer);

        $rows = $query->get();
        $organizations = Organization::whereIn('id', $rows->pluck('organization_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($organizations) {
            $lastService = $row->last_service ? Carbon::parse($row->last_service) : null;
            $risk = $this->churnRiskScore($lastService);

            return [
                'customer' => $organizations[$row->organization_id]->name ?? 'Unknown',
                'last_service' => $lastService?->toDateString() ?? '—',
                'risk_score' => $risk['score'],
                'risk_label' => $risk['label'],
            ];
        })->sortByDesc('risk_score')->take(10)->values()->toArray();
    }

    private function churnRiskScore(?Carbon $lastService): array
    {
        if (! $lastService) {
            return ['score' => 90, 'label' => 'High'];
        }

        $days = $lastService->diffInDays(now());

        return match (true) {
            $days >= 180 => ['score' => 85, 'label' => 'High'],
            $days >= 90 => ['score' => 65, 'label' => 'Medium'],
            $days >= 60 => ['score' => 45, 'label' => 'Medium'],
            default => ['score' => 25, 'label' => 'Low'],
        };
    }

    private function applyComparison(array $payload, Report $report, array $overrides, ?User $viewer): array
    {
        $compare = $overrides['compare'] ?? $report->compare ?? null;
        if (! is_array($compare) || $report->report_type === 'custom') {
            return $payload;
        }

        $filters = array_merge($report->filters ?? [], $overrides);
        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            return $payload;
        }

        if (($compare['type'] ?? 'previous_period') !== 'previous_period') {
            return $payload;
        }

        $start = Carbon::parse($filters['start_date'])->startOfDay();
        $end = Carbon::parse($filters['end_date'])->endOfDay();
        $days = max(1, $start->diffInDays($end) + 1);
        $previousStart = $start->copy()->subDays($days);
        $previousEnd = $end->copy()->subDays($days);

        $previousFilters = array_merge($filters, [
            'start_date' => $previousStart->toDateString(),
            'end_date' => $previousEnd->toDateString(),
        ]);

        $previousPayload = $this->generateByType($report->report_type, $previousFilters, $viewer, $report);

        $currentMetric = $this->primaryMetric($payload);
        $previousMetric = $this->primaryMetric($previousPayload);

        if ($currentMetric === null || $previousMetric === null) {
            return $payload;
        }

        $variance = $currentMetric - $previousMetric;
        $variancePct = $previousMetric != 0 ? ($variance / $previousMetric) * 100 : 0;

        $payload['comparison'] = [
            'current' => $currentMetric,
            'previous' => $previousMetric,
            'variance' => $variance,
            'variance_pct' => number_format($variancePct, 1),
        ];

        return $payload;
    }

    private function primaryMetric(array $payload): ?float
    {
        $meta = $payload['meta'] ?? [];

        foreach (['total_revenue', 'total_cost', 'total_margin', 'total_due'] as $key) {
            if (isset($meta[$key])) {
                return (float) str_replace(',', '', (string) $meta[$key]);
            }
        }

        if (isset($meta['count'])) {
            return (float) $meta['count'];
        }

        return null;
    }

    private function resolveDateRange(array $filters, Carbon $defaultStart, Carbon $defaultEnd): array
    {
        $start = $filters['start_date'] ?? null;
        $end = $filters['end_date'] ?? null;

        return [
            $start ? Carbon::parse($start)->startOfDay() : $defaultStart,
            $end ? Carbon::parse($end)->endOfDay() : $defaultEnd,
        ];
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

    private function slaStatus(int $responseMinutes, int $target, bool $assigned): string
    {
        $threshold = (int) ceil($target * 0.8);

        if ($responseMinutes >= $target) {
            return 'breached';
        }

        if (! $assigned && $responseMinutes >= $threshold) {
            return 'at_risk';
        }

        return 'on_track';
    }

    private function monthRange(Carbon $start, Carbon $end): array
    {
        $months = [];
        $cursor = $start->copy()->startOfMonth();

        while ($cursor->lte($end)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return $months;
    }

    private function queryForSource(string $source): Builder
    {
        return match ($source) {
            'work_orders' => WorkOrder::query(),
            'invoices' => Invoice::query(),
            'equipment' => Equipment::query(),
            'parts' => Part::query(),
            'organizations' => Organization::query(),
            'payments' => Payment::query(),
            'inventory_items' => InventoryItem::query(),
            'technicians' => User::query(),
            default => WorkOrder::query(),
        };
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $filter) {
            if (! isset($filter['field'], $filter['operator'])) {
                continue;
            }

            $field = $filter['field'];
            $operator = strtolower($filter['operator']);
            $value = $filter['value'] ?? null;

            match ($operator) {
                'in' => $query->whereIn($field, (array) $value),
                'between' => $query->whereBetween($field, (array) $value),
                'null' => $query->whereNull($field),
                'not_null' => $query->whereNotNull($field),
                default => $query->where($field, $operator, $value),
            };
        }
    }

    private function normalizeFilters(array $filters): array
    {
        if ($filters === []) {
            return [];
        }

        if (array_is_list($filters)) {
            return $filters;
        }

        $normalized = [];
        foreach ($filters as $field => $value) {
            $normalized[] = [
                'field' => $field,
                'operator' => '=',
                'value' => $value,
            ];
        }

        return $normalized;
    }

    private function normalizeSort(array $sortBy): array
    {
        if ($sortBy === []) {
            return [];
        }

        if (array_is_list($sortBy)) {
            return array_map(fn ($sort) => [
                'field' => $sort['field'] ?? null,
                'direction' => $sort['direction'] ?? 'asc',
            ], $sortBy);
        }

        $normalized = [];
        foreach ($sortBy as $field => $direction) {
            $normalized[] = [
                'field' => $field,
                'direction' => $direction,
            ];
        }

        return array_filter($normalized, fn ($sort) => $sort['field']);
    }

    private function parseList(array|string $value): array
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

    private function rowToArray($row, array $columns): array
    {
        $data = [];

        foreach ($columns as $column) {
            $data[$column] = data_get($row, $column, $row->{$column} ?? null);
        }

        return $data;
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
}
