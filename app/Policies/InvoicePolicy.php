<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Support\PermissionCatalog;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionCatalog::BILLING_VIEW) ||
               $user->can(PermissionCatalog::BILLING_VIEW_ALL) ||
               $user->can(PermissionCatalog::BILLING_VIEW_ORG) ||
               $user->can(PermissionCatalog::BILLING_VIEW_OWN);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->can(PermissionCatalog::BILLING_VIEW_ALL)) {
            return true;
        }

        if ($user->can(PermissionCatalog::BILLING_VIEW_ORG) && $invoice->organization_id === $user->organization_id) {
            return true;
        }

        // Load work order if needed for ownership check
        $invoice->loadMissing('workOrder');
        if ($user->can(PermissionCatalog::BILLING_VIEW_OWN) && $invoice->workOrder && $invoice->workOrder->requested_by_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionCatalog::BILLING_MANAGE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can(PermissionCatalog::BILLING_MANAGE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can(PermissionCatalog::BILLING_MANAGE);
    }
}
