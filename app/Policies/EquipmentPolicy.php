<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;
use App\Support\PermissionCatalog;

class EquipmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionCatalog::EQUIPMENT_VIEW) ||
               $user->can(PermissionCatalog::EQUIPMENT_VIEW_ALL) ||
               $user->can(PermissionCatalog::EQUIPMENT_VIEW_ORG) ||
               $user->can(PermissionCatalog::EQUIPMENT_VIEW_OWN);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Equipment $equipment): bool
    {
        if ($user->can(PermissionCatalog::EQUIPMENT_VIEW_ALL)) {
            return true;
        }

        if ($user->can(PermissionCatalog::EQUIPMENT_VIEW_ORG) && $equipment->organization_id === $user->organization_id) {
            return true;
        }

        if ($user->can(PermissionCatalog::EQUIPMENT_VIEW_OWN) && $equipment->assigned_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionCatalog::EQUIPMENT_MANAGE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Equipment $equipment): bool
    {
        return $user->can(PermissionCatalog::EQUIPMENT_MANAGE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->can(PermissionCatalog::EQUIPMENT_MANAGE);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Equipment $equipment): bool
    {
        return $user->hasRole(\App\Support\RoleCatalog::ADMIN);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Equipment $equipment): bool
    {
        return $user->hasRole(\App\Support\RoleCatalog::ADMIN);
    }
}
