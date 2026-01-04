<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

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
        'skill_level',
        'skills',
        'certifications',
        'territory',
        'max_daily_minutes',
        'max_weekly_minutes',
        'overtime_allowed',
        'department',
        'employee_id',
        'address',
        'timezone',
        'preferred_language',
        'notification_preferences',
        'is_active',
        'must_change_password',
        'onboarded_at',
        'last_seen_at',
        'failed_login_attempts',
        'locked_until',
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
            'failed_login_attempts' => 'integer',
            'locked_until' => 'datetime',
            'mfa_enabled' => 'boolean',
            'mfa_confirmed_at' => 'datetime',
            'mfa_secret' => 'encrypted',
            'notification_preferences' => 'array',
            'skill_level' => 'integer',
            'skills' => 'array',
            'certifications' => 'array',
            'max_daily_minutes' => 'integer',
            'max_weekly_minutes' => 'integer',
            'overtime_allowed' => 'boolean',
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

    public function messageThreads()
    {
        return $this->belongsToMany(MessageThread::class, 'message_thread_participants', 'user_id', 'thread_id');
    }

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function messageFolders()
    {
        return $this->hasMany(MessageFolder::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function serviceEvents()
    {
        return $this->hasMany(ServiceEvent::class, 'technician_id');
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function recurringSchedules()
    {
        return $this->hasMany(RecurringSchedule::class, 'assigned_to_user_id');
    }

    public function calendarBlocks()
    {
        return $this->hasMany(CalendarBlock::class);
    }

    public function createdCreditMemos()
    {
        return $this->hasMany(CreditMemo::class, 'created_by_user_id');
    }

    public function createdPurchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by_user_id');
    }

    public function approvedPurchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'approved_by_user_id');
    }

    public function submittedSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'submitted_by_user_id');
    }

    public function assignedSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to_user_id');
    }

    public function createdKnowledgeArticles()
    {
        return $this->hasMany(KnowledgeArticle::class, 'created_by_user_id');
    }

    public function updatedKnowledgeArticles()
    {
        return $this->hasMany(KnowledgeArticle::class, 'updated_by_user_id');
    }

    public function reviewedKnowledgeArticles()
    {
        return $this->hasMany(KnowledgeArticle::class, 'reviewed_by_user_id');
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
