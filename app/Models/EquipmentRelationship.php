<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRelationship extends Model
{
    use HasFactory;

    public const TYPE_CONTAINS = 'contains';
    public const TYPE_DEPENDS_ON = 'depends_on';
    public const TYPE_POWERS = 'powers';
    public const TYPE_CONNECTS_TO = 'connects_to';

    protected $fillable = [
        'parent_equipment_id',
        'child_equipment_id',
        'relationship_type',
        'notes',
    ];

    public function parentEquipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'parent_equipment_id');
    }

    public function childEquipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'child_equipment_id');
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_CONTAINS => 'Contains',
            self::TYPE_DEPENDS_ON => 'Depends On',
            self::TYPE_POWERS => 'Powers',
            self::TYPE_CONNECTS_TO => 'Connects To',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::typeOptions()[$this->relationship_type] ?? $this->relationship_type;
    }
}
