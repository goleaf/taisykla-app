<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceEvent extends Model
{
    use HasFactory;

    public const TYPE_REPAIR = 'repair';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_INSPECTION = 'inspection';
    public const TYPE_UPGRADE = 'upgrade';
    public const TYPE_INSTALLATION = 'installation';

    protected $fillable = [
        'equipment_id',
        'work_order_id',
        'technician_id',
        'event_type',
        'problem_description',
        'resolution_description',
        'started_at',
        'completed_at',
        'duration_minutes',
        'labor_cost',
        'parts_cost',
        'parts_replaced',
        'before_photos',
        'after_photos',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'parts_replaced' => 'array',
        'before_photos' => 'array',
        'after_photos' => 'array',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->labor_cost + (float) $this->parts_cost;
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_REPAIR => 'Repair',
            self::TYPE_MAINTENANCE => 'Maintenance',
            self::TYPE_INSPECTION => 'Inspection',
            self::TYPE_UPGRADE => 'Upgrade',
            self::TYPE_INSTALLATION => 'Installation',
        ];
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeForEquipment($query, int $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }
}
