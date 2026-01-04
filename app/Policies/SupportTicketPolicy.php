<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;
use App\Support\PermissionCatalog;

class SupportTicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionCatalog::SUPPORT_VIEW) ||
               $user->can(PermissionCatalog::SUPPORT_VIEW_ALL) ||
               $user->can(PermissionCatalog::SUPPORT_VIEW_ORG) ||
               $user->can(PermissionCatalog::SUPPORT_VIEW_OWN);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SupportTicket $supportTicket): bool
    {
        if ($user->can(PermissionCatalog::SUPPORT_VIEW_ALL)) {
            return true;
        }

        if ($user->can(PermissionCatalog::SUPPORT_VIEW_ORG) && $supportTicket->organization_id === $user->organization_id) {
            return true;
        }

        if ($user->can(PermissionCatalog::SUPPORT_VIEW_OWN)) {
             if ($supportTicket->submitted_by_user_id === $user->id) {
                 return true;
             }
             // Implicitly allow if assigned to user
             if ($supportTicket->assigned_to_user_id === $user->id) {
                 return true;
             }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionCatalog::SUPPORT_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SupportTicket $supportTicket): bool
    {
        if ($user->can(PermissionCatalog::SUPPORT_MANAGE)) {
            return true;
        }

        // Users can often update their own tickets (e.g. add comments, close)
        // But maybe restricted. The catalog has SUPPORT_MANAGE.
        // If simple user, maybe they can only "view" but interact via specific methods not covered by generic "update".
        // However, usually updating the ticket (changing status, etc) is for support staff.
        // Adding comments is usually a separate thing or part of update.
        // I'll restrict generic update to MANAGE or ASSIGN (if they can assign, they can probably update).
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupportTicket $supportTicket): bool
    {
        return $user->can(PermissionCatalog::SUPPORT_MANAGE);
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function assign(User $user, SupportTicket $supportTicket): bool
    {
        return $user->can(PermissionCatalog::SUPPORT_ASSIGN);
    }
}
