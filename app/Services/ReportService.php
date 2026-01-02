<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\Report;
use App\Models\WorkOrder;
use App\Models\WorkOrderFeedback;
use App\Models\WorkOrderPart;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    public function generateForReport(Report $report, array $overrides = []): array
    {
        if ($report->report_type !== 'custom') {
            return $this->generateByType($report->report_type, $overrides);
        }

        return $this->generateCustom($report, $overrides);
    }

    public function generateByType(string $type, array $filters = []): array
    {
        return match ($type) {
            'daily_summary' => $this->dailySummary($filters),
            'weekly_productivity' => $this->weeklyProductivity($filters),
            'monthly_performance' => $this->monthlyPerformance($filters),
            'customer_satisfaction' => $this->customerSatisfaction($filters),
            'revenue' => $this->revenueReport($filters),
            'profitability' => $this->profitabilityReport($filters),
            'accounts_receivable_aging' => $this->accountsReceivableAging($filters),
            'technician_utilization' => $this->technicianUtilization($filters),
            'first_time_fix' => $this->firstTimeFix($filters),
            'response_time' => $this->responseTime($filters),
            'schedule_adherence' => $this->scheduleAdherence($filters),
            'equipment_reliability' => $this->equipmentReliability($filters),
            'maintenance_frequency' => $this->maintenanceFrequency($filters),
            'parts_usage' => $this->partsUsage($filters),
            'lifecycle_analysis' => $this->lifecycleAnalysis($filters),
            default => $this->dailySummary($filters),
        };
    }

    private function generateCustom(Report $report, array $overrides = []): array
    {
        $definition = $report->definition ?? [];
        $fields = $this->parseList($definition['fields'] ?? []);
        $filters = $this->normalizeFilters($report->filters ?? []);
        $filters = array_merge($filters, $this->normalizeFilters($overrides));
        $groupBy = $this->parseList($report->group_by ?? []);
        $sortBy = $this->normalizeSort($report->sort_by ?? []);

        $query = $this->queryForSource($report->data_source ?? 'work_orders');
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

        $rows = $query->limit(200)->get();
        $columns = $groupBy !== []
            ? array_merge($groupBy, ['total'])
            : ($fields !== [] ? $fields : array_keys($rows->first()?->getAttributes() ?? []));

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => $this->rowToArray($row, $columns))->toArray(),
            'meta' => [
                'source' => $report->data_source,
                'count' => $rows->count(),
            ],
        ];
    }

    private function dailySummary(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->startOfDay(), now()->endOfDay());

        $orders = WorkOrder::query()
            ->with(['assignedTo', 'organization'])
            ->whereBetween('completed_at', [$start, $end])
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

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'count' => $orders->count(),
                'total_cost' => $orders->sum('total_cost'),
            ],
        ];
    }

    private function weeklyProductivity(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(7)->startOfDay(), now()->endOfDay());

        $rows = WorkOrder::query()
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('assigned_to_user_id')
            ->select('assigned_to_user_id')
            ->selectRaw('COUNT(*) as completed_jobs')
            ->selectRaw('AVG(labor_minutes) as avg_labor_minutes')
            ->selectRaw('SUM(labor_minutes) as total_labor_minutes')
            ->groupBy('assigned_to_user_id')
            ->get();

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

    private function monthlyPerformance(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(6)->startOfMonth(), now()->endOfMonth());

        $workOrders = WorkOrder::query()
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', completed_at) as month")
            ->selectRaw('COUNT(*) as completed_jobs')
            ->selectRaw('AVG(labor_minutes) as avg_labor_minutes')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $revenues = Invoice::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', paid_at) as month")
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('month')
            ->pluck('revenue', 'month');

        $ratings = WorkOrderFeedback::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', created_at) as month")
            ->selectRaw('AVG(rating) as avg_rating')
            ->groupBy('month')
            ->pluck('avg_rating', 'month');

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

    private function customerSatisfaction(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(3)->startOfDay(), now()->endOfDay());

        $rows = WorkOrderFeedback::query()
            ->join('work_orders', 'work_order_feedback.work_order_id', '=', 'work_orders.id')
            ->whereBetween('work_order_feedback.created_at', [$start, $end])
            ->select('work_orders.assigned_to_user_id')
            ->selectRaw('AVG(work_order_feedback.rating) as avg_rating')
            ->selectRaw('COUNT(*) as responses')
            ->groupBy('work_orders.assigned_to_user_id')
            ->get();

        $technicianNames = User::whereIn('id', $rows->pluck('assigned_to_user_id'))
            ->pluck('name', 'id');

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
            ],
        ];
    }

    private function revenueReport(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(6)->startOfMonth(), now()->endOfMonth());

        $rows = Invoice::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw("strftime('%Y-%m', paid_at) as month")
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $columns = ['Month', 'Revenue'];

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => [
                'Month' => $row->month,
                'Revenue' => number_format((float) $row->revenue, 2, '.', ''),
            ])->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function profitabilityReport(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subMonths(3)->startOfDay(), now()->endOfDay());

        $orders = WorkOrder::query()
            ->with(['invoices', 'parts', 'organization'])
            ->whereBetween('completed_at', [$start, $end])
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        $columns = ['Work Order', 'Customer', 'Revenue', 'Parts Cost', 'Margin'];

        $rows = $orders->map(function (WorkOrder $order) {
            $revenue = $order->invoices->sum('total');
            $partsCost = $order->parts->sum(fn ($part) => $part->quantity * $part->unit_cost);

            return [
                'Work Order' => $order->id,
                'Customer' => $order->organization?->name ?? '—',
                'Revenue' => number_format((float) $revenue, 2, '.', ''),
                'Parts Cost' => number_format((float) $partsCost, 2, '.', ''),
                'Margin' => number_format((float) ($revenue - $partsCost), 2, '.', ''),
            ];
        });

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'count' => $orders->count(),
                'total_margin' => $rows->sum(fn ($row) => (float) $row['Margin']),
            ],
        ];
    }

    private function accountsReceivableAging(array $filters): array
    {
        $today = Carbon::today();

        $invoices = Invoice::query()
            ->whereNull('paid_at')
            ->get();

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

    private function technicianUtilization(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(30)->startOfDay(), now()->endOfDay());

        $rows = WorkOrder::query()
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('assigned_to_user_id')
            ->select('assigned_to_user_id')
            ->selectRaw('COUNT(*) as completed_jobs')
            ->selectRaw('SUM(labor_minutes) as total_labor')
            ->selectRaw('SUM(travel_minutes) as total_travel')
            ->groupBy('assigned_to_user_id')
            ->get();

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

    private function firstTimeFix(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(90)->startOfDay(), now()->endOfDay());

        $orders = WorkOrder::query()
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('equipment_id')
            ->orderBy('completed_at')
            ->get(['id', 'equipment_id', 'assigned_to_user_id', 'completed_at']);

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

    private function responseTime(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(60)->startOfDay(), now()->endOfDay());

        $rows = WorkOrder::query()
            ->whereBetween('requested_at', [$start, $end])
            ->whereNotNull('assigned_at')
            ->select('priority')
            ->selectRaw("AVG((julianday(assigned_at) - julianday(requested_at)) * 24 * 60) as avg_minutes")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('priority')
            ->get();

        $columns = ['Priority', 'Avg Response Minutes', 'Requests'];

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => [
                'Priority' => ucfirst($row->priority),
                'Avg Response Minutes' => number_format((float) $row->avg_minutes, 1),
                'Requests' => (int) $row->total,
            ])->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function scheduleAdherence(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(30)->startOfDay(), now()->endOfDay());

        $orders = WorkOrder::query()
            ->whereBetween('scheduled_start_at', [$start, $end])
            ->whereNotNull('scheduled_start_at')
            ->whereNotNull('started_at')
            ->get(['assigned_to_user_id', 'scheduled_start_at', 'started_at']);

        $stats = [];

        foreach ($orders as $order) {
            $techId = $order->assigned_to_user_id ?? 0;
            $diff = $order->scheduled_start_at->diffInMinutes($order->started_at);
            $stats[$techId]['total'] = ($stats[$techId]['total'] ?? 0) + 1;
            $stats[$techId]['total_diff'] = ($stats[$techId]['total_diff'] ?? 0) + $diff;
            $stats[$techId]['on_time'] = ($stats[$techId]['on_time'] ?? 0) + ($diff <= 15 ? 1 : 0);
        }

        $technicianNames = User::whereIn('id', array_keys($stats))
            ->pluck('name', 'id');

        $columns = ['Technician', 'Appointments', 'Avg Variance Minutes', 'On-Time Rate'];
        $rows = collect($stats)->map(function ($value, $techId) use ($technicianNames) {
            $avgDiff = $value['total'] > 0 ? $value['total_diff'] / $value['total'] : 0;
            $rate = $value['total'] > 0 ? ($value['on_time'] / $value['total']) * 100 : 0;

            return [
                'Technician' => $technicianNames[$techId] ?? 'Unassigned',
                'Appointments' => $value['total'],
                'Avg Variance Minutes' => number_format($avgDiff, 1),
                'On-Time Rate' => number_format($rate, 1) . '%',
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

    private function equipmentReliability(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subYear()->startOfDay(), now()->endOfDay());

        $rows = WorkOrder::query()
            ->join('equipment', 'work_orders.equipment_id', '=', 'equipment.id')
            ->whereBetween('work_orders.created_at', [$start, $end])
            ->select('equipment.type', 'equipment.model')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('equipment.type', 'equipment.model')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

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
            ],
        ];
    }

    private function maintenanceFrequency(array $filters): array
    {
        $equipment = Equipment::query()
            ->withCount('workOrders')
            ->withMax('workOrders', 'completed_at')
            ->orderByDesc('work_orders_count')
            ->limit(20)
            ->get();

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
            ],
        ];
    }

    private function partsUsage(array $filters): array
    {
        [$start, $end] = $this->resolveDateRange($filters, now()->subDays(90)->startOfDay(), now()->endOfDay());

        $rows = WorkOrderPart::query()
            ->join('parts', 'work_order_parts.part_id', '=', 'parts.id')
            ->join('work_orders', 'work_order_parts.work_order_id', '=', 'work_orders.id')
            ->whereBetween('work_orders.completed_at', [$start, $end])
            ->select('parts.name')
            ->selectRaw('SUM(work_order_parts.quantity) as total_used')
            ->selectRaw('SUM(work_order_parts.quantity * work_order_parts.unit_cost) as total_cost')
            ->groupBy('parts.name')
            ->orderByDesc('total_used')
            ->limit(20)
            ->get();

        $columns = ['Part', 'Units Used', 'Total Cost'];

        return [
            'columns' => $columns,
            'rows' => $rows->map(fn ($row) => [
                'Part' => $row->name,
                'Units Used' => (int) $row->total_used,
                'Total Cost' => number_format((float) $row->total_cost, 2, '.', ''),
            ])->toArray(),
            'meta' => [
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
            ],
        ];
    }

    private function lifecycleAnalysis(array $filters): array
    {
        $equipment = Equipment::query()
            ->withCount('workOrders')
            ->withSum('workOrders', 'total_cost')
            ->orderByDesc('work_orders_sum_total_cost')
            ->limit(20)
            ->get();

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

        return [
            'columns' => $columns,
            'rows' => $rows->toArray(),
            'meta' => [
                'count' => $equipment->count(),
            ],
        ];
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
}
