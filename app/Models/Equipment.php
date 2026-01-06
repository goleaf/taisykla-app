<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class Equipment
 *
 * @property int $id
 * @property int|null $customer_id
 * @property int|null $equipment_type_id
 * @property string|null $manufacturer
 * @property string|null $model
 * @property string|null $serial_number
 * @property \Illuminate\Support\Carbon|null $purchase_date
 * @property \Illuminate\Support\Carbon|null $warranty_expiry
 * @property string $status
 * @property string|null $location
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $last_maintenance_at
 * @property \Illuminate\Support\Carbon|null $next_maintenance_due_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read Organization|null $customer
 * @property-read EquipmentType|null $equipmentType
 * @property-read string $warranty_status
 * @property-read int|null $days_since_last_service
 */
class Equipment extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Auditable, \Zap\Models\Concerns\HasSchedules;

    public const STATUS_OPERATIONAL = 'operational';
    public const STATUS_NEEDS_REPAIR = 'needs_repair';
    public const STATUS_OUT_OF_SERVICE = 'out_of_service';
    public const STATUS_RETIRED = 'retired';
    public const STATUS_IN_REPAIR = 'in_repair'; // Keeping for backward compatibility if needed, or remove if strict.

    protected $fillable = [
        'customer_id',
        'equipment_type_id',
        'parent_equipment_id', // Preserving
        'manufacturer',
        'model',
        'serial_number',
        'purchase_date',
        'warranty_expiry',
        'status',
        'location',
        'notes',
        'asset_tag', // Preserving
        'qr_code', // Preserving
        'barcode', // Preserving
        'purchase_price', // Preserving
        'purchase_vendor', // Preserving
        'expected_lifespan_months', // Preserving
        'health_score', // Preserving
        'lifecycle_status', // Preserving
        'ip_address', // Preserving
        'mac_address', // Preserving
        'dimensions', // Preserving
        'weight', // Preserving
        'assigned_user_id', // Preserving
        'specifications', // Preserving
        'custom_fields', // Preserving
        'last_maintenance_at', // Preserving
        'next_maintenance_due_at', // Preserving
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_price' => 'decimal:2',
        'last_service_at' => 'datetime',
        'last_maintenance_at' => 'datetime',
        'next_maintenance_due_at' => 'datetime',
        'specifications' => 'array',
        'custom_fields' => 'array',
        'weight' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'customer_id');
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function manufacturerRelationship(): BelongsTo // Renamed to avoid conflict with 'manufacturer' string attribute if it exists, but 'manufacturer' is in fillable as string? 
    // Wait, requirement says 'manufacturer' is FILLABLE. Existing model had 'manufacturer_id' AND 'manufacturer' string? 
    // Looking at old fillable: 'manufacturer_id' and 'manufacturer'.
    // Requirement says: "manufacturer". Usually implies string.
    // I will keep 'manufacturer' string. If there is a 'Manufacturer' model, I will keep 'manufacturer_id' as optional or remove if not requested.
    // I'll assume 'manufacturer' string is primary for this request.
    // But I'll keep 'manufacturer_id' if it was there and I didn't drop it.
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(PreventiveMaintenanceSchedule::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EquipmentDocument::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    // Preserving existing useful relationships
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

    public function serviceEvents(): HasMany
    {
        return $this->hasMany(ServiceEvent::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(EquipmentMetric::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        // "Active" usually means not retired/out of service.
        return $query->whereIn('status', [self::STATUS_OPERATIONAL, self::STATUS_NEEDS_REPAIR, self::STATUS_IN_REPAIR]);
    }

    public function scopeNeedsMaintenance($query)
    {
        // Checks last service date or next_maintenance_due_at
        return $query->where('next_maintenance_due_at', '<=', now())
            ->orWhere(function ($q) {
                $q->whereNull('last_maintenance_at')
                    ->where('created_at', '<=', now()->subMonths(6)); // Example rule
            });
        // Or simply based on prompt "checks last service date". 
        // Maybe: where('last_service_at', '<', now()->subYear())?
        // I will use next_maintenance_due_at if available, else fallback logic.
        // Let's implement a simple check: next maintenance passed OR last service was long ago.
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getDaysSinceLastServiceAttribute(): ?int
    {
        if (!$this->last_service_at) {
            return null;
        }
        return $this->last_service_at->diffInDays(now());
    }

    public function getWarrantyStatusAttribute(): string
    {
        if (!$this->warranty_expiry) {
            // Check 'warranties' relation if no flat field?
            // But I just added 'warranty_expiry' column.
            return 'expired'; // Or 'unknown'
        }

        if ($this->warranty_expiry->isPast()) {
            return 'expired';
        }

        if ($this->warranty_expiry->diffInDays(now()) <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    // ─── Methods ──────────────────────────────────────────────────────

    public function scheduleNextMaintenance(int $days = 90)
    {
        $this->update([
            'next_maintenance_due_at' => now()->addDays($days),
            'status' => self::STATUS_OPERATIONAL
        ]);

        // Could also create a MaintenanceSchedule record
        /*
        $this->maintenanceSchedules()->create([
            'due_at' => now()->addDays($days),
            'title' => 'Scheduled Maintenance',
            'status' => 'pending'
        ]);
        */

        return $this->next_maintenance_due_at;
    }

    public function checkWarrantyExpiry(): void
    {
        if ($this->warranty_status === 'expiring_soon') {
            // Trigger notification
            // Assuming we have a notification class or mechanism
            // For now, we'll just log it or dispatch an event.
            // dispatch(new \App\Events\EquipmentWarrantyExpiring($this));

            // Or using the Notification facade if User/Customer is notifiable
            /*
            $recipient = $this->assignedUser ?? $this->customer?->primaryContact;
            if ($recipient) {
                $recipient->notify(new \App\Notifications\EquipmentWarrantyExpiring($this));
            }
            */

            // Since explicit implementation of Notification class wasn't requested, 
            // I will leave a comment and maybe implemented a basic placeholder or Log.
            \Illuminate\Support\Facades\Log::info("Warranty expiring for Equipment ID {$this->id} ({$this->serial_number})");
        }
    }

    public static function validateRules(mixed $id = null): array
    {
        $uniqueSerial = 'unique:equipment,serial_number';
        if ($id) {
            $uniqueSerial .= ',' . $id;
        }

        return [
            'customer_id' => 'required|exists:organizations,id',
            'equipment_type_id' => 'required|exists:equipment_types,id',
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => ['required', 'string', 'max:255', $uniqueSerial],
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
            'status' => 'required|string|in:' . implode(',', array_keys(self::statusOptions())),
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPERATIONAL => 'Operational',
            self::STATUS_NEEDS_REPAIR => 'Needs Repair',
            self::STATUS_OUT_OF_SERVICE => 'Out of Service',
            self::STATUS_RETIRED => 'Retired',
        ];
    }
}

