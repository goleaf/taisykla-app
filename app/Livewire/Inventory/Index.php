<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Part;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showNewPart = false;
    public bool $showStock = false;
    public array $newPart = [];
    public array $newStock = [];

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetNewPart();
        $this->resetNewStock();
    }

    public function resetNewPart(): void
    {
        $this->newPart = [
            'sku' => '',
            'name' => '',
            'description' => '',
            'unit_cost' => 0,
            'unit_price' => 0,
            'vendor' => '',
            'reorder_level' => 0,
        ];
    }

    public function resetNewStock(): void
    {
        $this->newStock = [
            'part_id' => null,
            'location_id' => null,
            'quantity' => 0,
        ];
    }

    public function createPart(): void
    {
        $this->validate([
            'newPart.name' => ['required', 'string', 'max:255'],
            'newPart.sku' => ['nullable', 'string', 'max:255'],
            'newPart.description' => ['nullable', 'string'],
            'newPart.unit_cost' => ['required', 'numeric', 'min:0'],
            'newPart.unit_price' => ['required', 'numeric', 'min:0'],
            'newPart.vendor' => ['nullable', 'string', 'max:255'],
            'newPart.reorder_level' => ['required', 'integer', 'min:0'],
        ]);

        Part::create($this->newPart);
        session()->flash('status', 'Part added.');
        $this->resetNewPart();
        $this->showNewPart = false;
    }

    public function addStock(): void
    {
        $this->validate([
            'newStock.part_id' => ['required', 'exists:parts,id'],
            'newStock.location_id' => ['required', 'exists:inventory_locations,id'],
            'newStock.quantity' => ['required', 'integer'],
        ]);

        $item = InventoryItem::firstOrCreate([
            'part_id' => $this->newStock['part_id'],
            'location_id' => $this->newStock['location_id'],
        ], [
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);

        $item->update([
            'quantity' => $item->quantity + $this->newStock['quantity'],
        ]);

        session()->flash('status', 'Inventory updated.');
        $this->resetNewStock();
        $this->showStock = false;
    }

    public function render()
    {
        $parts = Part::orderBy('name')->paginate(10, pageName: 'parts');
        $inventory = InventoryItem::with(['part', 'location'])
            ->orderByDesc('quantity')
            ->paginate(10, pageName: 'inventory');
        $locations = InventoryLocation::orderBy('name')->get();

        return view('livewire.inventory.index', [
            'parts' => $parts,
            'inventory' => $inventory,
            'locations' => $locations,
        ]);
    }
}
