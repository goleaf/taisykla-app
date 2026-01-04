<?php

namespace App\Livewire\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentDocument;
use App\Models\ServiceEvent;
use App\Models\User;
use App\Services\EquipmentHealthService;
use App\Services\EquipmentLifecycleService;
use App\Services\QrCodeService;
use App\Support\PermissionCatalog;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Equipment $equipment;
    public string $activeTab = 'overview';
    public array $availableTabs = [
        'overview' => 'Overview',
        'specifications' => 'Specifications',
        'warranty' => 'Warranty',
        'service' => 'Service History',
        'documents' => 'Documents',
        'metrics' => 'Metrics',
    ];

    // Document upload
    public $documentFile = null;
    public string $documentTitle = '';
    public string $documentType = 'other';
    public string $documentNotes = '';

    // Service history filters
    public string $serviceFilter = '';
    public string $serviceDateFrom = '';
    public string $serviceDateTo = '';

    public function mount(Equipment $equipment): void
    {
        $user = auth()->user();
        if (!$this->canViewEquipment($user, $equipment)) {
            abort(403);
        }

        $this->equipment = $equipment->load([
            'organization',
            'category',
            'manufacturer',
            'assignedUser',
            'parent',
            'children',
            'warranties.claims',
            'workOrders.assignedTo',
            'workOrders.parts.part',
            'serviceEvents.technician',
            'documents.uploadedBy',
            'maintenanceSchedules',
            'attachments',
        ]);
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, $this->availableTabs)) {
            $this->activeTab = $tab;
        }
    }

    private function canViewEquipment(?User $user, Equipment $equipment): bool
    {
        if (!$user) {
            return false;
        }

        if (!$user->can(PermissionCatalog::EQUIPMENT_VIEW)) {
            return false;
        }

        if ($user->can(PermissionCatalog::EQUIPMENT_VIEW_ALL)) {
            return true;
        }

        if (
            $user->can(PermissionCatalog::EQUIPMENT_VIEW_ORG)
            && $user->organization_id
            && $equipment->organization_id === $user->organization_id
        ) {
            return true;
        }

        if ($user->can(PermissionCatalog::EQUIPMENT_VIEW_OWN) && $equipment->assigned_user_id === $user->id) {
            return true;
        }

        return false;
    }

    // ─── Overview Tab Data ────────────────────────────────────────────

    private function getQuickStats(): array
    {
        $ageYears = $this->equipment->age_in_years;
        $totalServiceCost = $this->equipment->total_service_cost;
        $completedWorkOrders = $this->equipment->workOrders->whereNotNull('completed_at');
        $totalDowntimeDays = 0;

        foreach ($completedWorkOrders as $wo) {
            if ($wo->started_at && $wo->completed_at) {
                $totalDowntimeDays += $wo->started_at->diffInDays($wo->completed_at);
            }
        }

        return [
            'age_years' => $ageYears,
            'total_service_cost' => number_format($totalServiceCost, 2),
            'service_count' => $this->equipment->serviceEvents->count(),
            'work_order_count' => $this->equipment->workOrders->count(),
            'downtime_days' => $totalDowntimeDays,
            'warranty_status' => $this->equipment->has_active_warranty ? 'Active' : 'Expired/None',
            'purchase_price' => $this->equipment->purchase_price
                ? number_format($this->equipment->purchase_price, 2)
                : 'N/A',
        ];
    }

    private function getHealthData(): array
    {
        $healthService = app(EquipmentHealthService::class);
        $score = $this->equipment->health_score ?? $healthService->calculateScore($this->equipment);

        return [
            'score' => $score,
            'grade' => $healthService->getHealthGrade($score),
            'label' => $healthService->getHealthLabel($score),
            'color' => $healthService->getHealthColor($score),
            'alerts' => $healthService->getPredictiveAlerts($this->equipment),
            'replacement' => $healthService->getReplacementRecommendation($this->equipment),
        ];
    }

    private function getQrCodeData(): array
    {
        $qrService = app(QrCodeService::class);
        return $qrService->getLabelData($this->equipment);
    }

    // ─── Specifications Tab Data ──────────────────────────────────────

    private function getSpecifications(): array
    {
        return [
            'basic' => [
                'Manufacturer' => $this->equipment->manufacturer?->name ?? $this->equipment->manufacturer,
                'Model' => $this->equipment->model,
                'Serial Number' => $this->equipment->serial_number,
                'Asset Tag' => $this->equipment->asset_tag,
                'Type' => $this->equipment->type,
                'Category' => $this->equipment->category?->name,
            ],
            'physical' => [
                'Dimensions' => $this->equipment->dimensions,
                'Weight' => $this->equipment->weight ? $this->equipment->weight . ' kg' : null,
            ],
            'network' => [
                'IP Address' => $this->equipment->ip_address,
                'MAC Address' => $this->equipment->mac_address,
            ],
            'location' => [
                'Building' => $this->equipment->location_building,
                'Floor' => $this->equipment->location_floor,
                'Room' => $this->equipment->location_room,
                'Full Location' => $this->equipment->location_full,
            ],
            'custom' => $this->equipment->specifications ?? [],
        ];
    }

    // ─── Warranty Tab Data ────────────────────────────────────────────

    private function getWarrantyData(): array
    {
        $warranties = $this->equipment->warranties->sortByDesc('ends_at');
        $activeWarranty = $warranties->first(fn($w) => $w->is_active);

        return [
            'warranties' => $warranties,
            'active' => $activeWarranty,
            'has_active' => (bool) $activeWarranty,
            'days_remaining' => $activeWarranty?->days_remaining,
            'claims' => $this->equipment->warranties->flatMap->claims,
        ];
    }

    private function getWarrantyTimeline(): array
    {
        $timeline = [];

        foreach ($this->equipment->warranties as $warranty) {
            if ($warranty->starts_at) {
                $timeline[] = [
                    'date' => $warranty->starts_at,
                    'event' => 'Warranty Started',
                    'provider' => $warranty->provider_name,
                    'type' => $warranty->coverage_type,
                    'icon' => 'o-shield-check',
                    'color' => 'success',
                ];
            }

            if ($warranty->ends_at) {
                $timeline[] = [
                    'date' => $warranty->ends_at,
                    'event' => $warranty->ends_at->isPast() ? 'Warranty Expired' : 'Warranty Expires',
                    'provider' => $warranty->provider_name,
                    'icon' => 'o-shield-exclamation',
                    'color' => $warranty->ends_at->isPast() ? 'error' : 'warning',
                    'is_future' => $warranty->ends_at->isFuture(),
                ];
            }
        }

        usort($timeline, fn($a, $b) => $a['date']->timestamp - $b['date']->timestamp);

        return $timeline;
    }

    // ─── Service History Tab Data ─────────────────────────────────────

    private function getServiceHistory()
    {
        $query = $this->equipment->serviceEvents()
            ->with('technician')
            ->orderByDesc('completed_at');

        if ($this->serviceFilter !== '') {
            $query->where('event_type', $this->serviceFilter);
        }

        if ($this->serviceDateFrom !== '') {
            $query->where('completed_at', '>=', Carbon::parse($this->serviceDateFrom));
        }

        if ($this->serviceDateTo !== '') {
            $query->where('completed_at', '<=', Carbon::parse($this->serviceDateTo));
        }

        return $query->get();
    }

    private function getMaintenanceHistory()
    {
        return $this->equipment->workOrders
            ->sortByDesc(fn($order) => $order->completed_at ?? $order->created_at)
            ->values();
    }

    // ─── Documents Tab Data ───────────────────────────────────────────

    private function getDocuments()
    {
        return $this->equipment->documents()
            ->with('uploadedBy')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('type');
    }

    public function uploadDocument(): void
    {
        $this->validate([
            'documentFile' => 'required|file|max:10240',
            'documentTitle' => 'required|string|max:255',
            'documentType' => 'required|string',
        ]);

        $path = $this->documentFile->store('equipment-documents', 'public');

        EquipmentDocument::create([
            'equipment_id' => $this->equipment->id,
            'uploaded_by_user_id' => auth()->id(),
            'type' => $this->documentType,
            'title' => $this->documentTitle,
            'file_path' => $path,
            'file_name' => $this->documentFile->getClientOriginalName(),
            'file_size' => $this->documentFile->getSize(),
            'mime_type' => $this->documentFile->getMimeType(),
            'notes' => $this->documentNotes,
        ]);

        $this->reset(['documentFile', 'documentTitle', 'documentType', 'documentNotes']);
        $this->equipment->refresh();

        session()->flash('status', 'Document uploaded successfully.');
    }

    public function deleteDocument(int $documentId): void
    {
        $document = $this->equipment->documents()->find($documentId);

        if ($document) {
            // Delete file from storage
            \Storage::disk('public')->delete($document->file_path);
            $document->delete();
            $this->equipment->refresh();
            session()->flash('status', 'Document deleted.');
        }
    }

    // ─── Metrics Tab Data ─────────────────────────────────────────────

    private function getMetricsData(): array
    {
        $lifecycleService = app(EquipmentLifecycleService::class);
        $healthService = app(EquipmentHealthService::class);

        $lifecycleStats = $lifecycleService->getLifecycleStats($this->equipment);
        $lifecycleTimeline = $lifecycleService->getLifecycleTimeline($this->equipment);

        // Calculate TCO
        $purchasePrice = $this->equipment->purchase_price ?? 0;
        $totalServiceCost = $this->equipment->total_service_cost;
        $tco = $purchasePrice + $totalServiceCost;

        // Calculate MTBF (Mean Time Between Failures)
        $failures = $this->equipment->serviceEvents()
            ->where('event_type', 'repair')
            ->orderBy('completed_at')
            ->get();

        $mtbf = null;
        if ($failures->count() >= 2) {
            $totalDays = 0;
            for ($i = 1; $i < $failures->count(); $i++) {
                if ($failures[$i]->completed_at && $failures[$i - 1]->completed_at) {
                    $totalDays += $failures[$i - 1]->completed_at->diffInDays($failures[$i]->completed_at);
                }
            }
            $mtbf = $failures->count() > 1 ? round($totalDays / ($failures->count() - 1)) : null;
        }

        // Calculate average repair time
        $avgRepairTime = $this->equipment->serviceEvents()
            ->where('event_type', 'repair')
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes');

        // Cost breakdown by year
        $costByYear = $this->equipment->serviceEvents()
            ->selectRaw('YEAR(completed_at) as year, SUM(labor_cost + parts_cost) as total')
            ->whereNotNull('completed_at')
            ->groupBy('year')
            ->orderBy('year')
            ->pluck('total', 'year')
            ->toArray();

        return [
            'lifecycle' => $lifecycleStats,
            'timeline' => $lifecycleTimeline,
            'tco' => [
                'total' => $tco,
                'purchase' => $purchasePrice,
                'service' => $totalServiceCost,
            ],
            'mtbf_days' => $mtbf,
            'avg_repair_minutes' => $avgRepairTime ? round($avgRepairTime) : null,
            'cost_by_year' => $costByYear,
            'failure_count' => $failures->count(),
        ];
    }

    // ─── Actions ──────────────────────────────────────────────────────

    public function generateQrCode(): void
    {
        $qrService = app(QrCodeService::class);
        $qrService->generateQrCode($this->equipment);
        $this->equipment->refresh();

        session()->flash('status', 'QR code generated.');
    }

    public function recalculateHealthScore(): void
    {
        $healthService = app(EquipmentHealthService::class);
        $healthService->updateEquipmentHealthScore($this->equipment);
        $this->equipment->refresh();

        session()->flash('status', 'Health score updated.');
    }

    // ─── Render ───────────────────────────────────────────────────────

    public function render()
    {
        $data = [
            'activeTab' => $this->activeTab,
            'tabs' => $this->availableTabs,
        ];

        // Load tab-specific data
        switch ($this->activeTab) {
            case 'overview':
                $data['quickStats'] = $this->getQuickStats();
                $data['health'] = $this->getHealthData();
                $data['qrCode'] = $this->getQrCodeData();
                $data['maintenanceHistory'] = $this->getMaintenanceHistory()->take(5);
                break;

            case 'specifications':
                $data['specifications'] = $this->getSpecifications();
                break;

            case 'warranty':
                $data['warranty'] = $this->getWarrantyData();
                $data['warrantyTimeline'] = $this->getWarrantyTimeline();
                break;

            case 'service':
                $data['serviceHistory'] = $this->getServiceHistory();
                $data['serviceTypes'] = ServiceEvent::typeOptions();
                break;

            case 'documents':
                $data['documents'] = $this->getDocuments();
                $data['documentTypes'] = EquipmentDocument::typeOptions();
                break;

            case 'metrics':
                $data['metrics'] = $this->getMetricsData();
                break;
        }

        return view('livewire.equipment.show', $data);
    }
}
