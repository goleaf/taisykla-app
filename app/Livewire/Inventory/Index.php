<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Part;
use App\Support\PermissionCatalog;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

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
        abort_unless(auth()->user()?->can(PermissionCatalog::INVENTORY_VIEW), 403);

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
        if (! auth()->user()?->can(PermissionCatalog::INVENTORY_MANAGE)) {
            return;
        }

        $this->validate([
            'newPart.name' => ['required', 'string', 'min:2', 'max:255'],
            'newPart.sku' => ['required', 'string', 'max:100', 'alpha_dash:ascii', Rule::unique('parts', 'sku')],
            'newPart.description' => ['nullable', 'string', 'max:1000'],
            'newPart.unit_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'newPart.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'newPart.vendor' => ['nullable', 'string', 'max:255'],
            'newPart.reorder_level' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        Part::create($this->newPart);
        session()->flash('status', 'Part added.');
        $this->resetNewPart();
        $this->showNewPart = false;
    }

    public function addStock(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::INVENTORY_MANAGE)) {
            return;
        }

        $this->validate([
            'newStock.part_id' => ['required', 'integer', 'exists:parts,id'],
            'newStock.location_id' => ['required', 'integer', 'exists:inventory_locations,id'],
            'newStock.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
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
        $lowStockParts = Part::query()
            ->leftJoin('inventory_items', 'parts.id', '=', 'inventory_items.part_id')
            ->select('parts.id', 'parts.name', 'parts.reorder_level', DB::raw('COALESCE(SUM(inventory_items.quantity), 0) as on_hand'))
            ->groupBy('parts.id', 'parts.name', 'parts.reorder_level')
            ->havingRaw('COALESCE(SUM(inventory_items.quantity), 0) <= parts.reorder_level')
            ->orderBy('on_hand')
            ->get();

        return view('livewire.inventory.index', [
            'parts' => $parts,
            'inventory' => $inventory,
            'locations' => $locations,
            'lowStockParts' => $lowStockParts,
            'canManage' => $this->canManage,
        ]);
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(PermissionCatalog::INVENTORY_MANAGE);
    }
}
