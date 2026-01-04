<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_OPERATIONAL = 'operational';
    public const STATUS_NEEDS_ATTENTION = 'needs_attention';
    public const STATUS_IN_REPAIR = 'in_repair';
    public const STATUS_RETIRED = 'retired';
    public const STATUS_DECOMMISSIONED = 'decommissioned';

    public const LIFECYCLE_NEW = 'new';
    public const LIFECYCLE_OPERATIONAL = 'operational';
    public const LIFECYCLE_WARRANTY_EXPIRING = 'warranty_expiring';
    public const LIFECYCLE_NEEDS_REPLACEMENT = 'needs_replacement';
    public const LIFECYCLE_DECOMMISSIONED = 'decommissioned';

    protected $fillable = [
        'organization_id',
        'parent_equipment_id',
        'equipment_category_id',
        'manufacturer_id',
        'name',
        'type',
        'manufacturer',
        'model',
        'serial_number',
        'asset_tag',
        'qr_code',
        'barcode',
        'purchase_date',
        'purchase_price',
        'purchase_vendor',
        'status',
        'expected_lifespan_months',
        'health_score',
        'lifecycle_status',
        'location_name',
        'location_address',
        'location_building',
        'location_floor',
        'location_room',
        'ip_address',
        'mac_address',
        'dimensions',
        'weight',
        'assigned_user_id',
        'notes',
        'specifications',
        'custom_fields',
        'last_maintenance_at',
        'next_maintenance_due_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'last_service_at' => 'datetime',
        'last_maintenance_at' => 'datetime',
        'next_maintenance_due_at' => 'datetime',
        'specifications' => 'array',
        'custom_fields' => 'array',
        'weight' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'parent_equipment_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Equipment::class, 'parent_equipment_id');
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EquipmentDocument::class);
    }

    public function serviceEvents(): HasMany
    {
        return $this->hasMany(ServiceEvent::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(EquipmentMetric::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceSchedule::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }

    public function parentRelationships(): HasMany
    {
        return $this->hasMany(EquipmentRelationship::class, 'parent_equipment_id');
    }

    public function childRelationships(): HasMany
    {
        return $this->hasMany(EquipmentRelationship::class, 'child_equipment_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeOperational($query)
    {
        return $query->where('status', self::STATUS_OPERATIONAL);
    }

    public function scopeNeedsAttention($query)
    {
        return $query->where('status', self::STATUS_NEEDS_ATTENTION);
    }

    public function scopeByLocation($query, ?string $building = null, ?string $floor = null, ?string $room = null)
    {
        return $query
            ->when($building, fn($q) => $q->where('location_building', $building))
            ->when($floor, fn($q) => $q->where('location_floor', $floor))
            ->when($room, fn($q) => $q->where('location_room', $room));
    }

    public function scopeWithHealthBelow($query, int $score)
    {
        return $query->where('health_score', '<', $score);
    }

    public function scopeWithActiveWarranty($query)
    {
        return $query->whereHas('warranties', function ($q) {
            $q->where('ends_at', '>=', now());
        });
    }

    public function scopeWithExpiredWarranty($query)
    {
        return $query->whereDoesntHave('warranties', function ($q) {
            $q->where('ends_at', '>=', now());
        });
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getAgeInMonthsAttribute(): ?int
    {
        if (!$this->purchase_date) {
            return null;
        }

        return $this->purchase_date->diffInMonths(Carbon::today());
    }

    public function getAgeInYearsAttribute(): ?float
    {
        if (!$this->purchase_date) {
            return null;
        }

        return round($this->purchase_date->diffInYears(Carbon::today(), true), 1);
    }

    public function getHasActiveWarrantyAttribute(): bool
    {
        return $this->warranties()
            ->where('ends_at', '>=', now())
            ->exists();
    }

    public function getActiveWarrantyAttribute(): ?Warranty
    {
        return $this->warranties()
            ->where('ends_at', '>=', now())
            ->orderBy('ends_at', 'desc')
            ->first();
    }

    public function getLocationFullAttribute(): string
    {
        $parts = array_filter([
            $this->location_building,
            $this->location_floor,
            $this->location_room,
        ]);

        return implode(' → ', $parts) ?: $this->location_name ?? 'Unknown';
    }

    public function getTotalServiceCostAttribute(): float
    {
        return $this->serviceEvents()->sum('labor_cost') + $this->serviceEvents()->sum('parts_cost');
    }

    // ─── Static Helpers ───────────────────────────────────────────────

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPERATIONAL => 'Operational',
            self::STATUS_NEEDS_ATTENTION => 'Needs Attention',
            self::STATUS_IN_REPAIR => 'In Repair',
            self::STATUS_RETIRED => 'Retired',
            self::STATUS_DECOMMISSIONED => 'Decommissioned',
        ];
    }

    public static function lifecycleOptions(): array
    {
        return [
            self::LIFECYCLE_NEW => 'New',
            self::LIFECYCLE_OPERATIONAL => 'Operational',
            self::LIFECYCLE_WARRANTY_EXPIRING => 'Warranty Expiring',
            self::LIFECYCLE_NEEDS_REPLACEMENT => 'Needs Replacement',
            self::LIFECYCLE_DECOMMISSIONED => 'Decommissioned',
        ];
    }
}

