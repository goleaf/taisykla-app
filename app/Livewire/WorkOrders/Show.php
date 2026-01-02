<?php

namespace App\Livewire\WorkOrders;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderEvent;
use App\Models\WorkOrderFeedback;
use App\Services\AutomationService;
use App\Services\AuditLogger;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Show extends Component
{
    public WorkOrder $workOrder;
    public string $status = '';
    public ?int $assignedToUserId = null;
    public string $note = '';
    public string $messageBody = '';
    public string $signatureName = '';
    public int $feedbackRating = 0;
    public string $feedbackComments = '';

    public array $statusOptions = [
        'submitted',
        'assigned',
        'in_progress',
        'on_hold',
        'completed',
        'closed',
        'canceled',
    ];

    public function mount(WorkOrder $workOrder): void
    {
        $user = auth()->user();
        if (! $this->canViewWorkOrder($user, $workOrder)) {
            abort(403);
        }

        $this->workOrder = $workOrder->load([
            'organization',
            'equipment',
            'category',
            'assignedTo',
            'requestedBy',
            'appointments.assignedTo',
            'parts.part',
            'feedback',
            'events.user',
            'attachments',
        ]);
        $this->status = $this->workOrder->status;
        $this->assignedToUserId = $this->workOrder->assigned_to_user_id;
        $this->signatureName = $this->workOrder->customer_signature_name ?? '';
        $this->feedbackRating = (int) ($this->workOrder->feedback?->rating ?? 0);
        $this->feedbackComments = $this->workOrder->feedback?->comments ?? '';
    }

    private function canViewWorkOrder(?User $user, WorkOrder $workOrder): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'dispatch', 'support'])) {
            return true;
        }

        if ($workOrder->requested_by_user_id === $user->id) {
            return true;
        }

        if ($user->hasRole('technician')) {
            return $workOrder->assigned_to_user_id === $user->id;
        }

        if ($user->hasRole('client')) {
            return $user->organization_id && $workOrder->organization_id === $user->organization_id;
        }

        return false;
    }

    public function updateStatus(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'dispatch', 'technician'])) {
            return;
        }

        $this->validate([
            'status' => ['required', Rule::in($this->statusOptions)],
        ]);

        $previousStatus = $this->workOrder->status;

        $updates = ['status' => $this->status];
        if ($this->status === 'assigned' && ! $this->workOrder->assigned_at) {
            $updates['assigned_at'] = now();
        }
        if ($this->status === 'in_progress' && ! $this->workOrder->started_at) {
            $updates['started_at'] = now();
        }
        if ($this->status === 'completed' && ! $this->workOrder->completed_at) {
            $updates['completed_at'] = now();
        }
        if ($this->status === 'canceled' && ! $this->workOrder->canceled_at) {
            $updates['canceled_at'] = now();
        }

        $this->workOrder->update($updates);

        if ($previousStatus !== $this->status) {
            WorkOrderEvent::create([
                'work_order_id' => $this->workOrder->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'from_status' => $previousStatus,
                'to_status' => $this->status,
            ]);

            app(AuditLogger::class)->log(
                'work_order.status_changed',
                $this->workOrder,
                'Work order status updated.',
                ['from' => $previousStatus, 'to' => $this->status]
            );

            app(AutomationService::class)->runForWorkOrder('work_order_status_changed', $this->workOrder, [
                'from_status' => $previousStatus,
                'to_status' => $this->status,
            ]);
        }
        $this->workOrder->refresh();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'messageBody' => ['required', 'string', 'max:2000'],
        ]);

        $user = auth()->user();
        if (! $user) {
            return;
        }

        $thread = $this->resolveMessageThread($user);

        Message::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => $this->messageBody,
        ]);

        $this->messageBody = '';
    }

    private function resolveMessageThread(User $user): MessageThread
    {
        $existing = MessageThread::where('work_order_id', $this->workOrder->id)
            ->whereHas('participants', function ($builder) use ($user) {
                $builder->where('user_id', $user->id);
            })
            ->latest()
            ->first();

        if ($existing) {
            return $existing;
        }

        $partner = $this->resolveMessagePartner($user);

        $thread = MessageThread::create([
            'subject' => 'Work Order #' . $this->workOrder->id,
            'organization_id' => $this->workOrder->organization_id,
            'work_order_id' => $this->workOrder->id,
            'created_by_user_id' => $user->id,
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
        ]);

        if ($partner && $partner->id !== $user->id) {
            MessageThreadParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $partner->id,
            ]);
        }

        return $thread;
    }

    private function resolveMessagePartner(User $user): ?User
    {
        if ($user->hasRole('client')) {
            return $this->workOrder->assignedTo
                ?? User::role('dispatch')->orderBy('name')->first()
                ?? User::role('admin')->orderBy('name')->first();
        }

        if ($user->hasRole('technician')) {
            return $this->workOrder->requestedBy
                ?? User::role('dispatch')->orderBy('name')->first()
                ?? User::role('admin')->orderBy('name')->first();
        }

        return $this->workOrder->requestedBy ?? $this->workOrder->assignedTo;
    }

    public function submitSignoff(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->canSignOff($user)) {
            return;
        }

        $this->validate([
            'signatureName' => ['required', 'string', 'max:255'],
        ]);

        $this->workOrder->update([
            'customer_signature_name' => $this->signatureName,
            'customer_signature_at' => now(),
        ]);

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => $user->id,
            'type' => 'customer_signoff',
            'note' => 'Customer approved the service.',
        ]);

        app(AuditLogger::class)->log(
            'work_order.customer_signoff',
            $this->workOrder,
            'Customer signed off on service.',
            ['signature_name' => $this->signatureName]
        );

        $this->workOrder->refresh();
    }

    public function submitFeedback(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->canLeaveFeedback($user)) {
            return;
        }

        $this->validate([
            'feedbackRating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedbackComments' => ['nullable', 'string', 'max:1000'],
        ]);

        WorkOrderFeedback::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => $user->id,
            'rating' => $this->feedbackRating,
            'comments' => $this->feedbackComments,
        ]);

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => $user->id,
            'type' => 'feedback',
            'note' => 'Customer submitted feedback.',
        ]);

        app(AuditLogger::class)->log(
            'work_order.feedback_submitted',
            $this->workOrder,
            'Customer submitted feedback.',
            ['rating' => $this->feedbackRating]
        );

        $this->workOrder->refresh();
    }

    private function canSignOff(User $user): bool
    {
        if ($this->workOrder->customer_signature_at) {
            return false;
        }

        if (! in_array($this->workOrder->status, ['completed', 'closed'], true)) {
            return false;
        }

        return $user->hasRole('client') || $this->workOrder->requested_by_user_id === $user->id;
    }

    private function canLeaveFeedback(User $user): bool
    {
        if ($this->workOrder->feedback) {
            return false;
        }

        if (! in_array($this->workOrder->status, ['completed', 'closed'], true)) {
            return false;
        }

        return $user->hasRole('client') || $this->workOrder->requested_by_user_id === $user->id;
    }

    private function statusSummary(): array
    {
        $status = $this->workOrder->status;
        $assigned = $this->workOrder->assignedTo?->name;
        $scheduled = $this->workOrder->scheduled_start_at?->format('M d, H:i');
        $timeWindow = $this->workOrder->time_window;
        $arrival = $this->workOrder->arrived_at?->format('M d, H:i');

        return match ($status) {
            'submitted' => [
                'title' => 'Request received',
                'description' => 'Your service request has been received and is awaiting review by our dispatch team.',
            ],
            'assigned' => [
                'title' => 'Technician assigned',
                'description' => $assigned
                    ? 'Assigned to ' . $assigned . ($scheduled ? ' with a scheduled visit on ' . $scheduled : '.') . ($timeWindow ? ' (' . $timeWindow . ')' : '')
                    : 'Your request has been assigned and is being scheduled.',
            ],
            'in_progress' => [
                'title' => 'Service in progress',
                'description' => $assigned
                    ? ($arrival ? $assigned . ' arrived at ' . $arrival . ' and is working on your equipment.' : $assigned . ' is on the way or has started service.')
                    : 'Service is currently in progress.',
            ],
            'on_hold' => [
                'title' => 'On hold',
                'description' => $this->workOrder->on_hold_reason
                    ? 'On hold: ' . $this->workOrder->on_hold_reason
                    : 'Your request is on hold while we await the next step.',
            ],
            'completed' => [
                'title' => 'Service completed',
                'description' => 'Service has been completed. Please review the report and provide your approval.',
            ],
            'closed' => [
                'title' => 'Request closed',
                'description' => 'This request has been closed. Thank you for working with us.',
            ],
            'canceled' => [
                'title' => 'Request canceled',
                'description' => 'This request has been canceled. Contact support if this is unexpected.',
            ],
            default => [
                'title' => Str::headline(str_replace('_', ' ', $status)),
                'description' => 'Status updated.',
            ],
        };
    }

    private function statusSteps(): array
    {
        $steps = [
            ['key' => 'submitted', 'label' => 'Submitted'],
            ['key' => 'assigned', 'label' => 'Assigned'],
            ['key' => 'in_progress', 'label' => 'In Progress'],
            ['key' => 'completed', 'label' => 'Completed'],
            ['key' => 'closed', 'label' => 'Closed'],
        ];

        $order = [];
        foreach ($steps as $index => $step) {
            $order[$step['key']] = $index;
        }

        $currentKey = $this->workOrder->status === 'on_hold' ? 'in_progress' : $this->workOrder->status;
        if (! array_key_exists($currentKey, $order)) {
            $currentKey = 'submitted';
        }

        $currentIndex = $order[$currentKey];
        $enriched = [];

        foreach ($steps as $index => $step) {
            $state = $index < $currentIndex ? 'complete' : ($index === $currentIndex ? 'current' : 'upcoming');
            $enriched[] = [
                'key' => $step['key'],
                'label' => $step['label'],
                'state' => $state,
            ];
        }

        return $enriched;
    }

    private function timeline(): array
    {
        return $this->workOrder->events
            ->sortBy('created_at')
            ->map(function (WorkOrderEvent $event) {
                return [
                    'summary' => $this->formatEvent($event),
                    'actor' => $event->user?->name ?? 'System',
                    'timestamp' => $event->created_at,
                    'type' => $event->type,
                ];
            })
            ->values()
            ->all();
    }

    private function formatEvent(WorkOrderEvent $event): string
    {
        if ($event->note) {
            return $event->note;
        }

        if ($event->type === 'status_change') {
            return match ($event->to_status) {
                'assigned' => 'Dispatch assigned your request.',
                'in_progress' => 'Service started.',
                'on_hold' => 'Request placed on hold.',
                'completed' => 'Service completed.',
                'closed' => 'Request closed.',
                'canceled' => 'Request canceled.',
                default => 'Status changed from ' . $this->friendlyStatus($event->from_status) . ' to ' . $this->friendlyStatus($event->to_status) . '.',
            };
        }

        if ($event->type === 'assignment') {
            $assignedId = $event->meta['assigned_to_user_id'] ?? null;
            if ($assignedId) {
                $assignedName = User::find($assignedId)?->name;
                return $assignedName ? 'Assigned to ' . $assignedName . '.' : 'Technician assigned.';
            }

            return 'Technician unassigned.';
        }

        return match ($event->type) {
            'arrival' => 'Technician arrived on site.',
            'created' => 'Request submitted.',
            'customer_signoff' => 'Customer approved the service.',
            'feedback' => 'Customer submitted feedback.',
            default => Str::headline(str_replace('_', ' ', $event->type)),
        };
    }

    private function friendlyStatus(?string $status): string
    {
        if (! $status) {
            return 'unknown';
        }

        return Str::headline(str_replace('_', ' ', $status));
    }

    private function serviceMetrics(): array
    {
        $started = $this->workOrder->started_at;
        $completed = $this->workOrder->completed_at;

        return [
            'arrived_at' => $this->workOrder->arrived_at,
            'started_at' => $started,
            'completed_at' => $completed,
            'duration_minutes' => $started && $completed ? $started->diffInMinutes($completed) : null,
            'estimated_minutes' => $this->workOrder->estimated_minutes,
            'labor_minutes' => $this->workOrder->labor_minutes,
            'travel_minutes' => $this->workOrder->travel_minutes,
        ];
    }

    private function messageThreadFor(User $user): ?MessageThread
    {
        return MessageThread::where('work_order_id', $this->workOrder->id)
            ->whereHas('participants', function ($builder) use ($user) {
                $builder->where('user_id', $user->id);
            })
            ->with(['messages.user'])
            ->latest()
            ->first();
    }

    private function technicianInsights(): array
    {
        $technician = $this->workOrder->assignedTo;
        if (! $technician) {
            return [
                'rating' => null,
                'rating_count' => 0,
            ];
        }

        $ratings = WorkOrderFeedback::query()
            ->whereHas('workOrder', function ($builder) use ($technician) {
                $builder->where('assigned_to_user_id', $technician->id);
            })
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        return [
            'rating' => $ratings?->avg_rating ? round((float) $ratings->avg_rating, 1) : null,
            'rating_count' => (int) ($ratings->total ?? 0),
        ];
    }

    private function trackingSnapshot(): array
    {
        $technician = $this->workOrder->assignedTo;
        $siteLat = $this->workOrder->location_latitude;
        $siteLng = $this->workOrder->location_longitude;
        $techLat = $technician?->current_latitude;
        $techLng = $technician?->current_longitude;

        $hasCoords = $siteLat !== null && $siteLng !== null && $techLat !== null && $techLng !== null;

        $etaMinutes = null;
        if (! $this->workOrder->arrived_at && in_array($this->workOrder->status, ['assigned', 'in_progress'], true)) {
            $etaMinutes = $this->workOrder->travel_minutes;
        }

        return [
            'has_coords' => $hasCoords,
            'map_url' => $hasCoords ? $this->mapRouteUrl($techLat, $techLng, $siteLat, $siteLng) : null,
            'eta_minutes' => $etaMinutes,
            'technician_coords' => $hasCoords ? ['lat' => $techLat, 'lng' => $techLng] : null,
            'site_coords' => $hasCoords ? ['lat' => $siteLat, 'lng' => $siteLng] : null,
        ];
    }

    private function mapRouteUrl(float $techLat, float $techLng, float $siteLat, float $siteLng): string
    {
        $origin = $techLat . ',' . $techLng;
        $destination = $siteLat . ',' . $siteLng;

        return 'https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=' . $origin . ';' . $destination;
    }
    public function assignTechnician(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'dispatch'])) {
            return;
        }

        $previousUserId = $this->workOrder->assigned_to_user_id;
        $updates = [
            'assigned_to_user_id' => $this->assignedToUserId,
            'status' => $this->workOrder->status,
        ];

        if ($this->assignedToUserId && $this->workOrder->status === 'submitted') {
            $updates['status'] = 'assigned';
        }

        if ($this->assignedToUserId && ! $this->workOrder->assigned_at) {
            $updates['assigned_at'] = now();
        }

        $this->workOrder->update($updates);

        if ($previousUserId !== $this->assignedToUserId) {
            WorkOrderEvent::create([
                'work_order_id' => $this->workOrder->id,
                'user_id' => auth()->id(),
                'type' => 'assignment',
                'note' => $this->assignedToUserId ? 'Assigned technician.' : 'Unassigned technician.',
                'meta' => [
                    'assigned_to_user_id' => $this->assignedToUserId,
                ],
            ]);

            app(AuditLogger::class)->log(
                'work_order.assignment_changed',
                $this->workOrder,
                'Work order assignment updated.',
                ['assigned_to_user_id' => $this->assignedToUserId]
            );

            if ($this->assignedToUserId) {
                app(AutomationService::class)->runForWorkOrder('work_order_assigned', $this->workOrder);
            }
        }

        $this->workOrder->refresh();
    }

    public function markArrived(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['technician', 'dispatch'])) {
            return;
        }

        $previousStatus = $this->workOrder->status;
        $updates = [];

        if (! $this->workOrder->arrived_at) {
            $updates['arrived_at'] = now();
        }

        if (! $this->workOrder->started_at) {
            $updates['started_at'] = now();
        }

        if (in_array($this->workOrder->status, ['submitted', 'assigned'], true)) {
            $updates['status'] = 'in_progress';
        }

        if ($updates === []) {
            return;
        }

        $this->workOrder->update($updates);

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => auth()->id(),
            'type' => 'arrival',
            'note' => 'Technician arrived on site.',
        ]);

        app(AuditLogger::class)->log(
            'work_order.arrived',
            $this->workOrder,
            'Work order marked as arrived.',
            ['status' => $this->workOrder->status]
        );

        if ($previousStatus !== $this->workOrder->status) {
            app(AutomationService::class)->runForWorkOrder('work_order_status_changed', $this->workOrder, [
                'from_status' => $previousStatus,
                'to_status' => $this->workOrder->status,
            ]);
        }

        $this->workOrder->refresh();
        $this->status = $this->workOrder->status;
    }

    public function addNote(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'dispatch', 'technician'])) {
            return;
        }

        $this->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'note' => $this->note,
        ]);

        $this->note = '';
        $this->workOrder->refresh();
    }

    public function render()
    {
        $user = auth()->user();
        $technicians = User::role('technician')->orderBy('name')->get();
        $timeline = $this->timeline();
        $statusSummary = $this->statusSummary();
        $statusSteps = $this->statusSteps();
        $serviceMetrics = $this->serviceMetrics();
        $messageThread = $user ? $this->messageThreadFor($user) : null;
        $nextAppointment = $this->workOrder->appointments
            ->sortBy('scheduled_start_at')
            ->first();
        $appointmentDuration = null;
        if ($nextAppointment?->scheduled_start_at && $nextAppointment?->scheduled_end_at) {
            $appointmentDuration = $nextAppointment->scheduled_start_at->diffInMinutes($nextAppointment->scheduled_end_at);
        }
        $estimatedDuration = $serviceMetrics['estimated_minutes'] ?? $appointmentDuration ?? $serviceMetrics['labor_minutes'];
        $technicianInsights = $this->technicianInsights();
        $tracking = $this->trackingSnapshot();

        return view('livewire.work-orders.show', [
            'technicians' => $technicians,
            'viewer' => $user,
            'timeline' => $timeline,
            'statusSummary' => $statusSummary,
            'statusSteps' => $statusSteps,
            'serviceMetrics' => $serviceMetrics,
            'messageThread' => $messageThread,
            'nextAppointment' => $nextAppointment,
            'appointmentDuration' => $appointmentDuration,
            'estimatedDuration' => $estimatedDuration,
            'technicianInsights' => $technicianInsights,
            'tracking' => $tracking,
        ]);
    }
}
