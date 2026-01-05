<?php

namespace App\Data\Equipment;

use App\Models\Equipment;
use Closure;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Data object for updating existing Equipment.
 */
class UpdateEquipmentData extends Data
{
    public function __construct(
        #[Exists('organizations', 'id')]
        public int|Optional $customer_id,

        #[Exists('equipment_types', 'id')]
        public int|null|Optional $equipment_type_id,

        #[Exists('equipment', 'id')]
        public int|null|Optional $parent_equipment_id,

        #[Exists('users', 'id')]
        public int|null|Optional $assigned_user_id,

        #[Max(255)]
        public string|Optional $manufacturer,

        #[Max(255)]
        public string|Optional $model,

        #[Max(255)]
        public string|Optional $serial_number,

        #[Max(100)]
        public string|null|Optional $asset_tag,

        public string|null|Optional $qr_code,
        public string|null|Optional $barcode,

        #[In([Equipment::STATUS_OPERATIONAL, Equipment::STATUS_NEEDS_REPAIR, Equipment::STATUS_OUT_OF_SERVICE, Equipment::STATUS_RETIRED])]
        public string|Optional $status,

        #[Max(255)]
        public string|null|Optional $location,

        public string|null|Optional $notes,
        public string|null|Optional $ip_address,
        public string|null|Optional $mac_address,
        public string|null|Optional $dimensions,

        #[Numeric, Min(0)]
        public float|null|Optional $weight,

        #[Numeric, Min(0)]
        public float|null|Optional $purchase_price,

        public string|null|Optional $purchase_vendor,

        #[Numeric, Min(1)]
        public int|null|Optional $expected_lifespan_months,

        #[Numeric, Min(0), Max(100)]
        public int|null|Optional $health_score,

        public string|null|Optional $lifecycle_status,

        public array|null|Optional $specifications,
        public array|null|Optional $custom_fields,

        public string|null|Optional $purchase_date,

        #[AfterOrEqual('purchase_date')]
        public string|null|Optional $warranty_expiry,
    ) {
    }

    /**
     * Additional validation rules
     */
    public static function rules(...$args): array
    {
        return [
            'serial_number' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    if (empty($value)) {
                        return;
                    }

                    // Get equipment ID from route
                    $equipmentId = request()->route('equipment') ?? request()->route('id');

                    // Check for duplicate serial number
                    $exists = \App\Models\Equipment::where('serial_number', $value)
                        ->when($equipmentId, fn($q) => $q->where('id', '!=', $equipmentId))
                        ->exists();

                    if ($exists) {
                        $fail('Equipment with this serial number already exists.');
                    }
                },
            ],
            'parent_equipment_id' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    if (empty($value)) {
                        return;
                    }

                    $data = request()->all();
                    $customerId = $data['customer_id'] ?? null;

                    if (empty($customerId)) {
                        return;
                    }

                    // Ensure parent equipment belongs to same customer
                    $parent = \App\Models\Equipment::find($value);
                    if ($parent && $parent->customer_id !== (int) $customerId) {
                        $fail('Parent equipment must belong to the same customer.');
                    }
                },
            ],
        ];
    }
}
