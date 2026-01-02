<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'equipment_id',
        'requested_by_user_id',
        'assigned_to_user_id',
        'assigned_at',
        'category_id',
        'priority',
        'status',
        'subject',
        'description',
        'location_name',
        'location_address',
        'location_latitude',
        'location_longitude',
        'requested_at',
        'scheduled_start_at',
        'scheduled_end_at',
        'arrived_at',
        'time_window',
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
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'arrived_at' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
        'customer_signature_at' => 'datetime',
        'is_warranty' => 'boolean',
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

    public function parts()
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function feedback()
    {
        return $this->hasOne(WorkOrderFeedback::class);
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

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
