<?php

namespace App\Livewire\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Organization;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public array $form = [];
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $search = '';
    public string $statusFilter = 'all';
    public string $categoryFilter = '';
    public string $organizationFilter = '';
    public string $typeFilter = '';
    public string $locationFilter = '';
    public string $sortField = 'last_service_at';
    public string $sortDirection = 'desc';

    protected $paginationTheme = 'tailwind';

    public array $statusOptions = [
        'all' => 'All',
        'operational' => 'Operational',
        'needs_attention' => 'Needs Attention',
        'in_repair' => 'In Repair',
        'retired' => 'Retired',
    ];

    public function mount(): void
    {
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
            'status' => 'operational',
            'location_name' => '',
            'location_address' => '',
            'notes' => '',
        ];
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
        if (! $this->canManage) {
            return;
        }

        $this->editingId = null;
        $this->resetForm();
        $this->showForm = true;
    }

    public function editEquipment(int $equipmentId): void
    {
        if (! $this->canManage) {
            return;
        }

        $user = auth()->user();
        $query = Equipment::query();

        if ($user->isBusinessCustomer()) {
            $query->where('organization_id', $user->organization_id);
        } elseif ($user->isConsumer()) {
            $query->where('assigned_user_id', $user->id);
        }

        $equipment = $query->findOrFail($equipmentId);
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
            'status' => $equipment->status,
            'location_name' => $equipment->location_name ?? '',
            'location_address' => $equipment->location_address ?? '',
            'notes' => $equipment->notes ?? '',
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
            'form.equipment_category_id' => ['nullable', 'exists:equipment_categories,id'],
            'form.name' => ['required', 'string', 'max:255'],
            'form.type' => ['required', 'string', 'max:255'],
            'form.manufacturer' => ['nullable', 'string', 'max:255'],
            'form.model' => ['nullable', 'string', 'max:255'],
            'form.serial_number' => ['nullable', 'string', 'max:255'],
            'form.asset_tag' => ['nullable', 'string', 'max:255'],
            'form.purchase_date' => ['nullable', 'date'],
            'form.status' => ['required', Rule::in($this->statusValues())],
            'form.location_name' => ['nullable', 'string', 'max:255'],
            'form.location_address' => ['nullable', 'string', 'max:1000'],
            'form.notes' => ['nullable', 'string'],
        ];
    }

    public function saveEquipment(): void
    {
        if (! $this->canManage) {
            return;
        }

        $user = auth()->user();
        if (! $user) {
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

        $query = Equipment::query()
            ->with(['organization', 'category'])
            ->withMax(['workOrders as last_service_at' => function (Builder $builder) {
                $builder->whereNotNull('completed_at')
                    ->whereIn('status', ['completed', 'closed']);
            }], 'completed_at');
        if ($isBusinessCustomer) {
            $query->where('organization_id', $user->organization_id);
        } elseif ($isConsumer) {
            $query->where('assigned_user_id', $user->id);
        }

        if (! $isClient && $this->organizationFilter !== '') {
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

        if (! $user) {
            return false;
        }

        return ! $user->isReadOnly();
    }

    private function statusValues(): array
    {
        return array_values(array_filter(array_keys($this->statusOptions), fn ($status) => $status !== 'all'));
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
        return Equipment::query()
            ->when($user->isBusinessCustomer(), fn ($builder) => $builder->where('organization_id', $user->organization_id))
            ->when($user->isConsumer(), fn ($builder) => $builder->where('assigned_user_id', $user->id))
            ->whereNotNull('type')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
    }

    private function availableLocations(User $user)
    {
        return Equipment::query()
            ->when($user->isBusinessCustomer(), fn ($builder) => $builder->where('organization_id', $user->organization_id))
            ->when($user->isConsumer(), fn ($builder) => $builder->where('assigned_user_id', $user->id))
            ->whereNotNull('location_name')
            ->select('location_name')
            ->distinct()
            ->orderBy('location_name')
            ->pluck('location_name');
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
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value === '' ? null : $value;
    }
}
