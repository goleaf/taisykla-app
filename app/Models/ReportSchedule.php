<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'frequency',
        'day_of_week',
        'day_of_month',
        'time_of_day',
        'format',
        'timezone',
        'recipients',
        'delivery_channels',
        'parameters',
        'conditions',
        'filters',
        'last_run_at',
        'next_run_at',
        'is_active',
    ];

    protected $casts = [
        'recipients' => 'array',
        'delivery_channels' => 'array',
        'parameters' => 'array',
        'conditions' => 'array',
        'filters' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
