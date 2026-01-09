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
        return $user->can(PermissionCatalog::SERVICE_REQUESTS_VIEW)
            || $user->can(PermissionCatalog::SERVICE_REQUESTS_VIEW_ALL)
            || $user->can(PermissionCatalog::SERVICE_REQUESTS_VIEW_OWN)
            || $user->can(PermissionCatalog::SERVICE_REQUESTS_VIEW_ORG);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        // Full access to all service requests
        if ($user->can(PermissionCatalog::SERVICE_REQUESTS_VIEW_ALL)) {
            return true;
        }

        // Technician can view assigned requests
        if (
            $user->can(PermissionCatalog::SERVICE_REQUESTS_VIEW_OWN)
            && $serviceRequest->technician_id === $user->id
        ) {
            return true;
        }

        // Customer can view their organization's requests
        if (
            $user->can(PermissionCatalog::SERVICE_REQUESTS_VIEW_ORG)
            && $serviceRequest->customer_id === $user->organization_id
        ) {
            return true;
        }

        // Consumer can view their own requests
        if (
            $user->can(PermissionCatalog::SERVICE_REQUESTS_VIEW_OWN)
            && $serviceRequest->customer_id === $user->id
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionCatalog::SERVICE_REQUESTS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        // Full update access
        if ($user->can(PermissionCatalog::SERVICE_REQUESTS_UPDATE)) {
            return true;
        }

        // Technician can update their own assigned requests (if not completed)
        if ($user->can(PermissionCatalog::SERVICE_REQUESTS_UPDATE_OWN)) {
            return $serviceRequest->technician_id === $user->id
                && $serviceRequest->status !== ServiceRequest::STATUS_COMPLETED;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->can(PermissionCatalog::SERVICE_REQUESTS_DELETE);
    }

    /**
     * Determine whether the user can assign a technician.
     */
    public function assign(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->can(PermissionCatalog::SERVICE_REQUESTS_ASSIGN);
    }

    /**
     * Determine whether the user can approve the request.
     */
    public function approve(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->can(PermissionCatalog::SERVICE_REQUESTS_APPROVE);
    }

    /**
     * Determine whether the user can reject the request.
     */
    public function reject(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->can(PermissionCatalog::SERVICE_REQUESTS_APPROVE);
    }
}
