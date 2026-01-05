<?php

namespace App\Data\Parts;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

/**
 * Data object for Inventory Transactions (stock movements).
 */
class InventoryTransactionData extends Data
{
    public function __construct(
        public ?int $id,
        public int $part_id,

        public string $type, // 'purchase', 'usage', 'adjustment', 'return', 'transfer'

        public int $quantity,
        public int $quantity_before,
        public int $quantity_after,

        public ?float $cost_per_unit,
        public ?float $total_cost,

        public ?int $related_id, // work_order_id, purchase_order_id, etc.
        public ?string $related_type, // 'work_order', 'purchase_order', etc.

        public ?int $user_id,
        public ?string $reference_number,
        public ?string $notes,

        public ?CarbonImmutable $transaction_date,
        public ?CarbonImmutable $created_at,
    ) {
    }
}
