<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentMetric extends Model
{
    use HasFactory;

    public const TYPE_TCO = 'tco';
    public const TYPE_MTBF = 'mtbf';
    public const TYPE_MTTR = 'mttr';
    public const TYPE_DOWNTIME_HOURS = 'downtime_hours';
    public const TYPE_REPAIR_COST = 'repair_cost';
    public const TYPE_MAINTENANCE_COST = 'maintenance_cost';

    protected $fillable = [
        'equipment_id',
        'metric_type',
        'value',
        'period_start',
        'period_end',
        'breakdown',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'breakdown' => 'array',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_TCO => 'Total Cost of Ownership',
            self::TYPE_MTBF => 'Mean Time Between Failures',
            self::TYPE_MTTR => 'Mean Time To Repair',
            self::TYPE_DOWNTIME_HOURS => 'Downtime Hours',
            self::TYPE_REPAIR_COST => 'Repair Cost',
            self::TYPE_MAINTENANCE_COST => 'Maintenance Cost',
        ];
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->where('period_start', '>=', $start)
            ->where('period_end', '<=', $end);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }
}
