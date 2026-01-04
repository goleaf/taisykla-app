<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Warranty extends Model
{
    use HasFactory;

    public const COVERAGE_PARTS_ONLY = 'parts_only';
    public const COVERAGE_LABOR_ONLY = 'labor_only';
    public const COVERAGE_FULL = 'full';
    public const COVERAGE_LIMITED = 'limited';
    public const COVERAGE_EXTENDED = 'extended';

    protected $fillable = [
        'equipment_id',
        'provider_name',
        'warranty_number',
        'coverage_type',
        'coverage_details',
        'starts_at',
        'ends_at',
        'claim_instructions',
        'document_path',
        'terms_conditions',
        'renewal_cost',
        'is_renewable',
        'contact_phone',
        'contact_email',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'renewal_cost' => 'decimal:2',
        'is_renewable' => 'boolean',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function claims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getIsActiveAttribute(): bool
    {
        if (!$this->ends_at) {
            return false;
        }

        return $this->ends_at->isFuture() || $this->ends_at->isToday();
    }

    public function getIsExpiredAttribute(): bool
    {
        return !$this->is_active;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->ends_at) {
            return null;
        }

        $days = Carbon::today()->diffInDays($this->ends_at, false);

        return max(0, $days);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->days_remaining !== null && $this->days_remaining <= 30 && $this->days_remaining > 0;
    }

    public function getDurationInMonthsAttribute(): ?int
    {
        if (!$this->starts_at || !$this->ends_at) {
            return null;
        }

        return $this->starts_at->diffInMonths($this->ends_at);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('ends_at', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }

    // ─── Static Helpers ───────────────────────────────────────────────

    public static function coverageOptions(): array
    {
        return [
            self::COVERAGE_PARTS_ONLY => 'Parts Only',
            self::COVERAGE_LABOR_ONLY => 'Labor Only',
            self::COVERAGE_FULL => 'Full Coverage',
            self::COVERAGE_LIMITED => 'Limited',
            self::COVERAGE_EXTENDED => 'Extended',
        ];
    }
}
