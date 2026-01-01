<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\MessageThread;
use App\Models\Organization;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $role = $user->getRoleNames()->first() ?? 'user';
        $metrics = [];
        $workOrders = collect();
        $appointments = collect();
        $tickets = collect();
        $invoices = collect();
        $equipment = collect();
        $threads = collect();

        $today = Carbon::today();

        if ($user->hasRole('admin')) {
            $metrics = [
                'work_orders_total' => WorkOrder::count(),
                'work_orders_open' => WorkOrder::whereIn('status', ['submitted', 'assigned', 'in_progress', 'on_hold'])->count(),
                'organizations' => Organization::count(),
                'technicians' => User::role('technician')->count(),
                'revenue_month' => Invoice::whereNotNull('paid_at')
                    ->where('paid_at', '>=', $today->copy()->startOfMonth())
                    ->sum('total'),
            ];

            $workOrders = WorkOrder::latest()->take(6)->get();
            $appointments = Appointment::whereDate('scheduled_start_at', $today)
                ->with(['workOrder', 'assignedTo'])
                ->orderBy('scheduled_start_at')
                ->take(6)
                ->get();
            $threads = MessageThread::latest()->take(5)->get();
        } elseif ($user->hasRole('dispatch')) {
            $metrics = [
                'queue' => WorkOrder::where('status', 'submitted')->count(),
                'unassigned' => WorkOrder::whereNull('assigned_to_user_id')
                    ->whereIn('status', ['submitted', 'assigned'])
                    ->count(),
                'today_appointments' => Appointment::whereDate('scheduled_start_at', $today)->count(),
                'overdue' => WorkOrder::whereIn('status', ['assigned', 'in_progress', 'on_hold'])
                    ->whereNotNull('scheduled_end_at')
                    ->where('scheduled_end_at', '<', now())
                    ->count(),
            ];

            $workOrders = WorkOrder::whereIn('status', ['submitted', 'assigned'])
                ->orderBy('priority')
                ->latest()
                ->take(8)
                ->get();
            $appointments = Appointment::whereDate('scheduled_start_at', $today)
                ->with(['workOrder', 'assignedTo'])
                ->orderBy('scheduled_start_at')
                ->take(8)
                ->get();
        } elseif ($user->hasRole('technician')) {
            $metrics = [
                'assigned_today' => Appointment::whereDate('scheduled_start_at', $today)
                    ->where('assigned_to_user_id', $user->id)
                    ->count(),
                'open_work_orders' => WorkOrder::where('assigned_to_user_id', $user->id)
                    ->whereIn('status', ['assigned', 'in_progress', 'on_hold'])
                    ->count(),
            ];

            $appointments = Appointment::where('assigned_to_user_id', $user->id)
                ->orderBy('scheduled_start_at')
                ->take(6)
                ->get();
            $workOrders = WorkOrder::where('assigned_to_user_id', $user->id)
                ->latest()
                ->take(6)
                ->get();
        } elseif ($user->hasRole('support')) {
            $metrics = [
                'open_tickets' => SupportTicket::where('status', 'open')->count(),
                'in_review' => SupportTicket::where('status', 'in_review')->count(),
                'recent_completions' => WorkOrder::where('status', 'completed')->count(),
            ];

            $tickets = SupportTicket::latest()->take(6)->get();
            $workOrders = WorkOrder::where('status', 'completed')->latest()->take(6)->get();
        } else {
            $organizationId = $user->organization_id;
            $metrics = [
                'active_work_orders' => WorkOrder::where('organization_id', $organizationId)
                    ->whereIn('status', ['submitted', 'assigned', 'in_progress', 'on_hold'])
                    ->count(),
                'equipment' => Equipment::where('organization_id', $organizationId)->count(),
                'open_invoices' => Invoice::where('organization_id', $organizationId)
                    ->whereIn('status', ['sent', 'overdue'])
                    ->count(),
            ];

            $workOrders = WorkOrder::where('organization_id', $organizationId)->latest()->take(6)->get();
            $equipment = Equipment::where('organization_id', $organizationId)->latest()->take(6)->get();
            $invoices = Invoice::where('organization_id', $organizationId)->latest()->take(6)->get();
            $threads = MessageThread::where('organization_id', $organizationId)->latest()->take(5)->get();
        }

        return view('livewire.dashboard', [
            'user' => $user,
            'role' => $role,
            'metrics' => $metrics,
            'workOrders' => $workOrders,
            'appointments' => $appointments,
            'tickets' => $tickets,
            'invoices' => $invoices,
            'equipment' => $equipment,
            'threads' => $threads,
        ]);
    }
}
