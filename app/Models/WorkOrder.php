<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'equipment_id',
        'requested_by_user_id',
        'assigned_to_user_id',
        'preferred_technician_id',
        'preferred_technician_ids',
        'assigned_at',
        'category_id',
        'required_skill_level',
        'required_skills',
        'required_certifications',
        'priority',
        'status',
        'subject',
        'description',
        'custom_fields',
        'location_name',
        'location_address',
        'location_latitude',
        'location_longitude',
        'requested_at',
        'scheduled_start_at',
        'scheduled_end_at',
        'arrived_at',
        'time_window',
        'service_territory',
        'customer_time_window_start',
        'customer_time_window_end',
        'customer_preference_notes',
        'estimated_minutes',
        'travel_minutes',
        'labor_minutes',
        'total_cost',
        'is_warranty',
        'on_hold_reason',
        'started_at',
        'completed_at',
        'canceled_at',
        'customer_signature_name',
        'customer_signature_at',
        'customer_signoff_functional',
        'customer_signoff_professional',
        'customer_signoff_satisfied',
        'customer_signoff_comments',
        'target_response_at',
        'target_resolution_at',
        'sla_breached_at',
        'sla_status',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'arrived_at' => 'datetime',
        'assigned_at' => 'datetime',
        'preferred_technician_ids' => 'array',
        'required_skills' => 'array',
        'required_certifications' => 'array',
        'customer_time_window_start' => 'datetime',
        'customer_time_window_end' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
        'customer_signature_at' => 'datetime',
        'is_warranty' => 'boolean',
        'customer_signoff_functional' => 'boolean',
        'customer_signoff_professional' => 'boolean',
        'customer_signoff_satisfied' => 'boolean',
        'custom_fields' => 'array',
        'target_response_at' => 'datetime',
        'target_resolution_at' => 'datetime',
        'sla_breached_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function preferredTechnician()
    {
        return $this->belongsTo(User::class, 'preferred_technician_id');
    }

    public function category()
    {
        return $this->belongsTo(WorkOrderCategory::class, 'category_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function events()
    {
        return $this->hasMany(WorkOrderEvent::class);
    }

    public function serviceEvents()
    {
        return $this->hasMany(ServiceEvent::class);
    }

    public function parts()
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function feedback()
    {
        return $this->hasOne(WorkOrderFeedback::class);
    }

    public function report()
    {
        return $this->hasOne(WorkOrderReport::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function warrantyClaims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    public function messageThreads()
    {
        return $this->hasMany(MessageThread::class);
    }

    protected static function booted()
    {
        static::created(function (WorkOrder $workOrder) {
            app(\App\Services\SLAService::class)->calculateTargets($workOrder);
        });
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}
