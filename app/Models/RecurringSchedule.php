<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'assigned_to_user_id',
        'starts_at',
        'duration_minutes',
        'time_window',
        'frequency',
        'interval',
        'days_of_week',
        'day_of_month',
        'occurrence_count',
        'ends_at',
        'next_run_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'next_run_at' => 'datetime',
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
