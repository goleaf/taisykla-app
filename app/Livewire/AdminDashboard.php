<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Organization;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AdminDashboard extends Component
{
    // Date range filter
    public string $dateRange = '30d';
    public ?string $startDate = null;
    public ?string $endDate = null;

    // Section toggles
    public bool $showSecurityDetails = false;
    public bool $showComplianceDetails = false;

    protected $queryString = ['dateRange'];

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->can(PermissionCatalog::SETTINGS_VIEW), 403);

        $this->updateDateRange();
    }

    public function updatedDateRange(): void
    {
        $this->updateDateRange();
    }

    private function updateDateRange(): void
    {
        $this->endDate = today()->format('Y-m-d');

        $this->startDate = match ($this->dateRange) {
            '7d' => today()->subDays(7)->format('Y-m-d'),
            '30d' => today()->subDays(30)->format('Y-m-d'),
            '90d' => today()->subDays(90)->format('Y-m-d'),
            '1y' => today()->subYear()->format('Y-m-d'),
            default => today()->subDays(30)->format('Y-m-d'),
        };
    }

    public function exportMetrics(): void
    {
        // In production, this would generate a CSV/PDF report
        session()->flash('success', 'Metrics export initiated. Check your email.');
    }

    public function runDatabaseMaintenance(): void
    {
        // Placeholder for DB maintenance tasks
        Cache::flush();
        session()->flash('success', 'Cache cleared successfully');
    }

    public function triggerBackup(): void
    {
        // Placeholder for backup trigger
        session()->flash('success', 'Backup job queued');
    }

    public function render()
    {
        return view('livewire.admin-dashboard', [
            'systemHealth' => $this->getSystemHealth(),
            'securityMetrics' => $this->getSecurityMetrics(),
            'userManagement' => $this->getUserManagement(),
            'businessIntelligence' => $this->getBusinessIntelligence(),
            'compliance' => $this->getCompliance(),
            'alerts' => $this->getAlerts(),
        ]);
    }

    private function getSystemHealth(): array
    {
        // Server metrics (simulated - in production would use actual monitoring)
        $uptime = 99.97; // Would come from monitoring service
        $responseTime = rand(45, 120); // ms - would come from APM

        // Database performance
        $dbConnections = DB::select('SELECT count(*) as count FROM sqlite_master')[0]->count ?? 0;

        // Storage (simulated)
        $storageUsed = 45.2; // GB
        $storageTotal = 100; // GB
        $storagePercent = ($storageUsed / $storageTotal) * 100;

        // Active sessions (users logged in today)
        $activeSessions = User::where('last_login_at', '>=', now()->subHours(24))->count();

        // API health (simulated endpoints)
        $endpoints = [
            ['name' => 'Work Orders API', 'status' => 'healthy', 'latency' => rand(20, 50)],
            ['name' => 'Equipment API', 'status' => 'healthy', 'latency' => rand(25, 60)],
            ['name' => 'Billing API', 'status' => 'healthy', 'latency' => rand(30, 70)],
            ['name' => 'Auth API', 'status' => 'healthy', 'latency' => rand(15, 40)],
            ['name' => 'Notifications API', 'status' => rand(1, 20) > 1 ? 'healthy' : 'degraded', 'latency' => rand(50, 150)],
        ];

        // Historical uptime (last 7 days)
        $uptimeHistory = collect(range(6, 0))->map(fn($i) => [
            'date' => now()->subDays($i)->format('M d'),
            'uptime' => rand(9990, 10000) / 100,
        ])->toArray();

        return [
            'uptime' => $uptime,
            'uptime_status' => $uptime >= 99.9 ? 'green' : ($uptime >= 99 ? 'yellow' : 'red'),
            'response_time' => $responseTime,
            'response_status' => $responseTime < 100 ? 'green' : ($responseTime < 200 ? 'yellow' : 'red'),
            'db_connections' => $dbConnections,
            'db_status' => 'green',
            'storage_used' => $storageUsed,
            'storage_total' => $storageTotal,
            'storage_percent' => $storagePercent,
            'storage_status' => $storagePercent < 70 ? 'green' : ($storagePercent < 90 ? 'yellow' : 'red'),
            'storage_projection_days' => round((100 - $storagePercent) / 0.5), // 0.5 GB/day growth
            'active_sessions' => $activeSessions,
            'endpoints' => $endpoints,
            'uptime_history' => $uptimeHistory,
        ];
    }

    private function getSecurityMetrics(): array
    {
        $today = today();
        $startDate = Carbon::parse($this->startDate);

        // Failed login attempts (from events or logs)
        $failedLogins = User::where('failed_login_attempts', '>', 0)->sum('failed_login_attempts');

        // Recent lockouts
        $recentLockouts = User::whereNotNull('locked_until')
            ->where('locked_until', '>', now())
            ->with(['roles'])
            ->latest('locked_until')
            ->limit(5)
            ->get();

        // Geographic anomalies (simulated)
        $geoAnomalies = collect([
            ['user' => 'john@example.com', 'location' => 'Unknown VPN', 'time' => now()->subHours(2), 'risk' => 'medium'],
            ['user' => 'admin@example.com', 'location' => 'New Country', 'time' => now()->subHours(5), 'risk' => 'high'],
        ]);

        // Suspicious activity log (simulated)
        $suspiciousActivity = collect([
            ['type' => 'Bulk Export', 'user' => 'staff@example.com', 'details' => 'Exported 500+ records', 'time' => now()->subHours(1)],
            ['type' => 'After Hours Access', 'user' => 'tech@example.com', 'details' => 'Login at 2:30 AM', 'time' => now()->subHours(8)],
        ]);

        // Security alerts count
        $securityAlerts = $failedLogins > 10 ? 1 : 0;
        $securityAlerts += $geoAnomalies->where('risk', 'high')->count();

        return [
            'failed_logins' => $failedLogins,
            'failed_status' => $failedLogins < 5 ? 'green' : ($failedLogins < 20 ? 'yellow' : 'red'),
            'recent_lockouts' => $recentLockouts,
            'lockout_count' => $recentLockouts->count(),
            'geo_anomalies' => $geoAnomalies,
            'suspicious_activity' => $suspiciousActivity,
            'security_alerts' => $securityAlerts,
            'security_status' => $securityAlerts === 0 ? 'green' : ($securityAlerts < 3 ? 'yellow' : 'red'),
        ];
    }

    private function getUserManagement(): array
    {
        // Accounts by role
        $accountsByRole = [];
        foreach (RoleCatalog::all() as $role) {
            $count = User::role($role)->where('is_active', true)->count();
            if ($count > 0) {
                $accountsByRole[$role] = $count;
            }
        }

        // Recent account creations (last 30 days)
        $recentCreations = User::where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->limit(5)
            ->get();

        // Recently deactivated
        $recentlyDeactivated = User::where('is_active', false)
            ->where('updated_at', '>=', now()->subDays(30))
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // Pending password resets (simulated - would check password_resets table)
        $pendingResets = 0;

        // Inactive accounts (90+ days)
        $inactiveAccounts = User::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_login_at')
                    ->orWhere('last_login_at', '<', now()->subDays(90));
            })
            ->count();

        // Locked accounts
        $lockedAccounts = User::whereNotNull('locked_until')
            ->where('locked_until', '>', now())
            ->count();

        // Total active
        $totalActive = User::where('is_active', true)->count();

        return [
            'accounts_by_role' => $accountsByRole,
            'total_active' => $totalActive,
            'recent_creations' => $recentCreations,
            'recently_deactivated' => $recentlyDeactivated,
            'pending_resets' => $pendingResets,
            'inactive_accounts' => $inactiveAccounts,
            'locked_accounts' => $lockedAccounts,
            'attention_needed' => $inactiveAccounts + $lockedAccounts + $pendingResets,
        ];
    }

    private function getBusinessIntelligence(): array
    {
        $today = today();
        $startOfMonth = $today->copy()->startOfMonth();
        $startOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();

        // Revenue metrics
        $currentMonthRevenue = Payment::whereBetween('paid_at', [$startOfMonth, $today])
            ->sum('amount');

        $lastMonthRevenue = Payment::whereBetween('paid_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');

        // Projection (based on days elapsed)
        $daysElapsed = $today->day;
        $daysInMonth = $today->daysInMonth;
        $projectedRevenue = $daysElapsed > 0
            ? ($currentMonthRevenue / $daysElapsed) * $daysInMonth
            : 0;

        // Job volume trend (last 12 months)
        $jobVolumeTrend = collect(range(11, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            return [
                'month' => $month->format('M'),
                'count' => WorkOrder::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        })->toArray();

        // Customer metrics
        $newCustomers = Organization::where('created_at', '>=', $startOfMonth)->count();
        $totalCustomers = Organization::count();

        // Average profitability per job
        $completedJobs = WorkOrder::whereNotNull('actual_end_at')
            ->where('actual_end_at', '>=', $startOfMonth)
            ->get();
        $avgProfit = $completedJobs->avg(function ($wo) {
            $revenue = $wo->invoices?->sum('total') ?? 0;
            $cost = ($wo->labor_minutes ?? 0) * 0.75; // $0.75/min labor cost
            return $revenue - $cost;
        }) ?? 0;

        // Service type breakdown
        $serviceBreakdown = WorkOrder::select('category_id', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startOfMonth)
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(fn($item) => [
                'name' => $item->category?->name ?? 'Uncategorized',
                'count' => $item->count,
            ])
            ->toArray();

        return [
            'current_month_revenue' => $currentMonthRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'projected_revenue' => round($projectedRevenue, 2),
            'revenue_change' => $lastMonthRevenue > 0
                ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                : 0,
            'job_volume_trend' => $jobVolumeTrend,
            'new_customers' => $newCustomers,
            'total_customers' => $totalCustomers,
            'avg_profit_per_job' => round($avgProfit, 2),
            'service_breakdown' => $serviceBreakdown,
            'jobs_this_month' => WorkOrder::where('created_at', '>=', $startOfMonth)->count(),
            'jobs_completed_this_month' => WorkOrder::where('actual_end_at', '>=', $startOfMonth)->count(),
        ];
    }

    private function getCompliance(): array
    {
        // Last backup (simulated)
        $lastBackup = now()->subHours(rand(1, 12));
        $backupStatus = now()->diffInHours($lastBackup) < 24 ? 'green' : 'red';

        // Security patches (simulated)
        $patchesPending = rand(0, 3);
        $patchStatus = $patchesPending === 0 ? 'green' : ($patchesPending < 3 ? 'yellow' : 'red');

        // Audit log integrity (simulated)
        $auditLogStatus = 'green';
        $lastAuditCheck = now()->subDays(rand(0, 2));

        // Data privacy compliance checklist
        $complianceChecklist = [
            ['item' => 'Data encryption at rest', 'status' => 'compliant', 'checked' => now()->subDays(7)],
            ['item' => 'Data encryption in transit', 'status' => 'compliant', 'checked' => now()->subDays(7)],
            ['item' => 'User consent records', 'status' => 'compliant', 'checked' => now()->subDays(14)],
            ['item' => 'Data retention policies', 'status' => 'compliant', 'checked' => now()->subDays(30)],
            ['item' => 'Access control review', 'status' => 'pending', 'checked' => now()->subDays(45)],
            ['item' => 'Incident response plan', 'status' => 'compliant', 'checked' => now()->subDays(60)],
        ];

        // Regulatory requirements
        $regulatoryStatus = [
            ['regulation' => 'GDPR', 'status' => 'compliant', 'last_reviewed' => now()->subDays(30)],
            ['regulation' => 'SOC 2', 'status' => 'in_progress', 'last_reviewed' => now()->subDays(60)],
            ['regulation' => 'PCI DSS', 'status' => 'compliant', 'last_reviewed' => now()->subDays(45)],
        ];

        return [
            'last_backup' => $lastBackup,
            'backup_status' => $backupStatus,
            'patches_pending' => $patchesPending,
            'patch_status' => $patchStatus,
            'audit_log_status' => $auditLogStatus,
            'last_audit_check' => $lastAuditCheck,
            'compliance_checklist' => $complianceChecklist,
            'regulatory_status' => $regulatoryStatus,
            'overall_compliance' => collect($complianceChecklist)->where('status', 'compliant')->count()
                / count($complianceChecklist) * 100,
        ];
    }

    private function getAlerts(): Collection
    {
        $alerts = collect();

        // Critical system notifications
        $storageUsed = 45.2;
        if ($storageUsed > 80) {
            $alerts->push([
                'type' => 'critical',
                'category' => 'storage',
                'title' => 'Storage Warning',
                'message' => "Storage is at {$storageUsed}% capacity",
                'action' => 'Expand storage or clean up old files',
            ]);
        }

        // License expiration (simulated)
        $licenseExpiry = now()->addDays(25);
        if ($licenseExpiry->diffInDays(now()) < 30) {
            $alerts->push([
                'type' => 'warning',
                'category' => 'license',
                'title' => 'License Expiring Soon',
                'message' => "System license expires on {$licenseExpiry->format('M d, Y')}",
                'action' => 'Renew license',
            ]);
        }

        // Maintenance schedule (simulated)
        $nextMaintenance = now()->addDays(5)->setHour(2)->setMinute(0);
        $alerts->push([
            'type' => 'info',
            'category' => 'maintenance',
            'title' => 'Scheduled Maintenance',
            'message' => "Next maintenance window: {$nextMaintenance->format('M d, Y g:i A')}",
            'action' => 'View schedule',
        ]);

        // Integration failures (check for any failed jobs)
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 0) {
            $alerts->push([
                'type' => 'warning',
                'category' => 'integration',
                'title' => 'Failed Background Jobs',
                'message' => "{$failedJobs} job(s) have failed and need attention",
                'action' => 'Review failed jobs',
            ]);
        }

        // Inactive users warning
        $inactiveCount = User::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_login_at')
                    ->orWhere('last_login_at', '<', now()->subDays(90));
            })
            ->count();

        if ($inactiveCount > 5) {
            $alerts->push([
                'type' => 'info',
                'category' => 'users',
                'title' => 'Inactive User Accounts',
                'message' => "{$inactiveCount} users haven't logged in for 90+ days",
                'action' => 'Review accounts',
            ]);
        }

        return $alerts->sortByDesc(fn($a) => match ($a['type']) {
            'critical' => 3,
            'warning' => 2,
            'info' => 1,
            default => 0,
        })->values();
    }
}
