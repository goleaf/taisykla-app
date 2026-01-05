<?php

namespace App\Data\Technician;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

/**
 * Data object for Technician Availability (time-off, breaks, special availability).
 */
class TechnicianAvailabilityData extends Data
{
    public function __construct(
        public ?int $id,
        public int $technician_id,

        public string $type, // 'time_off', 'break', 'meeting', 'training'

        public ?CarbonImmutable $start_datetime,
        public ?CarbonImmutable $end_datetime,

        public ?string $reason,
        public ?string $notes,

        public bool $is_approved = false,
        public ?int $approved_by_user_id,
    ) {
    }
}
