<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'assigned_to_user_id',
        'recurring_schedule_id',
        'scheduled_start_at',
        'scheduled_end_at',
        'time_window',
        'status',
        'notes',
        'is_exception',
    ];

    protected $casts = [
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'is_exception' => 'boolean',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function recurringSchedule()
    {
        return $this->belongsTo(RecurringSchedule::class);
    }
}
