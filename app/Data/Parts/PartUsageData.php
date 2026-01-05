<?php

namespace App\Data\Parts;

use Spatie\LaravelData\Data;

/**
 * Data object for Part Usage in work orders/service requests.
 */
class PartUsageData extends Data
{
    public function __construct(
        public ?int $id,
        public int $part_id,

        public ?int $work_order_id,
        public ?int $service_request_id,

        public int $quantity_used,

        public float $cost_per_unit,
        public float $price_per_unit,

        public float $total_cost,
        public float $total_price,

        public ?string $serial_number, // If part is serialized

        public ?string $notes,

        // Denormalized part info for historical record
        public ?string $part_name,
        public ?string $part_sku,
    ) {
    }
}
