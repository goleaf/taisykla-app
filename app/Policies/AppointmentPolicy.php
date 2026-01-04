<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use App\Support\PermissionCatalog;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionCatalog::SCHEDULE_VIEW) ||
               $user->can(PermissionCatalog::SCHEDULE_VIEW_ALL) ||
               $user->can(PermissionCatalog::SCHEDULE_VIEW_ASSIGNED) ||
               $user->can(PermissionCatalog::SCHEDULE_VIEW_ORG) ||
               $user->can(PermissionCatalog::SCHEDULE_VIEW_OWN);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->can(PermissionCatalog::SCHEDULE_VIEW_ALL)) {
            return true;
        }

        if ($user->can(PermissionCatalog::SCHEDULE_VIEW_ASSIGNED) && $appointment->assigned_to_user_id === $user->id) {
            return true;
        }

        // Load work order if not loaded to check organization/ownership
        $appointment->loadMissing('workOrder');

        if ($user->can(PermissionCatalog::SCHEDULE_VIEW_ORG) && $appointment->workOrder && $appointment->workOrder->organization_id === $user->organization_id) {
            return true;
        }

        if ($user->can(PermissionCatalog::SCHEDULE_VIEW_OWN) && $appointment->workOrder && $appointment->workOrder->requested_by_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionCatalog::SCHEDULE_MANAGE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can(PermissionCatalog::SCHEDULE_MANAGE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->can(PermissionCatalog::SCHEDULE_MANAGE);
    }
}
