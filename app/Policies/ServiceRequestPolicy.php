<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;

class ServiceRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionCatalog::WORK_ORDERS_VIEW) || // Reusing Work Order perms as proxy if needed, or define specific ones
            $user->hasRole(RoleCatalog::ADMIN) ||
            $user->hasRole(RoleCatalog::OPERATIONS_MANAGER) ||
            $user->hasRole(RoleCatalog::TECHNICIAN);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->hasRole(RoleCatalog::ADMIN) || $user->hasRole(RoleCatalog::OPERATIONS_MANAGER)) {
            return true;
        }

        if ($user->hasRole(RoleCatalog::TECHNICIAN) && $serviceRequest->technician_id === $user->id) {
            return true;
        }

        // Check if user belongs to the customer organization
        if ($serviceRequest->customer_id === $user->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Generally users can create requests, logic can be refined
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->hasRole(RoleCatalog::ADMIN) || $user->hasRole(RoleCatalog::OPERATIONS_MANAGER)) {
            return true;
        }

        if ($user->hasRole(RoleCatalog::TECHNICIAN) && $serviceRequest->technician_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole(RoleCatalog::ADMIN);
    }

    /**
     * Determine whether the user can assign a technician.
     */
    public function assign(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole(RoleCatalog::ADMIN) || $user->hasRole(RoleCatalog::OPERATIONS_MANAGER);
    }

    /**
     * Determine whether the user can approve the request.
     */
    public function approve(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole(RoleCatalog::ADMIN) || $user->hasRole(RoleCatalog::OPERATIONS_MANAGER);
    }

    /**
     * Determine whether the user can reject the request.
     */
    public function reject(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasRole(RoleCatalog::ADMIN) || $user->hasRole(RoleCatalog::OPERATIONS_MANAGER);
    }
}
