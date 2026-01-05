<?php

namespace App\Data\ServiceRequest;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Data object for rejecting service requests.
 * 
 * Replaces RejectRequestRequest.
 */
class RejectServiceRequestData extends Data
{
    public function __construct(
        #[Required, Max(2000)]
        public string $rejection_reason,
    ) {
    }
}
