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
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Data object for creating new Equipment.
 */
class CreateEquipmentData extends Data
{
    public function __construct(
        #[Required, Exists('organizations', 'id')]
        public int $customer_id,

        #[Exists('equipment_types', 'id')]
        public ?int $equipment_type_id = null,

        #[Exists('equipment', 'id')]
        public ?int $parent_equipment_id = null,

        #[Exists('users', 'id')]
        public ?int $assigned_user_id = null,

        #[Required, Max(255)]
        public string $manufacturer = '',

        #[Required, Max(255)]
        public string $model = '',

        #[Required, Max(255)]
        public string $serial_number = '',

        #[Max(100)]
        public ?string $asset_tag = null,

        public ?string $qr_code = null,
        public ?string $barcode = null,

        #[Required, In([Equipment::STATUS_OPERATIONAL, Equipment::STATUS_NEEDS_REPAIR, Equipment::STATUS_OUT_OF_SERVICE, Equipment::STATUS_RETIRED])]
        public string $status = Equipment::STATUS_OPERATIONAL,

        #[Max(255)]
        public ?string $location = null,

        public ?string $notes = null,
        public ?string $ip_address = null,
        public ?string $mac_address = null,
        public ?string $dimensions = null,

        #[Numeric, Min(0)]
        public ?float $weight = null,

        #[Numeric, Min(0)]
        public ?float $purchase_price = null,

        public ?string $purchase_vendor = null,

        #[Numeric, Min(1)]
        public ?int $expected_lifespan_months = null,

        #[Numeric, Min(0), Max(100)]
        public ?int $health_score = 100,

        public ?string $lifecycle_status = null,

        public ?array $specifications = null,
        public ?array $custom_fields = null,

        public ?string $purchase_date = null,

        #[AfterOrEqual('purchase_date')]
        public ?string $warranty_expiry = null,
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
                    // Check for duplicate serial number
                    $exists = \App\Models\Equipment::where('serial_number', $value)
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
