<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ServiceRequest
 *
 * @property int $id
 * @property int|null $customer_id
 * @property int|null $equipment_id
 * @property int|null $technician_id
 * @property string $priority
 * @property string $status
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property float|null $estimated_hours
 * @property float|null $actual_hours
 * @property float $estimated_cost
 * @property float $actual_cost
 * @property string $approval_status
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $rejection_reason
 * @property string|null $customer_notes
 * @property string|null $technician_notes
 * @property string|null $internal_notes
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Organization|null $customer
 * @property-read Equipment|null $equipment
 * @property-read User|null $technician
 * @property-read User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection|ServiceRequestItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|ServiceRequestNote[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|ServiceRequestAttachment[] $attachments
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivityLog[] $activityLogs
 */
class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status Constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Priority Constants
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /**
     * Approval Status Constants
     */
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'equipment_id',
        'technician_id',
        'priority',
        'status',
        'description',
        'scheduled_at',
        'started_at', // NEW
        'completed_at',
        'estimated_hours', // NEW
        'actual_hours', // NEW
        'estimated_cost',
        'actual_cost',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason', // NEW
        'customer_notes', // NEW
        'technician_notes', // NEW
        'internal_notes', // NEW
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    /**
     * Get the customer (organization) associated with the service request.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'customer_id');
    }

    /**
     * Get the equipment associated with the service request.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the technician (user) assigned to the service request.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the user who approved the service request.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the items associated with the service request.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ServiceRequestItem::class);
    }

    /**
     * Get the notes associated with the service request.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ServiceRequestNote::class);
    }

    /**
     * Get the attachments associated with the service request.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ServiceRequestAttachment::class);
    }

    /**
     * Get all of the activity logs for the service request.
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    /**
     * Scopes
     */

    /**
     * Scope a query to only include active service requests.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope a query to only include pending service requests.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include in-progress service requests.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope a query to only include completed service requests.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include high priority service requests.
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Accessors & Mutators
     */

    /**
     * Get the human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Set the priority and validate it.
     */
    public function setPriorityAttribute(string $value): void
    {
        $allowed = [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
        ];

        if (!in_array(strtolower($value), $allowed)) {
            $value = self::PRIORITY_MEDIUM;
        }

        $this->attributes['priority'] = strtolower($value);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::observe(\App\Observers\ServiceRequestObserver::class);
    }
}
