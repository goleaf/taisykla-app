<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'phone',
        'job_title',
        'department',
        'employee_id',
        'address',
        'timezone',
        'is_active',
        'must_change_password',
        'onboarded_at',
        'last_seen_at',
        'availability_status',
        'availability_updated_at',
        'current_latitude',
        'current_longitude',
        'mfa_enabled',
        'mfa_method',
        'mfa_phone',
        'mfa_email',
        'mfa_secret',
        'mfa_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_seen_at' => 'datetime',
            'onboarded_at' => 'datetime',
            'availability_updated_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'mfa_confirmed_at' => 'datetime',
            'mfa_secret' => 'encrypted',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignedWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'assigned_to_user_id');
    }

    public function requestedWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'requested_by_user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'assigned_to_user_id');
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function mfaChallenges()
    {
        return $this->hasMany(MfaChallenge::class);
    }

    public function securityKeys()
    {
        return $this->hasMany(SecurityKey::class);
    }

    public function isBusinessCustomer(): bool
    {
        return $this->hasAnyRole(RoleCatalog::businessCustomerRoles());
    }

    public function isConsumer(): bool
    {
        return $this->hasRole(RoleCatalog::CONSUMER);
    }

    public function isCustomer(): bool
    {
        return $this->isBusinessCustomer() || $this->isConsumer();
    }

    public function isOperations(): bool
    {
        return $this->hasAnyRole(RoleCatalog::operationsRoles());
    }

    public function isReadOnly(): bool
    {
        return $this->hasRole(RoleCatalog::GUEST);
    }

    public function canManageSchedule(): bool
    {
        return $this->can(PermissionCatalog::SCHEDULE_MANAGE);
    }

    public function canViewSchedule(): bool
    {
        return $this->can(PermissionCatalog::SCHEDULE_VIEW);
    }

    public function canAccessInventory(): bool
    {
        return $this->can(PermissionCatalog::INVENTORY_VIEW);
    }

    public function canManageReports(): bool
    {
        return $this->can(PermissionCatalog::REPORTS_MANAGE);
    }

    public function canViewReports(): bool
    {
        return $this->can(PermissionCatalog::REPORTS_VIEW);
    }

    public function canManageBilling(): bool
    {
        return $this->can(PermissionCatalog::BILLING_MANAGE);
    }

    public function canViewBilling(): bool
    {
        return $this->can(PermissionCatalog::BILLING_VIEW);
    }

    public function canManageSupportTickets(): bool
    {
        return $this->can(PermissionCatalog::SUPPORT_MANAGE);
    }

    public function canViewSupportTickets(): bool
    {
        return $this->can(PermissionCatalog::SUPPORT_VIEW);
    }

    public function canCreateSupportTickets(): bool
    {
        return $this->can(PermissionCatalog::SUPPORT_CREATE);
    }

    public function canAssignSupportTickets(): bool
    {
        return $this->can(PermissionCatalog::SUPPORT_ASSIGN);
    }

    public function canManageKnowledgeBase(): bool
    {
        return $this->can(PermissionCatalog::KNOWLEDGE_BASE_MANAGE);
    }

    public function canViewKnowledgeBase(): bool
    {
        return $this->can(PermissionCatalog::KNOWLEDGE_BASE_VIEW);
    }

    public function canUpdateWorkOrders(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_UPDATE);
    }

    public function canAssignWorkOrders(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_ASSIGN);
    }

    public function canCreateWorkOrders(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_CREATE);
    }

    public function canManageWorkOrderReports(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_REPORT);
    }

    public function canAddWorkOrderNotes(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_NOTE);
    }

    public function canMarkWorkOrdersArrived(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_ARRIVE);
    }

    public function canSignOffWorkOrders(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_SIGNOFF);
    }

    public function canSubmitWorkOrderFeedback(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_FEEDBACK);
    }

    public function canViewAllWorkOrders(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_VIEW_ALL);
    }

    public function canViewWorkOrders(): bool
    {
        return $this->can(PermissionCatalog::WORK_ORDERS_VIEW);
    }

    public function canViewEquipment(): bool
    {
        return $this->can(PermissionCatalog::EQUIPMENT_VIEW);
    }

    public function canManageEquipment(): bool
    {
        return $this->can(PermissionCatalog::EQUIPMENT_MANAGE);
    }

    public function canViewClients(): bool
    {
        return $this->can(PermissionCatalog::CLIENTS_VIEW);
    }

    public function canManageClients(): bool
    {
        return $this->can(PermissionCatalog::CLIENTS_MANAGE);
    }

    public function canViewMessages(): bool
    {
        return $this->can(PermissionCatalog::MESSAGES_VIEW);
    }

    public function canSendMessages(): bool
    {
        return $this->can(PermissionCatalog::MESSAGES_SEND);
    }

    public function canViewSettings(): bool
    {
        return $this->can(PermissionCatalog::SETTINGS_VIEW);
    }

    public function canManageSettings(): bool
    {
        return $this->can(PermissionCatalog::SETTINGS_MANAGE);
    }

    public function canManageUsers(): bool
    {
        return $this->can(PermissionCatalog::USERS_MANAGE);
    }
}
