<?php

namespace App\Policies;

use App\Models\Part;
use App\Models\User;
use App\Support\PermissionCatalog;

class PartPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionCatalog::INVENTORY_VIEW) || $user->can(PermissionCatalog::INVENTORY_MANAGE);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Part $part): bool
    {
        return $user->can(PermissionCatalog::INVENTORY_VIEW) || $user->can(PermissionCatalog::INVENTORY_MANAGE);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionCatalog::INVENTORY_MANAGE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Part $part): bool
    {
        return $user->can(PermissionCatalog::INVENTORY_MANAGE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Part $part): bool
    {
        return $user->can(PermissionCatalog::INVENTORY_MANAGE);
    }
}
