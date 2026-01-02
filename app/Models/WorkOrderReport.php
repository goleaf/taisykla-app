<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'created_by_user_id',
        'diagnosis_summary',
        'work_performed',
        'test_results',
        'recommendations',
        'diagnostic_minutes',
        'repair_minutes',
        'testing_minutes',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
