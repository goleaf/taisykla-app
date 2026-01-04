<?php

namespace App\Livewire\WorkOrders;

use App\Models\MessageThread;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderEvent;
use App\Models\WorkOrderFeedback;
use App\Models\SupportTicket;
use App\Services\AutomationService;
use App\Services\AuditLogger;
use App\Services\WorkOrderMessagingService;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public WorkOrder $workOrder;
    public string $status = '';
    public ?int $assignedToUserId = null;
    public string $note = '';
    public string $messageBody = '';
    public string $signatureName = '';
    public array $signoff = [];
    public array $feedback = [];
    public array $reportForm = [];
    public array $reportPhotos = [];
    public string $reportPhotoKind = 'before';

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
        if (!$this->canViewWorkOrder($user, $workOrder)) {
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
            'report',
            'events.user',
            'attachments',
        ]);
        $this->syncFromWorkOrder();
    }

    private function canViewWorkOrder(?User $user, WorkOrder $workOrder): bool
    {
        if (!$user) {
            return false;
        }

        if (!$user->can(PermissionCatalog::WORK_ORDERS_VIEW)) {
            return false;
        }

        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ALL)) {
            return true;
        }

        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ASSIGNED) && $workOrder->assigned_to_user_id === $user->id) {
            return true;
        }

        if (
            $user->can(PermissionCatalog::WORK_ORDERS_VIEW_ORG)
            && $user->organization_id
            && $workOrder->organization_id === $user->organization_id
        ) {
            return true;
        }

        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_OWN) && $workOrder->requested_by_user_id === $user->id) {
            return true;
        }

        return false;
    }

    private function canUpdateStatus(User $user): bool
    {
        return $user->canUpdateWorkOrders();
    }

    private function canAssign(User $user): bool
    {
        return $user->canAssignWorkOrders();
    }

    private function canMarkArrived(User $user): bool
    {
        return $user->canMarkWorkOrdersArrived();
    }

    private function canAddNote(User $user): bool
    {
        return $user->canAddWorkOrderNotes();
    }

    private function canManageReport(User $user): bool
    {
        return $user->canManageWorkOrderReports();
    }

    private function syncFromWorkOrder(): void
    {
        $this->status = $this->workOrder->status;
        $this->assignedToUserId = $this->workOrder->assigned_to_user_id;
        $this->signatureName = $this->workOrder->customer_signature_name ?? '';
        $this->signoff = [
            'functional' => $this->workOrder->customer_signoff_functional,
            'professional' => $this->workOrder->customer_signoff_professional,
            'satisfied' => $this->workOrder->customer_signoff_satisfied,
            'comments' => $this->workOrder->customer_signoff_comments ?? '',
        ];
        $this->feedback = [
            'overall' => (int) ($this->workOrder->feedback?->rating ?? 0),
            'professionalism' => (int) ($this->workOrder->feedback?->professionalism_rating ?? 0),
            'knowledge' => (int) ($this->workOrder->feedback?->knowledge_rating ?? 0),
            'communication' => (int) ($this->workOrder->feedback?->communication_rating ?? 0),
            'timeliness' => (int) ($this->workOrder->feedback?->timeliness_rating ?? 0),
            'quality' => (int) ($this->workOrder->feedback?->quality_rating ?? 0),
            'would_recommend' => $this->workOrder->feedback?->would_recommend,
            'comments' => $this->workOrder->feedback?->comments ?? '',
        ];
        $this->reportForm = [
            'diagnosis_summary' => $this->workOrder->report?->diagnosis_summary ?? '',
            'work_performed' => $this->workOrder->report?->work_performed ?? '',
            'test_results' => $this->workOrder->report?->test_results ?? '',
            'recommendations' => $this->workOrder->report?->recommendations ?? '',
            'diagnostic_minutes' => $this->workOrder->report?->diagnostic_minutes ?? null,
            'repair_minutes' => $this->workOrder->report?->repair_minutes ?? null,
            'testing_minutes' => $this->workOrder->report?->testing_minutes ?? null,
        ];
    }

    private function refreshWorkOrder(): void
    {
        $this->workOrder->refresh();
        $this->workOrder->load([
            'organization',
            'equipment',
            'category',
            'assignedTo',
            'requestedBy',
            'appointments.assignedTo',
            'parts.part',
            'feedback',
            'report',
            'events.user',
            'attachments',
        ]);
        $this->syncFromWorkOrder();
    }

    private function statusUpdates(WorkOrder $workOrder, string $status): array
    {
        $updates = ['status' => $status];
        if ($status === 'assigned' && !$workOrder->assigned_at) {
            $updates['assigned_at'] = now();
        }
        if ($status === 'in_progress' && !$workOrder->started_at) {
            $updates['started_at'] = now();
        }
        if ($status === 'completed' && !$workOrder->completed_at) {
            $updates['completed_at'] = now();
        }
        if ($status === 'canceled' && !$workOrder->canceled_at) {
            $updates['canceled_at'] = now();
        }

        return $updates;
    }

    private function recordStatusChange(User $user, string $previousStatus, string $newStatus): void
    {
        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => $user->id,
            'type' => 'status_change',
            'from_status' => $previousStatus,
            'to_status' => $newStatus,
        ]);

        app(AuditLogger::class)->log(
            'work_order.status_changed',
            $this->workOrder,
            'Work order status updated.',
            ['from' => $previousStatus, 'to' => $newStatus]
        );

        app(AutomationService::class)->runForWorkOrder('work_order_status_changed', $this->workOrder, [
            'from_status' => $previousStatus,
            'to_status' => $newStatus,
        ]);

        $this->sendStatusUpdateMessage($user, $newStatus);
    }

    private function normalizeId(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    public function updateStatus(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canUpdateStatus($user)) {
            return;
        }

        $this->validate([
            'status' => ['required', 'string', Rule::in($this->statusOptions)],
        ]);

        $previousStatus = $this->workOrder->status;
        $updates = $this->statusUpdates($this->workOrder, $this->status);
        $this->workOrder->update($updates);

        if ($previousStatus !== $this->status) {
            $this->recordStatusChange($user, $previousStatus, $this->status);
        }
        $this->refreshWorkOrder();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'messageBody' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $user = auth()->user();
        if (!$user) {
            return;
        }

        app(WorkOrderMessagingService::class)->postMessage(
            $this->workOrder,
            $user,
            $this->messageBody,
            $this->fallbackParticipantFor($user)
        );

        $this->messageBody = '';
    }

    public function saveReport(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canManageReport($user)) {
            return;
        }

        $this->validate([
            'reportForm.diagnosis_summary' => ['required', 'string', 'min:5', 'max:2000'],
            'reportForm.work_performed' => ['required', 'string', 'min:5', 'max:4000'],
            'reportForm.test_results' => ['nullable', 'string', 'max:2000'],
            'reportForm.recommendations' => ['nullable', 'string', 'max:2000'],
            'reportForm.diagnostic_minutes' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'reportForm.repair_minutes' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'reportForm.testing_minutes' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        $payload = [
            'diagnosis_summary' => $this->reportForm['diagnosis_summary'],
            'work_performed' => $this->reportForm['work_performed'],
            'test_results' => $this->reportForm['test_results'],
            'recommendations' => $this->reportForm['recommendations'],
            'diagnostic_minutes' => $this->reportForm['diagnostic_minutes'],
            'repair_minutes' => $this->reportForm['repair_minutes'],
            'testing_minutes' => $this->reportForm['testing_minutes'],
        ];

        if ($this->workOrder->report) {
            $this->workOrder->report->update($payload);
            $action = 'work_order.report_updated';
            $note = 'Service report updated.';
        } else {
            $this->workOrder->report()->create(array_merge($payload, [
                'created_by_user_id' => $user->id,
            ]));
            $action = 'work_order.report_created';
            $note = 'Service report created.';
        }

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => $user->id,
            'type' => 'service_report',
            'note' => $note,
        ]);

        app(AuditLogger::class)->log(
            $action,
            $this->workOrder,
            $note,
            ['work_order_id' => $this->workOrder->id]
        );

        $this->refreshWorkOrder();
    }

    public function uploadReportPhotos(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canManageReport($user)) {
            return;
        }

        $this->validate([
            'reportPhotoKind' => ['required', 'string', Rule::in(['before', 'during', 'after', 'report'])],
            'reportPhotos' => ['required', 'array', 'min:1', 'max:10'],
            'reportPhotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        foreach ($this->reportPhotos as $photo) {
            $path = $photo->storePublicly('work-orders/' . $this->workOrder->id . '/report', 'public');

            $this->workOrder->attachments()->create([
                'uploaded_by_user_id' => $user->id,
                'label' => ucfirst($this->reportPhotoKind) . ' photo',
                'file_name' => $photo->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'kind' => $this->reportPhotoKind,
            ]);
        }

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => $user->id,
            'type' => 'photo_upload',
            'note' => ucfirst($this->reportPhotoKind) . ' photos uploaded.',
        ]);

        $this->reportPhotos = [];
        $this->refreshWorkOrder();
    }

    public function submitSignoff(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canSignOff($user)) {
            return;
        }

        $this->validate([
            'signatureName' => ['required', 'string', 'min:2', 'max:255'],
            'signoff.functional' => ['required', 'boolean'],
            'signoff.professional' => ['required', 'boolean'],
            'signoff.satisfied' => ['required', 'boolean'],
            'signoff.comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workOrder->update([
            'customer_signature_name' => $this->signatureName,
            'customer_signature_at' => now(),
            'customer_signoff_functional' => $this->signoff['functional'],
            'customer_signoff_professional' => $this->signoff['professional'],
            'customer_signoff_satisfied' => $this->signoff['satisfied'],
            'customer_signoff_comments' => $this->signoff['comments'],
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

        if (!$this->signoff['functional'] || !$this->signoff['professional'] || !$this->signoff['satisfied']) {
            $this->createFollowUpTicket($user, $this->signoff['comments'] ?: 'Customer sign-off flagged concerns.');
        }

        $this->refreshWorkOrder();
    }

    public function submitFeedback(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canLeaveFeedback($user)) {
            return;
        }

        $this->validate([
            'feedback.overall' => ['required', 'integer', 'between:1,5'],
            'feedback.professionalism' => ['required', 'integer', 'between:1,5'],
            'feedback.knowledge' => ['required', 'integer', 'between:1,5'],
            'feedback.communication' => ['required', 'integer', 'between:1,5'],
            'feedback.timeliness' => ['required', 'integer', 'between:1,5'],
            'feedback.quality' => ['required', 'integer', 'between:1,5'],
            'feedback.would_recommend' => ['required', 'boolean'],
            'feedback.comments' => ['nullable', 'string', 'max:1000'],
        ]);

        WorkOrderFeedback::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => $user->id,
            'rating' => $this->feedback['overall'],
            'professionalism_rating' => $this->feedback['professionalism'],
            'knowledge_rating' => $this->feedback['knowledge'],
            'communication_rating' => $this->feedback['communication'],
            'timeliness_rating' => $this->feedback['timeliness'],
            'quality_rating' => $this->feedback['quality'],
            'would_recommend' => $this->feedback['would_recommend'],
            'comments' => $this->feedback['comments'],
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
            ['rating' => $this->feedback['overall']]
        );

        if ($this->feedback['overall'] <= 2 || !$this->feedback['would_recommend']) {
            $this->createFollowUpTicket($user, $this->feedback['comments'] ?: 'Customer reported dissatisfaction.');
        }

        $this->refreshWorkOrder();
    }

    private function createFollowUpTicket(User $user, string $description): void
    {
        if (!$user->can(PermissionCatalog::SUPPORT_CREATE)) {
            return;
        }

        $existing = SupportTicket::where('work_order_id', $this->workOrder->id)
            ->whereIn('status', ['open', 'in_review'])
            ->first();

        if ($existing) {
            return;
        }

        SupportTicket::create([
            'organization_id' => $this->workOrder->organization_id,
            'work_order_id' => $this->workOrder->id,
            'submitted_by_user_id' => $user->id,
            'status' => 'open',
            'priority' => 'high',
            'subject' => 'Customer feedback follow-up for Work Order #' . $this->workOrder->id,
            'description' => $description,
        ]);

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => $user->id,
            'type' => 'feedback_followup',
            'note' => 'Escalated feedback for follow-up.',
        ]);
    }

    private function canSignOff(User $user): bool
    {
        if ($this->workOrder->customer_signature_at) {
            return false;
        }

        if (!in_array($this->workOrder->status, ['completed', 'closed'], true)) {
            return false;
        }

        if (!$user->canSignOffWorkOrders()) {
            return false;
        }

        if ($this->workOrder->requested_by_user_id === $user->id) {
            return true;
        }

        return $user->organization_id && $this->workOrder->organization_id === $user->organization_id;
    }

    private function canLeaveFeedback(User $user): bool
    {
        if ($this->workOrder->feedback) {
            return false;
        }

        if (!in_array($this->workOrder->status, ['completed', 'closed'], true)) {
            return false;
        }

        if (!$user->canSubmitWorkOrderFeedback()) {
            return false;
        }

        if ($this->workOrder->requested_by_user_id === $user->id) {
            return true;
        }

        return $user->organization_id && $this->workOrder->organization_id === $user->organization_id;
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
        if (!array_key_exists($currentKey, $order)) {
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
            'service_report' => 'Service report updated.',
            'photo_upload' => 'Service photos uploaded.',
            'feedback_followup' => 'Feedback escalated for follow-up.',
            default => Str::headline(str_replace('_', ' ', $event->type)),
        };
    }

    private function friendlyStatus(?string $status): string
    {
        if (!$status) {
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
        $query = MessageThread::where('work_order_id', $this->workOrder->id)
            ->with(['messages.user']);

        if (!$user->canViewAllWorkOrders()) {
            $query->whereHas('participants', function ($builder) use ($user) {
                $builder->where('user_id', $user->id);
            });
        }

        return $query->latest()->first();
    }

    private function fallbackParticipantFor(User $actor): ?User
    {
        if ($this->workOrder->assigned_to_user_id) {
            return null;
        }

        if ($actor->isCustomer()) {
            return User::role([RoleCatalog::OPERATIONS_MANAGER, RoleCatalog::DISPATCH])->orderBy('name')->first()
                ?? User::role(RoleCatalog::ADMIN)->orderBy('name')->first();
        }

        return null;
    }

    private function postProgressMessage(User $actor, string $body): void
    {
        if (trim($body) === '') {
            return;
        }

        app(WorkOrderMessagingService::class)->postMessage(
            $this->workOrder,
            $actor,
            $body,
            $this->fallbackParticipantFor($actor)
        );
    }

    private function sendStatusUpdateMessage(User $actor, string $status): void
    {
        $message = match ($status) {
            'assigned' => $this->assignmentMessage($this->workOrder->assignedTo),
            'in_progress' => $this->workOrder->arrived_at
            ? 'Technician has arrived on site and started service.'
            : 'Service is now in progress.',
            'on_hold' => $this->workOrder->on_hold_reason
            ? 'Your request is on hold. ' . $this->workOrder->on_hold_reason
            : 'Your request is on hold. We will follow up with next steps.',
            'completed' => 'Service has been completed. Please review the report and provide your approval.',
            'closed' => 'Your request has been closed. Thank you for working with us.',
            'canceled' => 'Your request has been canceled. Contact support if this is unexpected.',
            default => null,
        };

        if ($message) {
            $this->postProgressMessage($actor, $message);
        }
    }

    private function assignmentMessage(?User $assignedUser): string
    {
        if (!$assignedUser) {
            return 'Your request has been assigned and is being scheduled.';
        }

        $scheduled = $this->workOrder->scheduled_start_at?->format('M d, H:i');
        $timeWindow = $this->workOrder->time_window;

        $message = 'Your request has been assigned to ' . $assignedUser->name . '.';
        if ($scheduled) {
            $message .= ' Scheduled for ' . $scheduled;
            if ($timeWindow) {
                $message .= ' (' . $timeWindow . ')';
            }
            $message .= '.';
        }

        return $message;
    }

    private function technicianInsights(): array
    {
        $technician = $this->workOrder->assignedTo;
        if (!$technician) {
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
        if (!$this->workOrder->arrived_at && in_array($this->workOrder->status, ['assigned', 'in_progress'], true)) {
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
        if (!$user || !$this->canAssign($user)) {
            return;
        }

        $previousUserId = $this->workOrder->assigned_to_user_id;
        $assignedUserId = $this->normalizeId($this->assignedToUserId);
        $this->assignedToUserId = $assignedUserId;
        $updates = [
            'assigned_to_user_id' => $assignedUserId,
            'status' => $this->workOrder->status,
        ];

        if ($assignedUserId && $this->workOrder->status === 'submitted') {
            $updates['status'] = 'assigned';
        }

        if ($assignedUserId && !$this->workOrder->assigned_at) {
            $updates['assigned_at'] = now();
        }

        $this->workOrder->update($updates);

        if ($previousUserId !== $assignedUserId) {
            $assignedUser = $assignedUserId
                ? User::find($assignedUserId)
                : null;

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
                ['assigned_to_user_id' => $assignedUserId]
            );

            if ($assignedUserId) {
                app(AutomationService::class)->runForWorkOrder('work_order_assigned', $this->workOrder);
            }

            $assignmentMessage = $assignedUser
                ? $this->assignmentMessage($assignedUser)
                : 'Your request is awaiting technician assignment.';
            $this->postProgressMessage($user, $assignmentMessage);
        }

        $this->refreshWorkOrder();
    }

    public function markArrived(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canMarkArrived($user)) {
            return;
        }

        $previousStatus = $this->workOrder->status;
        $updates = [];

        if (!$this->workOrder->arrived_at) {
            $updates['arrived_at'] = now();
        }

        if (!$this->workOrder->started_at) {
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

        $this->postProgressMessage($user, 'Technician has arrived on site and begun service.');
        $this->refreshWorkOrder();
    }

    public function addNote(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canAddNote($user)) {
            return;
        }

        $this->validate([
            'note' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        WorkOrderEvent::create([
            'work_order_id' => $this->workOrder->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'note' => $this->note,
        ]);

        $this->note = '';
        $this->refreshWorkOrder();
    }

    public function cloneWorkOrder(): void
    {
        $user = auth()->user();
        if (!$user || !$user->canCreateWorkOrders()) {
            session()->flash('error', 'You do not have permission to create work orders.');
            return;
        }

        $clone = $this->workOrder->replicate([
            'status',
            'assigned_to_user_id',
            'assigned_at',
            'requested_at',
            'scheduled_start_at',
            'scheduled_end_at',
            'arrived_at',
            'started_at',
            'completed_at',
            'canceled_at',
            'customer_signature_name',
            'customer_signature_at',
            'customer_signoff_functional',
            'customer_signoff_professional',
            'customer_signoff_satisfied',
            'customer_signoff_comments',
        ]);

        $clone->status = 'submitted';
        $clone->requested_by_user_id = $user->id;
        $clone->requested_at = now();
        $clone->subject = '[Clone] ' . $clone->subject;
        $clone->save();

        WorkOrderEvent::create([
            'work_order_id' => $clone->id,
            'user_id' => $user->id,
            'type' => 'created',
            'to_status' => 'submitted',
            'note' => 'Cloned from work order #' . $this->workOrder->id,
        ]);

        app(AuditLogger::class)->log(
            'work_order.cloned',
            $clone,
            'Work order cloned from #' . $this->workOrder->id,
            ['original_id' => $this->workOrder->id]
        );

        session()->flash('status', 'Work order cloned successfully!');
        $this->redirect(route('work-orders.show', $clone), navigate: true);
    }

    public function getCanCreateProperty(): bool
    {
        return auth()->user()?->canCreateWorkOrders() ?? false;
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
        $canUpdateStatus = $user ? $this->canUpdateStatus($user) : false;
        $canMarkArrived = $user ? $this->canMarkArrived($user) : false;
        $canAssign = $user ? $this->canAssign($user) : false;
        $canAddNote = $user ? $this->canAddNote($user) : false;
        $canManageReport = $user ? $this->canManageReport($user) : false;
        $canSignOff = $user ? $this->canSignOff($user) : false;
        $canLeaveFeedback = $user ? $this->canLeaveFeedback($user) : false;
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
        $photoGroups = $this->workOrder->attachments->groupBy('kind');

        return view('livewire.work-orders.show', [
            'technicians' => $technicians,
            'viewer' => $user,
            'canUpdateStatus' => $canUpdateStatus,
            'canMarkArrived' => $canMarkArrived,
            'canAssign' => $canAssign,
            'canAddNote' => $canAddNote,
            'canManageReport' => $canManageReport,
            'canSignOff' => $canSignOff,
            'canLeaveFeedback' => $canLeaveFeedback,
            'canCreate' => $this->canCreate,
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
            'photoGroups' => $photoGroups,
        ]);
    }
}
