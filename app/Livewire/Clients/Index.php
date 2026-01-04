<?php

namespace App\Livewire\Clients;

use App\Models\Organization;
use App\Models\ServiceAgreement;
use App\Support\PermissionCatalog;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showCreate = false;
    public array $new = [];
    public string $search = '';
    public string $statusFilter = 'all';
    public string $typeFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'typeFilter' => ['except' => 'all'],
    ];

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::CLIENTS_VIEW), 403);

        $this->resetNew();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->typeFilter = 'all';
        $this->resetPage();
    }

    public function resetNew(): void
    {
        $this->new = [
            'name' => '',
            'type' => 'business',
            'status' => 'active',
            'primary_contact_name' => '',
            'primary_contact_email' => '',
            'primary_contact_phone' => '',
            'billing_email' => '',
            'billing_address' => '',
            'service_agreement_id' => null,
            'notes' => '',
        ];
    }

    public function createOrganization(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::CLIENTS_MANAGE)) {
            return;
        }

        $this->validate([
            'new.name' => ['required', 'string', 'max:255'],
            'new.type' => ['required', 'string', 'max:50'],
            'new.status' => ['required', 'string', 'max:50'],
            'new.primary_contact_name' => ['nullable', 'string', 'max:255'],
            'new.primary_contact_email' => ['nullable', 'email', 'max:255'],
            'new.primary_contact_phone' => ['nullable', 'string', 'max:50'],
            'new.billing_email' => ['nullable', 'email', 'max:255'],
            'new.billing_address' => ['nullable', 'string', 'max:1000'],
            'new.service_agreement_id' => ['nullable', 'exists:service_agreements,id'],
            'new.notes' => ['nullable', 'string'],
        ]);

        Organization::create($this->new);
        session()->flash('status', 'Client organization created.');
        $this->resetNew();
        $this->showCreate = false;
    }

    public function render()
    {
        $query = Organization::query()->with('serviceAgreement');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        if ($this->search !== '') {
            $searchLike = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchLike) {
                $q->where('name', 'like', $searchLike)
                    ->orWhere('primary_contact_name', 'like', $searchLike)
                    ->orWhere('primary_contact_email', 'like', $searchLike)
                    ->orWhere('billing_email', 'like', $searchLike);
            });
        }

        $organizations = $query->latest()->paginate(10);
        $agreements = ServiceAgreement::orderBy('name')->get();

        return view('livewire.clients.index', [
            'organizations' => $organizations,
            'agreements' => $agreements,
            'canManage' => $this->canManage,
        ]);
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(PermissionCatalog::CLIENTS_MANAGE);
    }
}
