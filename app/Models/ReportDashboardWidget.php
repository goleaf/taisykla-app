<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDashboardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'dashboard_id',
        'title',
        'widget_type',
        'report_id',
        'data_source',
        'config',
        'position',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'position' => 'array',
        'is_active' => 'boolean',
    ];

    public function dashboard()
    {
        return $this->belongsTo(ReportDashboard::class, 'dashboard_id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
