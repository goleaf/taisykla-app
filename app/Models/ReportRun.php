<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'status',
        'format',
        'file_path',
        'row_count',
        'meta',
        'run_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'run_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
