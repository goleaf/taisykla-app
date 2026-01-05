<?php

namespace App\Data\ServiceRequest;

use App\Data\Customer\CustomerData;
use App\Data\Equipment\EquipmentData;
use App\Data\Technician\TechnicianData;
use App\Models\ServiceRequest;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;

/**
 * Main Service Request Data object for transforming and validating service request data.
 * 
 * Used for:
 * - API responses
 * - Data transfer between layers
 * - Validation and type safety
 */
class ServiceRequestData extends Data
{
    public function __construct(
        public int|Optional $id,

        #[Required, Exists('organizations', 'id')]
        public int $customer_id,

        #[Exists('equipment', 'id')]
        public ?int $equipment_id,

        #[Exists('users', 'id')]
        public ?int $technician_id,

        #[Required, In([ServiceRequest::PRIORITY_LOW, ServiceRequest::PRIORITY_MEDIUM, ServiceRequest::PRIORITY_HIGH, ServiceRequest::PRIORITY_URGENT])]
        public string $priority,

        #[Required, In([ServiceRequest::STATUS_PENDING, ServiceRequest::STATUS_ASSIGNED, ServiceRequest::STATUS_IN_PROGRESS, ServiceRequest::STATUS_COMPLETED, ServiceRequest::STATUS_CANCELLED])]
        public string $status,

        #[Required, Max(5000)]
        public string $description,

        public ?CarbonImmutable $scheduled_at,
        public ?CarbonImmutable $started_at,
        public ?CarbonImmutable $completed_at,

        #[Numeric]
        public ?float $estimated_hours,

        #[Numeric]
        public ?float $actual_hours,

        #[Numeric]
        public ?float $estimated_cost,

        #[Numeric]
        public ?float $actual_cost,

        #[Required, In([ServiceRequest::APPROVAL_PENDING, ServiceRequest::APPROVAL_APPROVED, ServiceRequest::APPROVAL_REJECTED])]
        public string $approval_status,

        public ?int $approved_by,
        public ?CarbonImmutable $approved_at,

        #[Max(2000)]
        public ?string $rejection_reason,

        #[Max(2000)]
        public ?string $customer_notes,

        #[Max(2000)]
        public ?string $technician_notes,

        #[Max(2000)]
        public ?string $internal_notes,

        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,
        public ?CarbonImmutable $deleted_at,

        // Lazy loaded relationships
        public Lazy|CustomerData|Optional $customer,
        public Lazy|EquipmentData|null|Optional $equipment,
        public Lazy|TechnicianData|null|Optional $technician,
    ) {
    }

    /**
     * Create from Eloquent model with lazy loading support
     */
    public static function fromModel(ServiceRequest $serviceRequest): self
    {
        return new self(
            id: $serviceRequest->id,
            customer_id: $serviceRequest->customer_id,
            equipment_id: $serviceRequest->equipment_id,
            technician_id: $serviceRequest->technician_id,
            priority: $serviceRequest->priority,
            status: $serviceRequest->status,
            description: $serviceRequest->description ?? '',
            scheduled_at: $serviceRequest->scheduled_at ? CarbonImmutable::parse($serviceRequest->scheduled_at) : null,
            started_at: $serviceRequest->started_at ? CarbonImmutable::parse($serviceRequest->started_at) : null,
            completed_at: $serviceRequest->completed_at ? CarbonImmutable::parse($serviceRequest->completed_at) : null,
            estimated_hours: $serviceRequest->estimated_hours,
            actual_hours: $serviceRequest->actual_hours,
            estimated_cost: $serviceRequest->estimated_cost,
            actual_cost: $serviceRequest->actual_cost,
            approval_status: $serviceRequest->approval_status,
            approved_by: $serviceRequest->approved_by,
            approved_at: $serviceRequest->approved_at ? CarbonImmutable::parse($serviceRequest->approved_at) : null,
            rejection_reason: $serviceRequest->rejection_reason,
            customer_notes: $serviceRequest->customer_notes,
            technician_notes: $serviceRequest->technician_notes,
            internal_notes: $serviceRequest->internal_notes,
            created_at: $serviceRequest->created_at ? CarbonImmutable::parse($serviceRequest->created_at) : null,
            updated_at: $serviceRequest->updated_at ? CarbonImmutable::parse($serviceRequest->updated_at) : null,
            deleted_at: $serviceRequest->deleted_at ? CarbonImmutable::parse($serviceRequest->deleted_at) : null,
            customer: Lazy::whenLoaded(
                'customer',
                $serviceRequest,
                fn() =>
                CustomerData::from($serviceRequest->customer)
            ),
            equipment: Lazy::whenLoaded(
                'equipment',
                $serviceRequest,
                fn() =>
                $serviceRequest->equipment ? EquipmentData::from($serviceRequest->equipment) : null
            ),
            technician: Lazy::whenLoaded(
                'technician',
                $serviceRequest,
                fn() =>
                $serviceRequest->technician ? TechnicianData::from($serviceRequest->technician) : null
            ),
        );
    }
}
