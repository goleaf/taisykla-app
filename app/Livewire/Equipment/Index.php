<?php

namespace App\Livewire\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Organization;
use App\Models\User;
use App\Models\Warranty;
use App\Services\AuditLogger;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public array $form = [];
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $inventoryView = 'list';
    public string $search = '';
    public string $statusFilter = 'all';
    public string $categoryFilter = '';
    public string $organizationFilter = '';
    public string $manufacturerFilter = '';
    public string $ownerFilter = '';
    public string $ageFilter = '';
    public string $warrantyFilter = '';
    public string $typeFilter = '';
    public string $locationFilter = '';
    public string $sortField = 'last_service_at';
    public string $sortDirection = 'desc';
    public array $photos = [];
    public array $selected = [];
    public string $bulkAction = '';
    public ?string $bulkStatus = null;
    public string $bulkLocation = '';
    public ?int $bulkOwner = null;
    public bool $showImport = false;

    protected $paginationTheme = 'tailwind';

    public array $statusOptions = [
        'all' => 'All',
        'operational' => 'Operational',
        'needs_attention' => 'Needs Attention',
        'in_repair' => 'In Repair',
        'retired' => 'Decommissioned',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::EQUIPMENT_VIEW), 403);

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $user = auth()->user();
        $this->form = [
            'organization_id' => $user->organization_id,
            'equipment_category_id' => null,
            'name' => '',
            'type' => '',
            'manufacturer' => '',
            'model' => '',
            'serial_number' => '',
            'asset_tag' => '',
            'purchase_date' => null,
            'purchase_price' => null,
            'purchase_vendor' => '',
            'status' => 'operational',
            'location_name' => '',
            'location_address' => '',
            'location_building' => '',
            'location_floor' => '',
            'location_room' => '',
            'assigned_user_id' => $user?->id,
            'notes' => '',
            'specifications' => '',
            'custom_fields' => '',
            'warranty_provider' => '',
            'warranty_type' => 'standard',
            'warranty_starts_at' => null,
            'warranty_ends_at' => null,
            'warranty_terms' => '',
        ];
        $this->photos = [];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOrganizationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedLocationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSortField(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->categoryFilter = '';
        $this->organizationFilter = '';
        $this->typeFilter = '';
        $this->locationFilter = '';
        $this->sortField = 'last_service_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function startCreate(): void
    {
        if (!$this->canManage) {
            return;
        }

        $this->editingId = null;
        $this->resetForm();
        $this->showForm = true;
    }

    public function editEquipment(int $equipmentId): void
    {
        if (!$this->canManage) {
            return;
        }

        $user = auth()->user();
        $equipment = $this->equipmentQueryFor($user)->findOrFail($equipmentId);
        $primaryWarranty = $equipment->warranties()
            ->orderByDesc('ends_at')
            ->first();
        $this->editingId = $equipment->id;
        $this->form = [
            'organization_id' => $equipment->organization_id,
            'equipment_category_id' => $equipment->equipment_category_id,
            'name' => $equipment->name,
            'type' => $equipment->type,
            'manufacturer' => $equipment->manufacturer ?? '',
            'model' => $equipment->model ?? '',
            'serial_number' => $equipment->serial_number ?? '',
            'asset_tag' => $equipment->asset_tag ?? '',
            'purchase_date' => $equipment->purchase_date?->toDateString(),
            'purchase_price' => $equipment->purchase_price,
            'purchase_vendor' => $equipment->purchase_vendor ?? '',
            'status' => $equipment->status,
            'location_name' => $equipment->location_name ?? '',
            'location_address' => $equipment->location_address ?? '',
            'location_building' => $equipment->location_building ?? '',
            'location_floor' => $equipment->location_floor ?? '',
            'location_room' => $equipment->location_room ?? '',
            'assigned_user_id' => $equipment->assigned_user_id,
            'notes' => $equipment->notes ?? '',
            'specifications' => $this->encodeJson($equipment->specifications),
            'custom_fields' => $this->encodeJson($equipment->custom_fields),
            'warranty_provider' => $primaryWarranty?->provider_name ?? '',
            'warranty_type' => $primaryWarranty?->coverage_type ?? 'standard',
            'warranty_starts_at' => $primaryWarranty?->starts_at?->toDateString(),
            'warranty_ends_at' => $primaryWarranty?->ends_at?->toDateString(),
            'warranty_terms' => $primaryWarranty?->coverage_details ?? '',
        ];
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->editingId = null;
        $this->resetForm();
        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'form.organization_id' => ['nullable', 'exists:organizations,id'],
            'form.equipment_category_id' => ['required', 'exists:equipment_categories,id'],
            'form.name' => ['required', 'string', 'max:255'],
            'form.type' => ['required', 'string', 'max:255'],
            'form.manufacturer' => ['nullable', 'string', 'max:255'],
            'form.model' => ['nullable', 'string', 'max:255'],
            'form.serial_number' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\\-_.\\/]+$/',
                Rule::unique('equipment', 'serial_number')->ignore($this->editingId),
            ],
            'form.asset_tag' => ['nullable', 'string', 'max:255'],
            'form.purchase_date' => ['nullable', 'date'],
            'form.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'form.purchase_vendor' => ['nullable', 'string', 'max:255'],
            'form.status' => ['required', Rule::in($this->statusValues())],
            'form.location_name' => ['nullable', 'string', 'max:255'],
            'form.location_address' => ['nullable', 'string', 'max:1000'],
            'form.location_building' => ['nullable', 'string', 'max:255'],
            'form.location_floor' => ['nullable', 'string', 'max:255'],
            'form.location_room' => ['nullable', 'string', 'max:255'],
            'form.assigned_user_id' => ['nullable', 'exists:users,id'],
            'form.notes' => ['nullable', 'string'],
            'form.specifications' => ['nullable', 'string', 'json'],
            'form.custom_fields' => ['nullable', 'string', 'json'],
            'form.warranty_provider' => ['nullable', 'string', 'max:255'],
            'form.warranty_type' => ['nullable', 'string', 'max:255'],
            'form.warranty_starts_at' => ['nullable', 'date'],
            'form.warranty_ends_at' => ['nullable', 'date', 'after_or_equal:form.warranty_starts_at'],
            'form.warranty_terms' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'],
        ];
    }

    public function saveEquipment(): void
    {
        if (!$this->canManage) {
            return;
        }

        $user = auth()->user();
        if (!$user) {
            return;
        }

        if ($user->isBusinessCustomer() && $user->organization_id) {
            $this->form['organization_id'] = $user->organization_id;
        }

        $this->validate();

        $payload = [
            'organization_id' => $this->normalizeId($this->form['organization_id']),
            'equipment_category_id' => $this->normalizeId($this->form['equipment_category_id']),
            'name' => trim($this->form['name']),
            'type' => trim($this->form['type']),
            'manufacturer' => $this->normalizeText($this->form['manufacturer']),
            'model' => $this->normalizeText($this->form['model']),
            'serial_number' => $this->normalizeText($this->form['serial_number']),
            'asset_tag' => $this->normalizeText($this->form['asset_tag']),
            'purchase_date' => $this->form['purchase_date'] ?: null,
            'status' => $this->form['status'],
            'location_name' => $this->normalizeText($this->form['location_name']),
            'location_address' => $this->normalizeText($this->form['location_address']),
            'notes' => $this->normalizeText($this->form['notes']),
        ];
        if ($user->isConsumer()) {
            $payload['assigned_user_id'] = $user->id;
        }

        if ($this->editingId) {
            $equipmentQuery = Equipment::query();
            if ($user->isBusinessCustomer()) {
                $equipmentQuery->where('organization_id', $user->organization_id);
            } elseif ($user->isConsumer()) {
                $equipmentQuery->where('assigned_user_id', $user->id);
            }
            $equipment = $equipmentQuery->findOrFail($this->editingId);
            $equipment->update($payload);
            app(AuditLogger::class)->log(
                'equipment.updated',
                $equipment,
                'Equipment updated.',
                ['name' => $equipment->name]
            );
            session()->flash('status', 'Equipment updated.');
        } else {
            $equipment = Equipment::create($payload);
            app(AuditLogger::class)->log(
                'equipment.created',
                $equipment,
                'Equipment created.',
                ['name' => $equipment->name]
            );
            session()->flash('status', 'Equipment added.');
        }

        $this->resetForm();
        $this->editingId = null;
        $this->showForm = false;
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $isBusinessCustomer = $user->isBusinessCustomer();
        $isConsumer = $user->isConsumer();
        $isClient = $user->isCustomer();

        $query = $this->equipmentQueryFor($user)
            ->with(['organization', 'category'])
            ->withMax([
                'workOrders as last_service_at' => function (Builder $builder) {
                    $builder->whereNotNull('completed_at')
                        ->whereIn('status', ['completed', 'closed']);
                }
            ], 'completed_at');

        if (!$isClient && $this->organizationFilter !== '') {
            $query->where('organization_id', $this->organizationFilter);
        }

        if ($this->categoryFilter !== '') {
            $query->where('equipment_category_id', $this->categoryFilter);
        }

        if ($this->typeFilter !== '') {
            $query->where('type', $this->typeFilter);
        }

        if ($this->locationFilter !== '') {
            $query->where('location_name', $this->locationFilter);
        }

        if ($this->search !== '') {
            $this->applySearch($query, $this->search);
        }

        $summary = $this->buildSummary(clone $query);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $this->applySort($query);
        $equipment = $query->paginate(10);
        $organizations = $isClient ? collect() : Organization::orderBy('name')->get();
        $categories = EquipmentCategory::orderBy('name')->get();
        $types = $this->availableTypes($user);
        $locations = $this->availableLocations($user);

        return view('livewire.equipment.index', [
            'equipment' => $equipment,
            'organizations' => $organizations,
            'categories' => $categories,
            'user' => $user,
            'isClient' => $isClient,
            'summary' => $summary,
            'canManage' => $this->canManage,
            'types' => $types,
            'locations' => $locations,
        ]);
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->canManageEquipment();
    }

    private function statusValues(): array
    {
        return array_values(array_filter(array_keys($this->statusOptions), fn($status) => $status !== 'all'));
    }

    private function applySearch(Builder $query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $searchLike = '%' . $search . '%';

        $query->where(function (Builder $builder) use ($search, $searchLike) {
            $builder->where('name', 'like', $searchLike)
                ->orWhere('type', 'like', $searchLike)
                ->orWhere('manufacturer', 'like', $searchLike)
                ->orWhere('model', 'like', $searchLike)
                ->orWhere('serial_number', 'like', $searchLike)
                ->orWhere('asset_tag', 'like', $searchLike)
                ->orWhere('location_name', 'like', $searchLike)
                ->orWhere('location_address', 'like', $searchLike)
                ->orWhere('notes', 'like', $searchLike)
                ->orWhereHas('organization', function (Builder $orgBuilder) use ($searchLike) {
                    $orgBuilder->where('name', 'like', $searchLike);
                })
                ->orWhereHas('category', function (Builder $catBuilder) use ($searchLike) {
                    $catBuilder->where('name', 'like', $searchLike);
                });

            if (is_numeric($search)) {
                $builder->orWhere('id', (int) $search);
            }
        });
    }

    private function applySort(Builder $query): void
    {
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        match ($this->sortField) {
            'name' => $query->orderBy('name', $direction),
            'type' => $query->orderBy('type', $direction),
            'location_name' => $query->orderBy('location_name', $direction),
            'status' => $query->orderBy('status', $direction),
            'last_service_at' => $query->orderBy('last_service_at', $direction),
            default => $query->orderByDesc('updated_at'),
        };
    }

    private function availableTypes(User $user)
    {
        return $this->equipmentQueryFor($user)
            ->whereNotNull('type')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
    }

    private function availableLocations(User $user)
    {
        return $this->equipmentQueryFor($user)
            ->whereNotNull('location_name')
            ->select('location_name')
            ->distinct()
            ->orderBy('location_name')
            ->pluck('location_name');
    }

    private function equipmentQueryFor(User $user): Builder
    {
        $query = Equipment::query();

        if ($user->can(PermissionCatalog::EQUIPMENT_VIEW_ALL)) {
            return $query;
        }

        $hasScope = false;
        $query->where(function (Builder $builder) use ($user, &$hasScope) {
            if ($user->can(PermissionCatalog::EQUIPMENT_VIEW_ORG) && $user->organization_id) {
                $builder->orWhere('organization_id', $user->organization_id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::EQUIPMENT_VIEW_OWN)) {
                $builder->orWhere('assigned_user_id', $user->id);
                $hasScope = true;
            }
        });

        if (!$hasScope) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function buildSummary(Builder $query): array
    {
        return [
            'total' => (clone $query)->count(),
            'operational' => (clone $query)->where('status', 'operational')->count(),
            'needs_attention' => (clone $query)->where('status', 'needs_attention')->count(),
            'in_repair' => (clone $query)->where('status', 'in_repair')->count(),
            'retired' => (clone $query)->where('status', 'retired')->count(),
        ];
    }

    private function normalizeId(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeText(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function encodeJson(?array $data): string
    {
        return $data ? json_encode($data, JSON_PRETTY_PRINT) : '';
    }

    // ─── View Mode Methods ────────────────────────────────────────────

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['list', 'grid', 'location'])) {
            $this->inventoryView = $mode;
        }
    }

    // ─── Advanced Filter Methods ──────────────────────────────────────

    public function updatedAgeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedWarrantyFilter(): void
    {
        $this->resetPage();
    }

    public function updatedManufacturerFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOwnerFilter(): void
    {
        $this->resetPage();
    }

    private function applyAdvancedFilters(Builder $query): void
    {
        // Age filter (in years)
        if ($this->ageFilter !== '') {
            $years = (int) $this->ageFilter;
            $cutoffDate = Carbon::today()->subYears($years);

            match ($this->ageFilter) {
                '1' => $query->where('purchase_date', '>=', Carbon::today()->subYear()),
                '3' => $query->whereBetween('purchase_date', [Carbon::today()->subYears(3), Carbon::today()->subYear()]),
                '5' => $query->whereBetween('purchase_date', [Carbon::today()->subYears(5), Carbon::today()->subYears(3)]),
                '5+' => $query->where('purchase_date', '<', Carbon::today()->subYears(5)),
                default => null,
            };
        }

        // Warranty filter
        if ($this->warrantyFilter !== '') {
            match ($this->warrantyFilter) {
                'active' => $query->whereHas('warranties', fn($q) => $q->where('ends_at', '>=', now())),
                'expiring' => $query->whereHas('warranties', fn($q) => $q->whereBetween('ends_at', [now(), now()->addDays(30)])),
                'expired' => $query->whereDoesntHave('warranties', fn($q) => $q->where('ends_at', '>=', now())),
                'none' => $query->whereDoesntHave('warranties'),
                default => null,
            };
        }

        // Manufacturer filter
        if ($this->manufacturerFilter !== '') {
            $query->where(function ($q) {
                $q->where('manufacturer', $this->manufacturerFilter)
                    ->orWhereHas('manufacturer', fn($mq) => $mq->where('name', $this->manufacturerFilter));
            });
        }

        // Owner filter
        if ($this->ownerFilter !== '') {
            $query->where('assigned_user_id', $this->ownerFilter);
        }
    }

    private function getAvailableManufacturers(): array
    {
        return Equipment::query()
            ->whereNotNull('manufacturer')
            ->whereNot('manufacturer', '')
            ->select('manufacturer')
            ->distinct()
            ->orderBy('manufacturer')
            ->pluck('manufacturer')
            ->toArray();
    }

    // ─── Bulk Actions ─────────────────────────────────────────────────

    public function selectAll(): void
    {
        $user = auth()->user();
        $this->selected = $this->equipmentQueryFor($user)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function deselectAll(): void
    {
        $this->selected = [];
    }

    public function toggleSelection(int $id): void
    {
        $key = (string) $id;
        if (in_array($key, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$key]));
        } else {
            $this->selected[] = $key;
        }
    }

    public function executeBulkAction(): void
    {
        if (!$this->canManage || empty($this->selected)) {
            return;
        }

        $user = auth()->user();
        $ids = array_map('intval', $this->selected);

        $equipment = $this->equipmentQueryFor($user)
            ->whereIn('id', $ids)
            ->get();

        $updated = 0;

        foreach ($equipment as $item) {
            $changes = [];

            if ($this->bulkAction === 'change_status' && $this->bulkStatus) {
                $changes['status'] = $this->bulkStatus;
            }

            if ($this->bulkAction === 'change_location' && $this->bulkLocation !== '') {
                $changes['location_name'] = $this->bulkLocation;
            }

            if ($this->bulkAction === 'change_owner' && $this->bulkOwner) {
                $changes['assigned_user_id'] = $this->bulkOwner;
            }

            if (!empty($changes)) {
                $item->update($changes);
                $updated++;
            }
        }

        $this->selected = [];
        $this->bulkAction = '';
        $this->bulkStatus = null;
        $this->bulkLocation = '';
        $this->bulkOwner = null;

        session()->flash('status', "Updated {$updated} equipment items.");
    }

    // ─── Export ───────────────────────────────────────────────────────

    public function exportCsv()
    {
        $user = auth()->user();
        $query = $this->equipmentQueryFor($user)->with(['organization', 'category', 'manufacturer']);

        // Apply current filters
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        if ($this->categoryFilter !== '') {
            $query->where('equipment_category_id', $this->categoryFilter);
        }
        if ($this->search !== '') {
            $this->applySearch($query, $this->search);
        }

        // Use selected items if any
        if (!empty($this->selected)) {
            $ids = array_map('intval', $this->selected);
            $query->whereIn('id', $ids);
        }

        $service = app(\App\Services\EquipmentImportExportService::class);
        $csv = $service->exportToCsv($query->get());

        $filename = 'equipment_export_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ─── Import ───────────────────────────────────────────────────────

    public $importFile = null;
    public array $importFieldMapping = [];
    public array $importPreview = [];
    public array $importResult = [];

    public function openImport(): void
    {
        if (!$this->canManage) {
            return;
        }

        $this->showImport = true;
        $this->importFile = null;
        $this->importFieldMapping = [];
        $this->importPreview = [];
        $this->importResult = [];
    }

    public function closeImport(): void
    {
        $this->showImport = false;
        $this->importFile = null;
        $this->importFieldMapping = [];
        $this->importPreview = [];
        $this->importResult = [];
    }

    public function updatedImportFile(): void
    {
        if (!$this->importFile) {
            return;
        }

        // Read first few rows for preview
        $handle = fopen($this->importFile->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $previewRows = [];

        for ($i = 0; $i < 3 && ($row = fgetcsv($handle)) !== false; $i++) {
            $previewRows[] = array_combine($headers, $row);
        }

        fclose($handle);

        $this->importPreview = [
            'headers' => $headers,
            'rows' => $previewRows,
        ];

        // Auto-suggest field mapping
        $service = app(\App\Services\EquipmentImportExportService::class);
        $availableFields = $service->getAvailableImportFields();

        foreach ($headers as $header) {
            $normalizedHeader = strtolower(str_replace([' ', '-'], '_', $header));
            foreach ($availableFields as $field => $config) {
                if ($normalizedHeader === $field || str_contains($normalizedHeader, $field)) {
                    $this->importFieldMapping[$header] = $field;
                    break;
                }
            }
        }
    }

    public function executeImport(): void
    {
        if (!$this->canManage || !$this->importFile) {
            return;
        }

        $service = app(\App\Services\EquipmentImportExportService::class);

        try {
            $this->importResult = $service->importFromCsv(
                $this->importFile,
                $this->importFieldMapping,
                auth()->id()
            );

            if ($this->importResult['imported'] > 0) {
                session()->flash('status', sprintf(
                    'Successfully imported %d equipment items.',
                    $this->importResult['imported']
                ));
            }
        } catch (\Exception $e) {
            $this->importResult = [
                'error' => $e->getMessage(),
                'imported' => 0,
                'skipped' => 0,
            ];
        }
    }

    public function downloadSampleCsv()
    {
        $service = app(\App\Services\EquipmentImportExportService::class);
        $csv = $service->generateSampleCsv();

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'equipment_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ─── Location Hierarchy ───────────────────────────────────────────

    public function getLocationHierarchy(): array
    {
        $user = auth()->user();
        $equipment = $this->equipmentQueryFor($user)
            ->whereNotNull('location_building')
            ->select('location_building', 'location_floor', 'location_room')
            ->selectRaw('COUNT(*) as equipment_count')
            ->groupBy('location_building', 'location_floor', 'location_room')
            ->get();

        $hierarchy = [];

        foreach ($equipment as $item) {
            $building = $item->location_building ?? 'Unknown';
            $floor = $item->location_floor ?? 'Unknown';
            $room = $item->location_room ?? 'Unknown';

            if (!isset($hierarchy[$building])) {
                $hierarchy[$building] = ['floors' => [], 'count' => 0];
            }
            if (!isset($hierarchy[$building]['floors'][$floor])) {
                $hierarchy[$building]['floors'][$floor] = ['rooms' => [], 'count' => 0];
            }
            if (!isset($hierarchy[$building]['floors'][$floor]['rooms'][$room])) {
                $hierarchy[$building]['floors'][$floor]['rooms'][$room] = 0;
            }

            $hierarchy[$building]['floors'][$floor]['rooms'][$room] += $item->equipment_count;
            $hierarchy[$building]['floors'][$floor]['count'] += $item->equipment_count;
            $hierarchy[$building]['count'] += $item->equipment_count;
        }

        return $hierarchy;
    }
}
