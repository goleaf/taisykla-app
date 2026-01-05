<?php

namespace App\Data\Equipment;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

/**
 * Data object for Equipment Maintenance History records.
 */
class MaintenanceHistoryData extends Data
{
    public function __construct(
        public ?int $id,
        public int $equipment_id,
        public ?int $service_request_id,
        public ?int $work_order_id,
        public ?int $technician_id,

        #[Max(255)]
        public string $type,

        public ?string $description,
        public ?string $notes,

        public ?float $cost,
        public ?float $hours,

        public ?CarbonImmutable $performed_at,
        public ?CarbonImmutable $next_due_at,
        public ?CarbonImmutable $created_at,
    ) {
    }
}
