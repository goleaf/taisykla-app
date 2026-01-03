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

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::CLIENTS_VIEW), 403);

        $this->resetNew();
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
        $organizations = Organization::latest()->paginate(10);
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
