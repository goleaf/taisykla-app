<?php

namespace App\Data\Invoice;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Data object for Payments received against invoices.
 */
class PaymentData extends Data
{
    public function __construct(
        public ?int $id,

        #[Required]
        public int $invoice_id,

        #[Required]
        public int $customer_id,

        #[Required, Max(100)]
        public string $payment_number,

        #[Numeric, Min(0.01)]
        public float $amount,

        #[Required, Max(50)]
        public string $payment_method, // 'cash', 'check', 'credit_card', 'bank_transfer'

        #[Max(100)]
        public ?string $reference_number, // Check number, transaction ID, etc.

        public ?string $notes,

        public ?CarbonImmutable $payment_date,

        public ?int $processed_by_user_id,

        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,
    ) {
    }
}
