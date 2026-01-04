<?php

namespace App\Livewire\Profile;

use App\Support\RoleCatalog;
use Illuminate\Support\Collection;
use Livewire\Component;

class AccessPermissions extends Component
{
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $roles = $user->roles->map(function ($role) {
            return [
                'name' => $role->name,
                'label' => RoleCatalog::label($role->name),
                'permissions_count' => $role->permissions()->count(),
            ];
        });

        // Get all unique permissions from all roles + direct permissions
        $permissions = $user->getAllPermissions()->groupBy(function ($permission) {
            // Group logic: "work_orders.view" -> "Work Orders"
            $parts = explode('.', $permission->name);
            $group = count($parts) > 1 ? \Illuminate\Support\Str::headline($parts[0]) : 'General';
            return $group;
        })->sortKeys();

        return view('livewire.profile.access-permissions', [
            'roles' => $roles,
            'groupedPermissions' => $permissions,
        ]);
    }
}
