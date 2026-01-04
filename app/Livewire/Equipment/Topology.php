<?php

namespace App\Livewire\Equipment;

use App\Models\Equipment;
use App\Services\EquipmentTopologyService;
use App\Support\PermissionCatalog;
use Livewire\Component;

class Topology extends Component
{
    public ?int $selectedEquipmentId = null;
    public string $viewMode = 'network'; // network, hierarchy, subnet
    public string $subnetFilter = '';
    public ?array $impactAnalysis = null;
    public ?array $dependencyChain = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::EQUIPMENT_VIEW), 403);
    }

    public function selectEquipment(int $equipmentId): void
    {
        $this->selectedEquipmentId = $equipmentId;
        $this->loadImpactAnalysis();
    }

    public function clearSelection(): void
    {
        $this->selectedEquipmentId = null;
        $this->impactAnalysis = null;
        $this->dependencyChain = null;
    }

    public function loadImpactAnalysis(): void
    {
        if (!$this->selectedEquipmentId) {
            $this->impactAnalysis = null;
            $this->dependencyChain = null;
            return;
        }

        $equipment = Equipment::find($this->selectedEquipmentId);
        if (!$equipment) {
            return;
        }

        $service = app(EquipmentTopologyService::class);
        $this->impactAnalysis = $service->getImpactAnalysis($equipment);
        $this->dependencyChain = $service->getDependencyChain($equipment);
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['network', 'hierarchy', 'subnet'])) {
            $this->viewMode = $mode;
            $this->clearSelection();
        }
    }

    public function render()
    {
        $service = app(EquipmentTopologyService::class);
        $user = auth()->user();
        $organizationId = $user?->organization_id;

        $topology = $service->getNetworkTopology($organizationId);
        $subnets = $service->getAvailableSubnets();

        // Get subnet equipment if filter is set
        $subnetEquipment = null;
        if ($this->subnetFilter && $this->viewMode === 'subnet') {
            $subnetEquipment = $service->getBySubnet($this->subnetFilter);
        }

        return view('livewire.equipment.topology', [
            'topology' => $topology,
            'subnets' => $subnets,
            'subnetEquipment' => $subnetEquipment,
            'selectedEquipment' => $this->selectedEquipmentId
                ? Equipment::with(['manufacturer', 'category', 'parent'])->find($this->selectedEquipmentId)
                : null,
        ]);
    }
}
