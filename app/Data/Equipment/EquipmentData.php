<?php

namespace App\Data\Equipment;

use App\Data\Customer\CustomerData;
use App\Models\Equipment;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;

/**
 * Main Equipment Data object for transforming and validating equipment data.
 */
class EquipmentData extends Data
{
    public function __construct(
        public int|Optional $id,

        #[Required]
        public int $customer_id,

        public ?int $equipment_type_id,
        public ?int $parent_equipment_id,
        public ?int $assigned_user_id,

        #[Max(255)]
        public ?string $manufacturer,

        #[Max(255)]
        public ?string $model,

        #[Max(255)]
        public ?string $serial_number,

        #[Max(100)]
        public ?string $asset_tag,

        public ?string $qr_code,
        public ?string $barcode,

        #[In([Equipment::STATUS_OPERATIONAL, Equipment::STATUS_NEEDS_REPAIR, Equipment::STATUS_OUT_OF_SERVICE, Equipment::STATUS_RETIRED])]
        public string $status = Equipment::STATUS_OPERATIONAL,

        #[Max(255)]
        public ?string $location,

        public ?string $notes,
        public ?string $ip_address,
        public ?string $mac_address,
        public ?string $dimensions,

        #[Numeric]
        public ?float $weight,

        #[Numeric]
        public ?float $purchase_price,

        public ?string $purchase_vendor,

        public ?int $expected_lifespan_months,
        public ?int $health_score,
        public ?string $lifecycle_status,

        public ?array $specifications,
        public ?array $custom_fields,

        public ?CarbonImmutable $purchase_date,
        public ?CarbonImmutable $warranty_expiry,
        public ?CarbonImmutable $last_maintenance_at,
        public ?CarbonImmutable $next_maintenance_due_at,
        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,
        public ?CarbonImmutable $deleted_at,

        // Lazy loaded relationships
        public Lazy|CustomerData|Optional $customer,
    ) {
    }

    /**
     * Create from Eloquent model with lazy loading support
     */
    public static function fromModel(Equipment $equipment): self
    {
        return new self(
            id: $equipment->id,
            customer_id: $equipment->customer_id,
            equipment_type_id: $equipment->equipment_type_id,
            parent_equipment_id: $equipment->parent_equipment_id,
            assigned_user_id: $equipment->assigned_user_id,
            manufacturer: $equipment->manufacturer,
            model: $equipment->model,
            serial_number: $equipment->serial_number,
            asset_tag: $equipment->asset_tag,
            qr_code: $equipment->qr_code,
            barcode: $equipment->barcode,
            status: $equipment->status,
            location: $equipment->location,
            notes: $equipment->notes,
            ip_address: $equipment->ip_address,
            mac_address: $equipment->mac_address,
            dimensions: $equipment->dimensions,
            weight: $equipment->weight,
            purchase_price: $equipment->purchase_price,
            purchase_vendor: $equipment->purchase_vendor,
            expected_lifespan_months: $equipment->expected_lifespan_months,
            health_score: $equipment->health_score,
            lifecycle_status: $equipment->lifecycle_status,
            specifications: $equipment->specifications,
            custom_fields: $equipment->custom_fields,
            purchase_date: $equipment->purchase_date ? CarbonImmutable::parse($equipment->purchase_date) : null,
            warranty_expiry: $equipment->warranty_expiry ? CarbonImmutable::parse($equipment->warranty_expiry) : null,
            last_maintenance_at: $equipment->last_maintenance_at ? CarbonImmutable::parse($equipment->last_maintenance_at) : null,
            next_maintenance_due_at: $equipment->next_maintenance_due_at ? CarbonImmutable::parse($equipment->next_maintenance_due_at) : null,
            created_at: $equipment->created_at ? CarbonImmutable::parse($equipment->created_at) : null,
            updated_at: $equipment->updated_at ? CarbonImmutable::parse($equipment->updated_at) : null,
            deleted_at: $equipment->deleted_at ? CarbonImmutable::parse($equipment->deleted_at) : null,
            customer: Lazy::whenLoaded(
                'customer',
                $equipment,
                fn() =>
                CustomerData::from($equipment->customer)
            ),
        );
    }
}
