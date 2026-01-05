<?php

namespace App\Data\Invoice;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;

/**
 * Main Invoice Data object.
 */
class InvoiceData extends Data
{
    public function __construct(
        public int|Optional $id,

        #[Required]
        public int $customer_id,

        #[Required, Max(100)]
        public string $invoice_number,

        #[Required, Max(50)]
        public string $status, // 'draft', 'sent', 'paid', 'overdue', 'cancelled'

        #[Numeric, Min(0)]
        public float $subtotal = 0,

        #[Numeric, Min(0)]
        public float $tax_amount = 0,

        #[Numeric, Min(0)]
        public float $discount_amount = 0,

        #[Numeric, Min(0)]
        public float $total_amount = 0,

        #[Numeric, Min(0)]
        public float $amount_paid = 0,

        #[Numeric, Min(0)]
        public float $balance_due = 0,

        public ?string $currency = 'USD',

        public ?string $notes,
        public ?string $terms,

        public ?int $payment_terms_days,

        public ?CarbonImmutable $invoice_date,
        public ?CarbonImmutable $due_date,
        public ?CarbonImmutable $paid_at,

        public ?int $created_by_user_id,

        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,

        // Lazy loaded relationships
        public Lazy|array|Optional $items, // Invoice items
    ) {
    }
}
