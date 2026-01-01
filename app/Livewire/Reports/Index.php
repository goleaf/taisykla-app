<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderFeedback;
use Livewire\Component;

class Index extends Component
{
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

        return view('livewire.reports.index', [
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'averageRating' => $averageRating,
            'statusCounts' => $statusCounts,
            'categoryCounts' => $categoryCounts,
        ]);
    }
}
