<?php

namespace App\Livewire\Equipment;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Show extends Component
{
    public Equipment $equipment;

    public function mount(Equipment $equipment): void
    {
        $user = auth()->user();
        if (! $this->canViewEquipment($user, $equipment)) {
            abort(403);
        }

        $this->equipment = $equipment->load([
            'organization',
            'category',
            'assignedUser',
            'warranties.claims',
            'workOrders.assignedTo',
            'workOrders.parts.part',
            'attachments',
        ]);
    }

    private function canViewEquipment(?User $user, Equipment $equipment): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'dispatch', 'support', 'technician'])) {
            return true;
        }

        if ($user->hasRole('client')) {
            return $user->organization_id && $equipment->organization_id === $user->organization_id;
        }

        return false;
    }

    private function maintenanceHistory()
    {
        return $this->equipment->workOrders
            ->sortByDesc(fn ($order) => $order->completed_at ?? $order->created_at)
            ->values();
    }

    private function healthSnapshot(): array
    {
        $ageYears = $this->equipment->purchase_date
            ? $this->equipment->purchase_date->diffInYears(Carbon::today())
            : null;

        $serviceCount = $this->equipment->workOrders
            ->whereIn('status', ['completed', 'closed'])
            ->count();

        if ($this->equipment->status === 'retired') {
            return [
                'label' => 'Retired',
                'score' => 0,
                'summary' => 'This device is retired from service.',
                'age_years' => $ageYears,
                'service_count' => $serviceCount,
            ];
        }

        $score = 100;
        if ($ageYears !== null) {
            $score -= min(60, $ageYears * 8);
        }
        $score -= min(30, $serviceCount * 5);

        if ($this->equipment->status === 'needs_attention') {
            $score -= 10;
        }
        if ($this->equipment->status === 'in_repair') {
            $score -= 15;
        }

        $score = max(0, $score);

        $label = match (true) {
            $score >= 75 => 'Good',
            $score >= 50 => 'Monitor',
            default => 'End of Life',
        };

        $summary = match ($label) {
            'Good' => 'Device is operating within expected lifecycle.',
            'Monitor' => 'Device shows signs of wear; plan preventive maintenance.',
            default => 'Device is nearing end of lifecycle. Consider replacement.',
        };

        return [
            'label' => $label,
            'score' => $score,
            'summary' => $summary,
            'age_years' => $ageYears,
            'service_count' => $serviceCount,
        ];
    }

    public function render()
    {
        $maintenanceHistory = $this->maintenanceHistory();
        $health = $this->healthSnapshot();
        $lastService = $maintenanceHistory->first()?->completed_at;

        return view('livewire.equipment.show', [
            'maintenanceHistory' => $maintenanceHistory,
            'health' => $health,
            'lastService' => $lastService,
        ]);
    }
}
