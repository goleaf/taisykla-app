<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'requested_by_user_id',
        'status',
        'format',
        'filters',
        'parameters',
        'file_path',
        'row_count',
        'error_message',
        'queued_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'parameters' => 'array',
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
