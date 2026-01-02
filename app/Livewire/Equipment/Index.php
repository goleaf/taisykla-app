<?php

namespace App\Livewire\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Organization;
use App\Services\AuditLogger;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public array $new = [];
    public bool $showCreate = false;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetNew();
    }

    public function resetNew(): void
    {
        $user = auth()->user();
        $this->new = [
            'organization_id' => $user->organization_id,
            'equipment_category_id' => null,
            'name' => '',
            'type' => '',
            'manufacturer' => '',
            'model' => '',
            'serial_number' => '',
            'status' => 'operational',
            'location_name' => '',
            'location_address' => '',
        ];
    }

    public function createEquipment(): void
    {
        $user = auth()->user();

        if ($user->hasRole('client') && $user->organization_id) {
            $this->new['organization_id'] = $user->organization_id;
        }

        $this->validate([
            'new.organization_id' => ['nullable', 'exists:organizations,id'],
            'new.equipment_category_id' => ['nullable', 'exists:equipment_categories,id'],
            'new.name' => ['required', 'string', 'max:255'],
            'new.type' => ['required', 'string', 'max:255'],
            'new.manufacturer' => ['nullable', 'string', 'max:255'],
            'new.model' => ['nullable', 'string', 'max:255'],
            'new.serial_number' => ['nullable', 'string', 'max:255'],
            'new.status' => ['required', 'string', 'max:50'],
            'new.location_name' => ['nullable', 'string', 'max:255'],
            'new.location_address' => ['nullable', 'string', 'max:1000'],
        ]);

        $equipment = Equipment::create($this->new);
        app(AuditLogger::class)->log(
            'equipment.created',
            $equipment,
            'Equipment created.',
            ['name' => $equipment->name]
        );
        session()->flash('status', 'Equipment added.');
        $this->resetNew();
        $this->showCreate = false;
    }

    public function render()
    {
        $user = auth()->user();

        $query = Equipment::query()->with('organization');
        if ($user->hasRole('client')) {
            $query->where('organization_id', $user->organization_id);
        }

        $equipment = $query->latest()->paginate(10);
        $organizations = Organization::orderBy('name')->get();
        $categories = EquipmentCategory::orderBy('name')->get();

        return view('livewire.equipment.index', [
            'equipment' => $equipment,
            'organizations' => $organizations,
            'categories' => $categories,
            'user' => $user,
        ]);
    }
}
