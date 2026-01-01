<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'equipment_id',
        'warranty_id',
        'status',
        'submitted_at',
        'resolved_at',
        'details',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }
}
