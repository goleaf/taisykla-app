<?php

namespace App\Data\Parts;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Main Part Data object for inventory items.
 */
class PartData extends Data
{
    public function __construct(
        public int|Optional $id,

        #[Required, Max(255)]
        public string $name,

        #[Required, Max(100)]
        public string $sku,

        #[Max(100)]
        public ?string $manufacturer_part_number,

        public ?int $category_id,
        public ?int $supplier_id,

        #[Max(255)]
        public ?string $manufacturer,

        public ?string $description,

        #[Numeric, Min(0)]
        public float $cost = 0,

        #[Numeric, Min(0)]
        public float $price = 0,

        public int $quantity_on_hand = 0,
        public int $quantity_reserved = 0,
        public int $minimum_quantity = 0,
        public int $reorder_quantity = 0,

        #[Max(50)]
        public ?string $unit_of_measure, // 'each', 'box', 'meter', etc.

        public ?string $location, // Warehouse location

        public bool $is_active = true,
        public bool $is_serialized = false,

        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,
    ) {
    }
}
