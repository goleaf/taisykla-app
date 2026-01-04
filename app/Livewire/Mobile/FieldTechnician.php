<?php

namespace App\Livewire\Mobile;

use App\Models\Part;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.mobile')]
class FieldTechnician extends Component
{
    use WithFileUploads;

    // Screen management
    #[Url]
    public string $activeScreen = 'jobs';

    #[Url]
    public ?int $activeJobId = null;

    // Job list
    public string $sortBy = 'time'; // time, route, priority
    public bool $isRefreshing = false;

    // Job detail state
    public ?WorkOrder $currentJob = null;
    public string $jobStatus = '';
    public bool $showEquipmentDetails = false;
    public bool $showPhotoGallery = false;

    // Timer state
    public ?Carbon $workStartTime = null;
    public int $elapsedSeconds = 0;
    public bool $timerRunning = false;
    public string $activityType = 'work'; // work, travel, break, diagnosis

    // Photo capture
    public array $photos = [];
    public string $photoLabel = 'during'; // before, during, after

    // Work notes
    public string $noteContent = '';
    public string $noteCategory = 'diagnosis'; // diagnosis, repair, testing, other
    public array $workNotes = [];
    public array $noteTemplates = [];

    // Parts usage
    public string $partSearch = '';
    public ?string $scannedBarcode = null;
    public array $selectedParts = [];
    public array $favoriteParts = [];

    // Time tracking
    public array $timeEntries = [];
    public array $dailySummary = [];
    public bool $showManualTimeEntry = false;
    public string $manualStartTime = '';
    public string $manualEndTime = '';
    public string $manualActivity = 'work';

    // Customer sign-off
    public string $signatureData = '';
    public string $customerName = '';
    public int $satisfactionRating = 5;
    public string $additionalComments = '';

    // Offline mode
    public bool $isOnline = true;
    public array $offlineQueue = [];
    public bool $isSyncing = false;

    // Quick actions
    public bool $showQuickActions = false;
    public bool $showEmergencyModal = false;
    public string $emergencyNote = '';
    public bool $showRequestPartsModal = false;
    public string $partsRequestNote = '';

    protected $listeners = [
        'location-updated' => 'handleLocationUpdate',
        'network-status-changed' => 'handleNetworkChange',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->can(PermissionCatalog::DASHBOARD_VIEW), 403);

        $this->loadNoteTemplates();
        $this->loadFavoriteParts();
        $this->loadOfflineQueue();
        $this->loadDailySummary();

