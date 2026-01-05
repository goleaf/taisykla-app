<?php

namespace App\Data\Customer;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

/**
 * Data object for Customer Location/Site information.
 */
class CustomerLocationData extends Data
{
    public function __construct(
        public ?int $id,
        public int $customer_id,

        #[Max(255)]
        public string $name,

        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $postal_code,
        public ?string $country,

        public ?float $latitude,
        public ?float $longitude,

        #[Max(100)]
        public ?string $access_code,

        public ?string $access_instructions,
        public ?string $parking_instructions,

        public bool $is_primary = false,
        public bool $is_active = true,

        public ?string $notes,
    ) {
    }
}
