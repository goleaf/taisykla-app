<?php

namespace App\Livewire\Customer;

use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TrackTechnician extends Component
{
    public WorkOrder $workOrder;

    public ?float $technicianLat = null;
    public ?float $technicianLng = null;
    public ?string $technicianStatus = null;
    public ?string $eta = null;
    public bool $isLive = false;

    protected $listeners = ['refreshTracking' => 'loadTechnicianLocation'];

    public function mount(WorkOrder $workOrder): void
    {
        $user = auth()->user();
        abort_unless($user?->can(PermissionCatalog::WORK_ORDERS_VIEW), 403);

        // Verify access to this work order
        $canAccess = $user->can(PermissionCatalog::WORK_ORDERS_VIEW_ALL)
            || ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ORG) && $workOrder->organization_id === $user->organization_id)
            || ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_OWN) && $workOrder->requested_by_user_id === $user->id);

        abort_unless($canAccess, 403);

        $this->workOrder = $workOrder->load(['assignedTo', 'organization', 'equipment']);
        $this->loadTechnicianLocation();
    }

    public function loadTechnicianLocation(): void
    {
        if (!$this->workOrder->assignedTo) {
            return;
        }

        $technician = $this->workOrder->assignedTo;

        $this->technicianLat = $technician->current_latitude;
        $this->technicianLng = $technician->current_longitude;
        $this->technicianStatus = $technician->availability_status;

        // Determine if tracking is live (technician is en route)
        $this->isLive = in_array($this->workOrder->status, ['assigned', 'in_progress'])
            && $this->technicianLat
            && $this->technicianLng;

        // Calculate rough ETA based on distance
        if ($this->isLive && $this->workOrder->location_latitude && $this->workOrder->location_longitude) {
            $distance = $this->haversineDistance(
                $this->technicianLat,
                $this->technicianLng,
                $this->workOrder->location_latitude,
                $this->workOrder->location_longitude
            );

            // Assume average speed of 40 km/h in urban area
            $minutes = ceil(($distance / 40) * 60);
            $this->eta = $minutes > 60
                ? floor($minutes / 60) . 'h ' . ($minutes % 60) . 'm'
                : $minutes . ' min';
        }
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function render()
    {
        return view('livewire.customer.track-technician');
    }
}
