<?php

namespace App\Livewire;

use App\Models\Equipment;
use App\Models\Organization;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';
    public bool $isOpen = false;

    public function updatedQuery()
    {
        // No-op, results are computed
    }

    public function getResultsProperty(): array
    {
        if (strlen($this->query) < 2) {
            return [];
        }

        $search = trim($this->query);
        $like = '%' . $search . '%';

        $workOrders = WorkOrder::query()
            ->where('id', $search) // Exact ID match
            ->orWhere('subject', 'like', $like)
            ->orWhere('description', 'like', $like)
            ->limit(5)
            ->get();

        $customers = Organization::query()
            ->where('name', 'like', $like)
            ->orWhere('primary_contact_name', 'like', $like)
            ->orWhere('primary_contact_email', 'like', $like)
            ->limit(5)
            ->get();

        $equipment = Equipment::query()
            ->where('name', 'like', $like)
            ->orWhere('serial_number', 'like', $like)
            ->orWhere('model', 'like', $like)
            ->orWhere('asset_tag', 'like', $like)
            ->limit(5)
            ->get();

        return [
            'workOrders' => $workOrders,
            'customers' => $customers,
            'equipment' => $equipment,
        ];
    }

    public function open()
    {
        $this->isOpen = true;
        $this->js("setTimeout(() => \$refs.searchInput.focus(), 100)");
    }

    public function close()
    {
        $this->isOpen = false;
        $this->query = '';
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
