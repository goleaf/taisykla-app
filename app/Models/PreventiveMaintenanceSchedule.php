<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PreventiveMaintenanceSchedule extends Model
{
    use HasFactory;

    public const FREQ_DAYS = 'days';
    public const FREQ_WEEKS = 'weeks';
    public const FREQ_MONTHS = 'months';
    public const FREQ_HOURS_OF_USE = 'hours_of_use';

    protected $fillable = [
        'equipment_id',
        'assigned_user_id',
        'equipment_category_id',
        'name',
        'description',
        'frequency_type',
        'frequency_value',
        'next_due_at',
        'last_performed_at',
        'checklist_template',
        'reminder_days_before',
        'auto_create_work_order',
        'is_active',
    ];

    protected $casts = [
        'next_due_at' => 'datetime',
        'last_performed_at' => 'datetime',
        'checklist_template' => 'array',
        'auto_create_work_order' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public static function frequencyOptions(): array
    {
        return [
            self::FREQ_DAYS => 'Days',
            self::FREQ_WEEKS => 'Weeks',
            self::FREQ_MONTHS => 'Months',
            self::FREQ_HOURS_OF_USE => 'Hours of Use',
        ];
    }

    public function calculateNextDueDate(): Carbon
    {
        $base = $this->last_performed_at ?? now();

        return match ($this->frequency_type) {
            self::FREQ_DAYS => $base->addDays($this->frequency_value),
            self::FREQ_WEEKS => $base->addWeeks($this->frequency_value),
            self::FREQ_MONTHS => $base->addMonths($this->frequency_value),
            default => $base->addDays($this->frequency_value),
        };
    }

    public function markCompleted(): void
    {
        $this->last_performed_at = now();
        $this->next_due_at = $this->calculateNextDueDate();
        $this->save();
    }

    public function isOverdue(): bool
    {
        return $this->next_due_at && $this->next_due_at->isPast();
    }

    public function isDueSoon(): bool
    {
        if (!$this->next_due_at) {
            return false;
        }

        return $this->next_due_at->isBetween(now(), now()->addDays($this->reminder_days_before));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('next_due_at', '<=', now());
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereBetween('next_due_at', [now(), now()->addDays($days)]);
    }
}