        // Check for active job
        $activeJob = $this->getActiveJob();
        if ($activeJob) {
            $this->activeJobId = $activeJob->id;
            $this->currentJob = $activeJob;
            $this->jobStatus = $activeJob->status;
            if ($activeJob->started_at) {
                $this->workStartTime = $activeJob->started_at;
                $this->timerRunning = true;
            }
        }
    }

    // ===========================================
    // Job List Methods
    // ===========================================

    public function refreshJobs(): void
    {
        $this->isRefreshing = true;
        // Trigger a refresh of the job list
        $this->isRefreshing = false;
        $this->dispatch('jobs-refreshed');
    }

    public function setSortBy(string $sort): void
    {
        $this->sortBy = $sort;
    }

    public function viewJob(int $jobId): void
    {
        $this->currentJob = WorkOrder::with([
            'organization',
            'equipment',
            'attachments',
            'parts.part',
            'events',
        ])->findOrFail($jobId);

        $this->activeJobId = $jobId;
        $this->jobStatus = $this->currentJob->status;
        $this->activeScreen = 'detail';
        $this->loadJobNotes();
        $this->loadJobParts();
    }

    public function callCustomer(): void
    {
        if ($this->currentJob?->organization?->primary_contact_phone) {
            $this->dispatch('open-phone', phone: $this->currentJob->organization->primary_contact_phone);
        }
    }

    public function messageCustomer(): void
    {
        if ($this->currentJob?->organization?->primary_contact_phone) {
            $this->dispatch('open-sms', phone: $this->currentJob->organization->primary_contact_phone);
        }
    }

    public function navigateToJob(): void
    {
        if ($this->currentJob) {
            $address = $this->currentJob->location_address;
            $lat = $this->currentJob->location_latitude;
            $lng = $this->currentJob->location_longitude;

            if ($lat && $lng) {
                $this->dispatch('open-navigation', lat: $lat, lng: $lng, address: $address);
            } elseif ($address) {
                $this->dispatch('open-navigation', address: $address);
            }
        }
    }

    public function copyAddress(): void
    {
        if ($this->currentJob?->location_address) {
            $this->dispatch('copy-to-clipboard', text: $this->currentJob->location_address);
        }
    }

    // ===========================================
    // Status Control Methods
    // ===========================================

    public function updateJobStatus(string $status): void
    {
        if (!$this->currentJob) {
            return;
        }

        $this->queueAction('update_status', [
            'work_order_id' => $this->currentJob->id,
            'status' => $status,
        ]);

        if ($this->isOnline) {
            $this->currentJob->update(['status' => $status]);
            $this->currentJob->events()->create([
                'user_id' => auth()->id(),
                'type' => 'status_changed',
                'note' => "Status changed to {$status}",
            ]);
        }

        $this->jobStatus = $status;
    }

    public function arriveAtSite(): void
    {
        if (!$this->currentJob) {
            return;
        }

        $this->queueAction('arrive_at_site', [
            'work_order_id' => $this->currentJob->id,
            'arrived_at' => now()->toISOString(),
        ]);

        if ($this->isOnline) {
            $this->currentJob->update([
                'status' => 'in_progress',
                'arrived_at' => now(),
            ]);
            $this->currentJob->events()->create([
                'user_id' => auth()->id(),
                'type' => 'arrived',
                'note' => 'Technician arrived on site',
            ]);
        }

        $this->jobStatus = 'in_progress';
        $this->dispatch('arrived-at-site');
    }

    public function startWork(): void
    {
        if (!$this->currentJob) {
            return;
        }

        $this->workStartTime = now();
        $this->timerRunning = true;
        $this->activityType = 'work';

        $this->queueAction('start_work', [
            'work_order_id' => $this->currentJob->id,
            'started_at' => $this->workStartTime->toISOString(),
        ]);

        if ($this->isOnline) {
            $this->currentJob->update(['started_at' => $this->workStartTime]);
            $this->currentJob->events()->create([
                'user_id' => auth()->id(),
                'type' => 'work_started',
                'note' => 'Work started',
            ]);
        }
    }

    public function pauseTimer(): void
    {
        $this->timerRunning = false;
        $this->recordTimeEntry();
    }

    public function resumeTimer(): void
    {
        $this->timerRunning = true;
        $this->workStartTime = now();
    }

    public function stopTimer(): void
    {
        $this->timerRunning = false;
        $this->recordTimeEntry();
        $this->elapsedSeconds = 0;
    }

    public function setActivityType(string $type): void
    {
        if ($this->timerRunning) {
            $this->recordTimeEntry();
        }
        $this->activityType = $type;
        if ($this->timerRunning) {
            $this->workStartTime = now();
        }
    }

    public function requestHelp(): void
    {
        $this->showEmergencyModal = true;
    }

    // ===========================================
    // Photo Capture Methods
    // ===========================================

    public function goToPhotos(): void
    {
        $this->activeScreen = 'photos';
    }

    public function setPhotoLabel(string $label): void
    {
        $this->photoLabel = $label;
    }

    public function savePhoto(array $photoData): void
    {
        $photoData['label'] = $this->photoLabel;
        $photoData['work_order_id'] = $this->currentJob?->id;
        $this->photos[] = $photoData;

        $this->queueAction('save_photo', $photoData);
    }

    public function deletePhoto(int $index): void
    {
        if (isset($this->photos[$index])) {
            unset($this->photos[$index]);
            $this->photos = array_values($this->photos);
        }
    }

    // ===========================================
    // Work Notes Methods
    // ===========================================

    public function goToNotes(): void
    {
        $this->activeScreen = 'notes';
    }

    public function setNoteCategory(string $category): void
    {
        $this->noteCategory = $category;
    }

    public function saveNote(): void
    {
        if (empty(trim($this->noteContent))) {
            return;
        }

        $note = [
            'id' => uniqid(),
            'content' => $this->noteContent,
            'category' => $this->noteCategory,
            'timestamp' => now()->toISOString(),
            'work_order_id' => $this->currentJob?->id,
        ];

        $this->workNotes[] = $note;
        $this->queueAction('save_note', $note);

        if ($this->isOnline && $this->currentJob) {
            $this->currentJob->events()->create([
                'user_id' => auth()->id(),
                'type' => 'note_added',
                'note' => "[{$this->noteCategory}] {$this->noteContent}",
            ]);
        }

        $this->noteContent = '';
    }

    public function applyTemplate(int $index): void
    {
        if (isset($this->noteTemplates[$index])) {
            $this->noteContent = $this->noteTemplates[$index]['content'];
        }
    }

    // ===========================================
    // Parts Usage Methods
    // ===========================================

    public function goToParts(): void
    {
        $this->activeScreen = 'parts';
    }

    public function searchParts(): void
    {
        // Search is handled reactively via computed property
    }

    public function handleBarcodeScan(string $barcode): void
    {
        $this->scannedBarcode = $barcode;
        $part = Part::where('sku', $barcode)->orWhere('barcode', $barcode)->first();

        if ($part) {
            $this->addPart($part->id, 1);
            $this->dispatch('part-found', name: $part->name);
        } else {
            $this->dispatch('part-not-found', barcode: $barcode);
        }
    }

    public function addPart(int $partId, int $quantity = 1): void
    {
        // Check if already in selected parts
        foreach ($this->selectedParts as $index => $selected) {
            if ($selected['part_id'] === $partId) {
                $this->selectedParts[$index]['quantity'] += $quantity;
                $this->calculatePartsCost();
                return;
            }
        }

        $part = Part::find($partId);
        if ($part) {
            $this->selectedParts[] = [
                'part_id' => $partId,
                'name' => $part->name,
                'sku' => $part->sku,
                'quantity' => $quantity,
                'unit_cost' => $part->unit_cost ?? 0,
                'available' => $part->inventoryItems()->sum('quantity'),
            ];
            $this->calculatePartsCost();

            $this->queueAction('add_part', [
                'work_order_id' => $this->currentJob?->id,
                'part_id' => $partId,
                'quantity' => $quantity,
            ]);
        }
    }

    public function updatePartQuantity(int $index, int $quantity): void
    {
        if (isset($this->selectedParts[$index])) {
            $this->selectedParts[$index]['quantity'] = max(1, $quantity);
            $this->calculatePartsCost();
        }
    }

    public function removePart(int $index): void
    {
        if (isset($this->selectedParts[$index])) {
            unset($this->selectedParts[$index]);
            $this->selectedParts = array_values($this->selectedParts);
            $this->calculatePartsCost();
        }
    }

    public function addFromFavorites(int $partId): void
    {
        $this->addPart($partId, 1);
    }

    // ===========================================
    // Time Tracking Methods
    // ===========================================

    public function goToTimeTracking(): void
    {
        $this->activeScreen = 'time';
        $this->loadDailySummary();
    }

    public function startBreak(): void
    {
        if ($this->timerRunning) {
            $this->recordTimeEntry();
        }
        $this->activityType = 'break';
        $this->timerRunning = true;
        $this->workStartTime = now();
    }

    public function endBreak(): void
    {
        $this->recordTimeEntry();
        $this->activityType = 'work';
        $this->timerRunning = true;
        $this->workStartTime = now();
    }

    public function toggleManualEntry(): void
    {
        $this->showManualTimeEntry = !$this->showManualTimeEntry;
        $this->manualStartTime = now()->subHour()->format('H:i');
        $this->manualEndTime = now()->format('H:i');
    }

    public function saveManualEntry(): void
    {
        if (empty($this->manualStartTime) || empty($this->manualEndTime)) {
            return;
        }

        $entry = [
            'id' => uniqid(),
            'work_order_id' => $this->currentJob?->id,
            'activity' => $this->manualActivity,
            'start_time' => today()->setTimeFromTimeString($this->manualStartTime)->toISOString(),
            'end_time' => today()->setTimeFromTimeString($this->manualEndTime)->toISOString(),
            'duration_minutes' => Carbon::parse($this->manualStartTime)->diffInMinutes(Carbon::parse($this->manualEndTime)),
            'is_manual' => true,
        ];

        $this->timeEntries[] = $entry;
        $this->queueAction('manual_time_entry', $entry);

        $this->showManualTimeEntry = false;
        $this->loadDailySummary();
    }

    // ===========================================
    // Customer Sign-off Methods
    // ===========================================

    public function goToSignoff(): void
    {
        $this->activeScreen = 'signoff';
        $this->customerName = $this->currentJob?->organization?->primary_contact_name ?? '';
    }

    public function clearSignature(): void
    {
        $this->signatureData = '';
        $this->dispatch('clear-signature');
    }

    public function setRating(int $rating): void
    {
        $this->satisfactionRating = $rating;
    }

    public function submitCompletion(): void
    {
        if (!$this->currentJob || empty($this->signatureData)) {
            $this->dispatch('validation-error', message: 'Signature is required');
            return;
        }

        $completionData = [
            'work_order_id' => $this->currentJob->id,
            'completed_at' => now()->toISOString(),
            'customer_signature_name' => $this->customerName,
            'customer_signature_at' => now()->toISOString(),
            'customer_signoff_satisfied' => $this->satisfactionRating >= 4,
            'customer_signoff_comments' => $this->additionalComments,
            'signature_data' => $this->signatureData,
        ];

        $this->queueAction('complete_work_order', $completionData);

        if ($this->isOnline) {
            $this->currentJob->update([
                'status' => 'completed',
                'completed_at' => now(),
                'customer_signature_name' => $this->customerName,
                'customer_signature_at' => now(),
                'customer_signoff_satisfied' => $this->satisfactionRating >= 4,
                'customer_signoff_comments' => $this->additionalComments,
            ]);
        }

        $this->timerRunning = false;
        $this->recordTimeEntry();
        $this->jobStatus = 'completed';

        $this->dispatch('job-completed');
        $this->activeScreen = 'jobs';
        $this->currentJob = null;
        $this->activeJobId = null;
    }

    // ===========================================
    // Quick Actions
    // ===========================================

    public function toggleQuickActions(): void
    {
        $this->showQuickActions = !$this->showQuickActions;
    }

    public function emergencyContact(): void
    {
        $this->showEmergencyModal = true;
        $this->showQuickActions = false;
    }

    public function sendEmergencyAlert(): void
    {
        $this->queueAction('emergency_alert', [
            'work_order_id' => $this->currentJob?->id,
            'user_id' => auth()->id(),
            'note' => $this->emergencyNote,
            'timestamp' => now()->toISOString(),
            'location' => auth()->user()->current_latitude . ',' . auth()->user()->current_longitude,
        ]);

        if ($this->isOnline) {
            auth()->user()->update(['availability_status' => 'emergency']);
            if ($this->currentJob) {
                $this->currentJob->events()->create([
                    'user_id' => auth()->id(),
                    'type' => 'emergency_triggered',
                    'note' => $this->emergencyNote ?: 'Emergency alert triggered',
                ]);
            }
        }

        $this->showEmergencyModal = false;
        $this->emergencyNote = '';
        $this->dispatch('emergency-sent');
    }

    public function requestPartsDelivery(): void
    {
        $this->showRequestPartsModal = true;
        $this->showQuickActions = false;
    }

    public function submitPartsRequest(): void
    {
        $this->queueAction('parts_request', [
            'work_order_id' => $this->currentJob?->id,
            'user_id' => auth()->id(),
            'note' => $this->partsRequestNote,
            'location' => $this->currentJob?->location_address,
        ]);

        $this->showRequestPartsModal = false;
        $this->partsRequestNote = '';
        $this->dispatch('parts-requested');
    }

    public function viewTodaySchedule(): void
    {
        $this->showQuickActions = false;
        $this->activeScreen = 'jobs';
    }

    public function checkInventory(): void
    {
        $this->showQuickActions = false;
        $this->goToParts();
    }

    // ===========================================
    // Offline Mode Methods
    // ===========================================

    #[On('network-status-changed')]
    public function handleNetworkChange(bool $online): void
    {
        $wasOffline = !$this->isOnline;
        $this->isOnline = $online;

        if ($online && $wasOffline) {
            $this->syncOfflineQueue();
        }
    }

    public function syncOfflineQueue(): void
    {
        if (empty($this->offlineQueue) || !$this->isOnline) {
            return;
        }

        $this->isSyncing = true;

        foreach ($this->offlineQueue as $index => $action) {
            try {
                $this->processQueuedAction($action);
                unset($this->offlineQueue[$index]);
            } catch (\Exception $e) {
                // Mark action as failed, will retry later
                $this->offlineQueue[$index]['failed'] = true;
                $this->offlineQueue[$index]['error'] = $e->getMessage();
            }
        }

        $this->offlineQueue = array_values($this->offlineQueue);
        $this->saveOfflineQueue();
        $this->isSyncing = false;

        if (empty($this->offlineQueue)) {
            $this->dispatch('sync-complete');
        }
    }

    protected function queueAction(string $type, array $data): void
    {
        $action = [
            'id' => uniqid(),
            'type' => $type,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        if (!$this->isOnline) {
            $this->offlineQueue[] = $action;
            $this->saveOfflineQueue();
        } else {
            $this->processQueuedAction($action);
        }
    }

    protected function processQueuedAction(array $action): void
    {
        // Process different action types
        switch ($action['type']) {
            case 'update_status':
                $wo = WorkOrder::find($action['data']['work_order_id']);
                $wo?->update(['status' => $action['data']['status']]);
                break;
            case 'save_photo':
                // Save photo to storage
                break;
            case 'save_note':
                $wo = WorkOrder::find($action['data']['work_order_id']);
                $wo?->events()->create([
                    'user_id' => auth()->id(),
                    'type' => 'note_added',
                    'note' => "[{$action['data']['category']}] {$action['data']['content']}",
                ]);
                break;
            // Add more action handlers as needed
        }
    }

    protected function saveOfflineQueue(): void
    {
        session(['offline_queue' => $this->offlineQueue]);
    }

    protected function loadOfflineQueue(): void
    {
        $this->offlineQueue = session('offline_queue', []);
    }

    // ===========================================
    // Helper Methods
    // ===========================================

    #[On('location-updated')]
    public function handleLocationUpdate(float $lat, float $lng): void
    {
        auth()->user()->update([
            'current_latitude' => $lat,
            'current_longitude' => $lng,
        ]);
    }

    public function goBack(): void
    {
        if ($this->activeScreen !== 'jobs' && $this->activeScreen !== 'detail') {
            $this->activeScreen = 'detail';
        } elseif ($this->activeScreen === 'detail') {
            $this->activeScreen = 'jobs';
            $this->currentJob = null;
        }
    }

    protected function getActiveJob(): ?WorkOrder
    {
        return WorkOrder::where('assigned_to_user_id', auth()->id())
            ->where('status', 'in_progress')
            ->first();
    }

    protected function recordTimeEntry(): void
    {
        if (!$this->workStartTime) {
            return;
        }

        $entry = [
            'id' => uniqid(),
            'work_order_id' => $this->currentJob?->id,
            'activity' => $this->activityType,
            'start_time' => $this->workStartTime->toISOString(),
            'end_time' => now()->toISOString(),
            'duration_minutes' => $this->workStartTime->diffInMinutes(now()),
            'is_manual' => false,
        ];

        $this->timeEntries[] = $entry;
        $this->queueAction('time_entry', $entry);
        $this->loadDailySummary();
    }

    protected function calculatePartsCost(): void
    {
        // Cost calculation is handled in the view via computed property
    }

    protected function loadJobNotes(): void
    {
        if (!$this->currentJob) {
            return;
        }

        $this->workNotes = $this->currentJob->events()
            ->where('type', 'note_added')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($event) => [
                'id' => $event->id,
                'content' => $event->note,
                'category' => 'general',
                'timestamp' => $event->created_at->toISOString(),
            ])
            ->toArray();
    }

    protected function loadJobParts(): void
    {
        if (!$this->currentJob) {
            return;
        }

        $this->selectedParts = $this->currentJob->parts()
            ->with('part')
            ->get()
            ->map(fn($wp) => [
                'part_id' => $wp->part_id,
                'name' => $wp->part?->name ?? 'Unknown',
                'sku' => $wp->part?->sku ?? '',
                'quantity' => $wp->quantity,
                'unit_cost' => $wp->unit_cost,
                'available' => $wp->part?->inventoryItems()->sum('quantity') ?? 0,
            ])
            ->toArray();
    }

    protected function loadNoteTemplates(): void
    {
        $this->noteTemplates = [
            ['name' => 'Initial Diagnosis', 'content' => 'Initial inspection completed. Issue identified: '],
            ['name' => 'Parts Needed', 'content' => 'Required parts for repair: '],
            ['name' => 'Work Complete', 'content' => 'Repairs completed successfully. Tested and verified working.'],
            ['name' => 'Follow-up Required', 'content' => 'Follow-up visit recommended for: '],
            ['name' => 'Customer Information', 'content' => 'Customer informed about: '],
        ];
    }

    protected function loadFavoriteParts(): void
    {
        $this->favoriteParts = Part::withSum('inventoryItems', 'quantity')
            ->orderByDesc('inventory_items_sum_quantity')
            ->limit(8)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'available' => $p->inventory_items_sum_quantity ?? 0,
            ])
            ->toArray();
    }

    protected function loadDailySummary(): void
    {
        $this->dailySummary = [
            'work' => collect($this->timeEntries)->where('activity', 'work')->sum('duration_minutes'),
            'travel' => collect($this->timeEntries)->where('activity', 'travel')->sum('duration_minutes'),
            'break' => collect($this->timeEntries)->where('activity', 'break')->sum('duration_minutes'),
            'diagnosis' => collect($this->timeEntries)->where('activity', 'diagnosis')->sum('duration_minutes'),
        ];
    }

    public function getWorkQueueProperty(): Collection
    {
        $query = WorkOrder::with(['organization', 'equipment', 'category'])
            ->where('assigned_to_user_id', auth()->id())
            ->whereDate('scheduled_start_at', today())
            ->whereIn('status', ['assigned', 'in_progress']);

        if ($this->sortBy === 'time') {
            $query->orderBy('scheduled_start_at');
        } elseif ($this->sortBy === 'route') {
            $query->orderBy('route_sequence');
        } elseif ($this->sortBy === 'priority') {
            $query->orderByRaw("CASE priority 
                WHEN 'urgent' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'standard' THEN 3 
                ELSE 4 END");
        }

        return $query->get();
    }

    public function getSearchResultsProperty(): Collection
    {
        if (strlen($this->partSearch) < 2) {
            return collect();
        }

        return Part::withSum('inventoryItems', 'quantity')
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->partSearch}%")
                    ->orWhere('sku', 'like', "%{$this->partSearch}%");
            })
            ->limit(20)
            ->get();
    }

    public function getPartsTotalProperty(): float
    {
        return collect($this->selectedParts)->sum(fn($p) => $p['quantity'] * $p['unit_cost']);
    }

    public function render()
    {
        return view('livewire.mobile.field-technician', [
            'workQueue' => $this->workQueue,
            'searchResults' => $this->searchResults,
            'partsTotal' => $this->partsTotal,
        ]);
    }
}
