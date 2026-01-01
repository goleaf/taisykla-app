<?php

namespace App\Livewire\Settings;

use App\Models\CommunicationTemplate;
use App\Models\InventoryLocation;
use App\Models\Organization;
use App\Models\ServiceAgreement;
use App\Models\User;
use App\Models\WorkOrderCategory;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public bool $showUserCreate = false;
    public bool $showAgreementCreate = false;
    public bool $showCategoryCreate = false;
    public bool $showTemplateCreate = false;
    public bool $showLocationCreate = false;

    public array $newUser = [];
    public array $newAgreement = [];
    public array $newCategory = [];
    public array $newTemplate = [];
    public array $newLocation = [];

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetNewUser();
        $this->resetNewAgreement();
        $this->resetNewCategory();
        $this->resetNewTemplate();
        $this->resetNewLocation();
    }

    public function resetNewUser(): void
    {
        $this->newUser = [
            'name' => '',
            'email' => '',
            'role' => 'client',
            'organization_id' => null,
        ];
    }

    public function resetNewAgreement(): void
    {
        $this->newAgreement = [
            'name' => '',
            'agreement_type' => 'pay_per_service',
            'response_time_minutes' => null,
            'resolution_time_minutes' => null,
            'included_visits_per_month' => null,
            'monthly_fee' => 0,
            'includes_parts' => false,
            'includes_labor' => false,
            'billing_terms' => '',
            'coverage_details' => '',
            'is_active' => true,
        ];
    }

    public function resetNewCategory(): void
    {
        $this->newCategory = [
            'name' => '',
            'description' => '',
            'default_estimated_minutes' => null,
            'is_active' => true,
        ];
    }

    public function resetNewTemplate(): void
    {
        $this->newTemplate = [
            'name' => '',
            'channel' => 'email',
            'subject' => '',
            'body' => '',
            'is_active' => true,
        ];
    }

    public function resetNewLocation(): void
    {
        $this->newLocation = [
            'name' => '',
            'address' => '',
            'notes' => '',
        ];
    }

    public function createUser(): void
    {
        $this->validate([
            'newUser.name' => ['required', 'string', 'max:255'],
            'newUser.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'newUser.role' => ['required', 'string', 'exists:roles,name'],
            'newUser.organization_id' => ['nullable', 'exists:organizations,id'],
        ]);

        $user = User::create([
            'name' => $this->newUser['name'],
            'email' => $this->newUser['email'],
            'password' => Hash::make('password'),
            'organization_id' => $this->newUser['organization_id'],
            'is_active' => true,
        ]);

        $user->assignRole($this->newUser['role']);

        session()->flash('status', 'User created with default password: password');
        $this->resetNewUser();
        $this->showUserCreate = false;
    }

    public function createAgreement(): void
    {
        $this->validate([
            'newAgreement.name' => ['required', 'string', 'max:255'],
            'newAgreement.agreement_type' => ['required', 'string', 'max:50'],
            'newAgreement.monthly_fee' => ['required', 'numeric', 'min:0'],
        ]);

        ServiceAgreement::create($this->newAgreement);
        session()->flash('status', 'Service agreement created.');
        $this->resetNewAgreement();
        $this->showAgreementCreate = false;
    }

    public function createCategory(): void
    {
        $this->validate([
            'newCategory.name' => ['required', 'string', 'max:255'],
            'newCategory.default_estimated_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        WorkOrderCategory::create($this->newCategory);
        session()->flash('status', 'Work category created.');
        $this->resetNewCategory();
        $this->showCategoryCreate = false;
    }

    public function createTemplate(): void
    {
        $this->validate([
            'newTemplate.name' => ['required', 'string', 'max:255'],
            'newTemplate.channel' => ['required', 'string', 'max:50'],
            'newTemplate.subject' => ['nullable', 'string', 'max:255'],
            'newTemplate.body' => ['required', 'string'],
        ]);

        CommunicationTemplate::create([
            'name' => $this->newTemplate['name'],
            'channel' => $this->newTemplate['channel'],
            'subject' => $this->newTemplate['subject'],
            'body' => $this->newTemplate['body'],
            'is_active' => $this->newTemplate['is_active'],
            'created_by_user_id' => auth()->id(),
        ]);

        session()->flash('status', 'Template created.');
        $this->resetNewTemplate();
        $this->showTemplateCreate = false;
    }

    public function createLocation(): void
    {
        $this->validate([
            'newLocation.name' => ['required', 'string', 'max:255'],
            'newLocation.address' => ['nullable', 'string', 'max:1000'],
            'newLocation.notes' => ['nullable', 'string'],
        ]);

        InventoryLocation::create($this->newLocation);
        session()->flash('status', 'Inventory location created.');
        $this->resetNewLocation();
        $this->showLocationCreate = false;
    }

    public function render()
    {
        $users = User::with('organization')->latest()->paginate(10, pageName: 'users');
        $roles = Role::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();
        $agreements = ServiceAgreement::orderBy('name')->paginate(10, pageName: 'agreements');
        $categories = WorkOrderCategory::orderBy('name')->paginate(10, pageName: 'categories');
        $templates = CommunicationTemplate::orderBy('name')->paginate(10, pageName: 'templates');
        $locations = InventoryLocation::orderBy('name')->paginate(10, pageName: 'locations');

        return view('livewire.settings.index', [
            'users' => $users,
            'roles' => $roles,
            'organizations' => $organizations,
            'agreements' => $agreements,
            'categories' => $categories,
            'templates' => $templates,
            'locations' => $locations,
        ]);
    }
}
