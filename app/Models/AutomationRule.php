<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger',
        'conditions',
        'actions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
    ];
    public const TRIGGER_SLA_WARNING = 'sla_warning';
    public const TRIGGER_SLA_BREACHED = 'sla_breached';
    public const TRIGGER_WORK_ORDER_CREATED = 'work_order_created';

    public static function getTriggers(): array
    {
        return [
            self::TRIGGER_WORK_ORDER_CREATED => 'Work Order Created',
            self::TRIGGER_SLA_WARNING => 'SLA Warning',
            self::TRIGGER_SLA_BREACHED => 'SLA Breached',
        ];
    }
}
