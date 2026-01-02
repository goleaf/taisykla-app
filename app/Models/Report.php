<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'report_type',
        'data_source',
        'description',
        'definition',
        'filters',
        'group_by',
        'sort_by',
        'compare',
        'is_public',
        'created_by_user_id',
    ];

    protected $casts = [
        'definition' => 'array',
        'filters' => 'array',
        'group_by' => 'array',
        'sort_by' => 'array',
        'compare' => 'array',
        'is_public' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function schedules()
    {
        return $this->hasMany(ReportSchedule::class);
    }

    public function runs()
    {
        return $this->hasMany(ReportRun::class);
    }
}
