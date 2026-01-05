<?php

namespace App\Data\ServiceRequest;

use App\Models\ServiceRequest;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Data object for updating service request status.
 * 
 * Replaces UpdateStatusRequest.
 */
class UpdateStatusData extends Data
{
    public function __construct(
        #[Required, In([ServiceRequest::STATUS_PENDING, ServiceRequest::STATUS_ASSIGNED, ServiceRequest::STATUS_IN_PROGRESS, ServiceRequest::STATUS_COMPLETED, ServiceRequest::STATUS_CANCELLED])]
        public string $status,
    ) {
    }
}
