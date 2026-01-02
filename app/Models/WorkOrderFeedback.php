<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'user_id',
        'rating',
        'professionalism_rating',
        'knowledge_rating',
        'communication_rating',
        'timeliness_rating',
        'quality_rating',
        'would_recommend',
        'comments',
    ];

    protected $casts = [
        'would_recommend' => 'boolean',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
