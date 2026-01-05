<?php

namespace App\Data\Customer;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

/**
 * Data object for Customer Contact information.
 */
class CustomerContactData extends Data
{
    public function __construct(
        public ?int $id,
        public int $customer_id,

        #[Max(255)]
        public string $name,

        #[Max(255)]
        public ?string $title,

        #[Email, Max(255)]
        public ?string $email,

        #[Max(20)]
        public ?string $phone,

        #[Max(20)]
        public ?string $mobile,

        public ?string $department,

        public bool $is_primary = false,
        public bool $receives_notifications = true,

        public ?string $notes,
    ) {
    }
}
