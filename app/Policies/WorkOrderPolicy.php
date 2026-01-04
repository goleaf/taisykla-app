<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use Illuminate\Auth\Access\Response;

class WorkOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionCatalog::WORK_ORDERS_VIEW) ||
               $user->can(PermissionCatalog::WORK_ORDERS_VIEW_ALL) ||
               $user->can(PermissionCatalog::WORK_ORDERS_VIEW_ASSIGNED) ||
               $user->can(PermissionCatalog::WORK_ORDERS_VIEW_ORG) ||
               $user->can(PermissionCatalog::WORK_ORDERS_VIEW_OWN);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkOrder $workOrder): bool
    {
        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ALL)) {
            return true;
        }

        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ASSIGNED) && $workOrder->assigned_to_user_id === $user->id) {
            return true;
        }

        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ORG) && $workOrder->organization_id === $user->organization_id) {
            return true;
        }

        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_OWN) && $workOrder->requested_by_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionCatalog::WORK_ORDERS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkOrder $workOrder): bool
    {
        // General update permission
        if ($user->can(PermissionCatalog::WORK_ORDERS_UPDATE)) {
            return true;
        }
        
        // Users can usually update their own work orders if they are just adding notes or feedback, 
        // but core updates are restricted. 
        // For now, strictly follow the catalog.
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkOrder $workOrder): bool
    {
        // No explicit delete permission in catalog, restricted to Admin generally.
        return $user->hasRole(\App\Support\RoleCatalog::ADMIN);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasRole(\App\Support\RoleCatalog::ADMIN);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasRole(\App\Support\RoleCatalog::ADMIN);
    }

    /**
     * Determine whether the user can assign the work order.
     */
    public function assign(User $user, WorkOrder $workOrder): bool
    {
        return $user->can(PermissionCatalog::WORK_ORDERS_ASSIGN);
    }

    /**
     * Determine whether the user can mark the work order as arrived.
     */
    public function markArrived(User $user, WorkOrder $workOrder): bool
    {
        if (!$user->can(PermissionCatalog::WORK_ORDERS_ARRIVE)) {
            return false;
        }

        // Must be assigned to the user or user is a manager
        return $workOrder->assigned_to_user_id === $user->id || $user->hasRole(\App\Support\RoleCatalog::OPERATIONS_MANAGER);
    }

    /**
     * Determine whether the user can add notes to the work order.
     */
    public function addNote(User $user, WorkOrder $workOrder): bool
    {
        if ($user->can(PermissionCatalog::WORK_ORDERS_NOTE)) {
            return true;
        }
        
        // Implicitly allow if they can update it
        if ($this->update($user, $workOrder)) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can generate a report for the work order.
     */
    public function generateReport(User $user, WorkOrder $workOrder): bool
    {
        return $user->can(PermissionCatalog::WORK_ORDERS_REPORT);
    }

    /**
     * Determine whether the user can sign off the work order.
     */
    public function signOff(User $user, WorkOrder $workOrder): bool
    {
        if (!$user->can(PermissionCatalog::WORK_ORDERS_SIGNOFF)) {
            return false;
        }

        // Typically the customer (requester) signs off
        return $workOrder->requested_by_user_id === $user->id;
    }

    /**
     * Determine whether the user can provide feedback on the work order.
     */
    public function provideFeedback(User $user, WorkOrder $workOrder): bool
    {
        if (!$user->can(PermissionCatalog::WORK_ORDERS_FEEDBACK)) {
            return false;
        }
        
        return $workOrder->requested_by_user_id === $user->id;
    }
}
