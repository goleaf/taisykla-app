<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\Part;
use App\Models\WorkOrder;
use App\Services\RouteOptimizationService;
use App\Support\PermissionCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class TechnicianDashboard extends Component
{
    // Status tracking
    public string $currentStatus = 'available';
    public ?int $activeJobId = null;
    public ?Carbon $jobStartTime = null;
    public int $elapsedSeconds = 0;

    // UI state
    public ?int $expandedJobId = null;
    public string $replyMessage = '';
    public ?int $replyToMessageId = null;
    public bool $showEmergencyModal = false;
    public string $emergencyNote = '';

    // Timer tracking
    public array $timeSummary = [
        'work' => 0,
        'travel' => 0,
        'break' => 0,
        'billable' => 0,
    ];

    protected $listeners = ['jobReordered' => 'handleJobReorder'];

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->can(PermissionCatalog::DASHBOARD_VIEW), 403);

        $this->currentStatus = $user->availability_status ?? 'available';
        $this->loadTimeSummary();

        // Check for active job
        $activeJob = $this->getActiveJob();
        if ($activeJob) {
            $this->activeJobId = $activeJob->id;
            $this->jobStartTime = $activeJob->actual_start_at;
        }
    }

    public function updateStatus(string $status): void
    {
        $validStatuses = ['available', 'traveling', 'on_site', 'working', 'break', 'off_duty'];
        if (!in_array($status, $validStatuses)) {
            return;
        }

        $user = auth()->user();
        $previousStatus = $user->availability_status;

        // Track time for previous status
        $this->trackStatusTime($previousStatus, $status);

        $user->update(['availability_status' => $status]);
        $this->currentStatus = $status;

        $this->dispatch('status-updated', status: $status);
    }

    public function checkIn(int $workOrderId): void
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);

        // Ensure it's assigned to this user
        abort_unless($workOrder->assigned_to_user_id === auth()->id(), 403);

        $workOrder->update([
            'status' => 'in_progress',
            'actual_start_at' => now(),
        ]);

        // Create event
        $workOrder->events()->create([
            'user_id' => auth()->id(),
            'type' => 'checked_in',
            'note' => 'Technician arrived on site',
        ]);

        $this->activeJobId = $workOrderId;
        $this->jobStartTime = now();
        $this->updateStatus('on_site');
    }

    public function checkOut(int $workOrderId): void
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        abort_unless($workOrder->assigned_to_user_id === auth()->id(), 403);

        $workOrder->update([
            'status' => 'completed',
            'actual_end_at' => now(),
            'labor_minutes' => $this->jobStartTime
                ? now()->diffInMinutes($this->jobStartTime)
                : null,
        ]);

        $workOrder->events()->create([
            'user_id' => auth()->id(),
            'type' => 'checked_out',
            'note' => 'Work completed',
        ]);

        $this->activeJobId = null;
        $this->jobStartTime = null;
        $this->elapsedSeconds = 0;
        $this->updateStatus('available');
    }

    public function startTravel(int $workOrderId): void
    {
        $this->updateStatus('traveling');

        $workOrder = WorkOrder::findOrFail($workOrderId);
        $workOrder->events()->create([
            'user_id' => auth()->id(),
            'type' => 'travel_started',
            'note' => 'En route to location',
        ]);
    }

    public function toggleJobExpand(int $jobId): void
    {
        $this->expandedJobId = $this->expandedJobId === $jobId ? null : $jobId;
    }

    public function sendQuickReply(): void
    {
        if (empty(trim($this->replyMessage)) || !$this->replyToMessageId) {
            return;
        }

        $originalMessage = Message::findOrFail($this->replyToMessageId);

        $thread = $originalMessage->thread;

        Message::create([
            'thread_id' => $originalMessage->thread_id,
            'user_id' => auth()->id(),
            'sender_id' => auth()->id(),
            'subject' => $thread?->subject,
            'body' => $this->replyMessage,
            'timestamp' => now(),
            'message_type' => $thread?->type ?? 'direct',
            'channel' => 'in_app',
            'related_work_order_id' => $thread?->work_order_id,
        ]);

        $this->replyMessage = '';
        $this->replyToMessageId = null;
    }

    public function reservePart(int $partId, int $quantity = 1): void
    {
        // Reserve parts for current job
        if (!$this->activeJobId) {
            session()->flash('error', 'No active job to reserve parts for');
            return;
        }

        $part = Part::findOrFail($partId);

        // Check availability
        $available = $part->inventoryItems()->sum('quantity');
        if ($available < $quantity) {
            session()->flash('error', 'Insufficient stock');
            return;
        }

        // Create reservation (work_order_parts)
        $workOrder = WorkOrder::find($this->activeJobId);
        $workOrder->parts()->attach($partId, [
            'quantity' => $quantity,
            'unit_cost' => $part->unit_cost,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session()->flash('success', "Reserved {$quantity}x {$part->name}");
    }

    public function triggerEmergency(): void
    {
        $this->showEmergencyModal = true;
    }

    public function sendEmergencyAlert(): void
    {
        $user = auth()->user();

        // Create high-priority message to dispatch
        // In real implementation, this would trigger SMS/push notifications

        $user->update(['availability_status' => 'emergency']);

        // Log emergency event
        if ($this->activeJobId) {
            WorkOrder::find($this->activeJobId)?->events()->create([
                'user_id' => auth()->id(),
                'type' => 'emergency_triggered',
                'note' => $this->emergencyNote ?: 'Emergency alert triggered',
            ]);
        }

        $this->showEmergencyModal = false;
        $this->emergencyNote = '';

        session()->flash('alert', 'Emergency alert sent to dispatch');
    }

    public function handleJobReorder(array $newOrder): void
    {
        // Update route sequence (would integrate with RouteOptimizationService)
        foreach ($newOrder as $index => $jobId) {
            WorkOrder::where('id', $jobId)
                ->where('assigned_to_user_id', auth()->id())
                ->update(['route_sequence' => $index + 1]);
        }
    }

    public function updateLocation(): void
    {
        // This would be called periodically from JavaScript with GPS coords
        // For now, it's a placeholder
    }

    #[On('location-updated')]
    public function handleLocationUpdate(float $lat, float $lng): void
    {
        auth()->user()->update([
            'current_latitude' => $lat,
            'current_longitude' => $lng,
        ]);
    }

    private function getActiveJob(): ?WorkOrder
    {
        return WorkOrder::where('assigned_to_user_id', auth()->id())
            ->where('status', 'in_progress')
            ->first();
    }

    private function loadTimeSummary(): void
    {
        // Calculate today's time breakdown from events/logs
        $today = today();

        // This is a simplified version - real implementation would use time tracking events
        $this->timeSummary = [
            'work' => rand(120, 360), // Placeholder
            'travel' => rand(30, 90),
            'break' => rand(15, 45),
            'billable' => rand(100, 300),
        ];
    }

    private function trackStatusTime(string $from, string $to): void
    {
        // Track time spent in each status for reporting
        // Real implementation would persist this to database
    }

    public function render()
    {
        $user = auth()->user();

        // Today's work queue
        $workQueue = $this->getWorkQueue();

        // Optimize route
        $routeService = app(RouteOptimizationService::class);
        $optimizedRoute = $routeService->optimizeRoute($user);
        $routeSummary = $routeService->getRouteSummary($optimizedRoute);
        $conflicts = $routeService->detectConflicts($optimizedRoute);

        // Prepare map stops
        $mapStops = $optimizedRoute->map(fn($wo) => [
            'id' => $wo->id,
            'sequence' => $wo->route_sequence ?? 0,
            'label' => $wo->organization?->name ?? 'Customer',
            'address' => $wo->location_address,
            'lat' => $wo->location_latitude,
            'lng' => $wo->location_longitude,
            'priority' => $wo->priority,
            'scheduled' => $wo->scheduled_start_at?->format('g:i A'),
            'eta' => $wo->estimated_arrival?->format('g:i A'),
        ])->toArray();

        // Messages
        $messages = $this->getRecentMessages();
        $unreadCount = $this->getUnreadMessageCount();

        // Parts inventory
        $commonParts = $this->getCommonParts();

        // Alerts
        $alerts = $this->getAlerts();

        return view('livewire.technician-dashboard', [
            'workQueue' => $workQueue,
            'optimizedRoute' => $optimizedRoute,
            'routeSummary' => $routeSummary,
            'conflicts' => $conflicts,
            'mapStops' => $mapStops,
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'commonParts' => $commonParts,
            'alerts' => $alerts,
            'currentLocation' => [
                'lat' => $user->current_latitude,
                'lng' => $user->current_longitude,
            ],
        ]);
    }

    private function getWorkQueue(): Collection
    {
        return WorkOrder::with(['organization', 'equipment', 'category', 'events', 'parts'])
            ->where('assigned_to_user_id', auth()->id())
            ->whereDate('scheduled_start_at', today())
            ->whereIn('status', ['assigned', 'in_progress'])
            ->orderByRaw("CASE priority 
                WHEN 'urgent' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'standard' THEN 3 
                ELSE 4 END")
            ->orderBy('scheduled_start_at')
            ->get();
    }

    private function getRecentMessages(): Collection
    {
        return Message::whereHas('thread.participants', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->with(['user', 'thread.workOrder'])
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getUnreadMessageCount(): int
    {
        return Message::whereHas('thread.participants', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->where('user_id', '!=', auth()->id())
            ->where('created_at', '>', auth()->user()->last_seen_at ?? now()->subDay())
            ->count();
    }

    private function getCommonParts(): Collection
    {
        // Get frequently used parts
        return Part::withSum('inventoryItems', 'quantity')
            ->orderByDesc('inventory_items_sum_quantity')
            ->limit(8)
            ->get();
    }

    private function getAlerts(): Collection
    {
        $alerts = collect();

        // Check for urgent work orders
        $urgentCount = WorkOrder::where('assigned_to_user_id', auth()->id())
            ->where('priority', 'urgent')
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();

        if ($urgentCount > 0) {
            $alerts->push([
                'type' => 'urgent',
                'message' => "You have {$urgentCount} urgent job(s) today",
                'icon' => 'exclamation',
            ]);
        }

        // Check for overdue jobs
        $overdueCount = WorkOrder::where('assigned_to_user_id', auth()->id())
            ->where('scheduled_start_at', '<', now()->subMinutes(30))
            ->whereIn('status', ['assigned'])
            ->count();

        if ($overdueCount > 0) {
            $alerts->push([
                'type' => 'warning',
                'message' => "{$overdueCount} job(s) past scheduled time",
                'icon' => 'clock',
            ]);
        }

        return $alerts;
    }
}
