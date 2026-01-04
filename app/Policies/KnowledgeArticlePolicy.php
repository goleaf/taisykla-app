<?php

namespace App\Policies;

use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Support\PermissionCatalog;

class KnowledgeArticlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionCatalog::KNOWLEDGE_BASE_VIEW) ||
               $user->can(PermissionCatalog::KNOWLEDGE_BASE_MANAGE);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        if ($user->can(PermissionCatalog::KNOWLEDGE_BASE_MANAGE)) {
            return true;
        }

        if (!$user->can(PermissionCatalog::KNOWLEDGE_BASE_VIEW)) {
            return false;
        }

        // Must be published for non-managers
        if ($knowledgeArticle->status !== 'published') {
            return false;
        }

        // Visibility checks
        if ($knowledgeArticle->visibility === 'public') {
            return true;
        }

        if ($knowledgeArticle->visibility === 'customer' && $user->isCustomer()) {
            return true;
        }

        if ($knowledgeArticle->visibility === 'internal' && ($user->isOperations() || $user->hasRole(\App\Support\RoleCatalog::TECHNICIAN))) {
            return true;
        }

        if ($knowledgeArticle->visibility === 'role') {
             $allowedRoles = $knowledgeArticle->visibility_roles ?? [];
             return $user->hasAnyRole($allowedRoles);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionCatalog::KNOWLEDGE_BASE_MANAGE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user->can(PermissionCatalog::KNOWLEDGE_BASE_MANAGE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user->can(PermissionCatalog::KNOWLEDGE_BASE_MANAGE);
    }
}
