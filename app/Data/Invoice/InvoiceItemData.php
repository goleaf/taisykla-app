<?php

namespace App\Data\Invoice;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Data object for Invoice Line Items.
 */
class InvoiceItemData extends Data
{
    public function __construct(
        public ?int $id,

        #[Required]
        public int $invoice_id,

        #[Required, Max(255)]
        public string $description,

        #[Numeric, Min(0)]
        public float $quantity = 1,

        #[Numeric, Min(0)]
        public float $unit_price = 0,

        #[Numeric, Min(0)]
        public float $discount_percentage = 0,

        #[Numeric, Min(0)]
        public float $tax_percentage = 0,

        #[Numeric, Min(0)]
        public float $total = 0,

        public ?int $part_id, // If item is from inventory
        public ?int $service_request_id, // If item is from service
        public ?int $work_order_id, // If item is from work order

        #[Max(100)]
        public ?string $item_code,

        public ?string $item_type, // 'service', 'part', 'labor', 'other'
    ) {
    }
}
