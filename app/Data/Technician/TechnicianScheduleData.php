<?php

namespace App\Data\Technician;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

/**
 * Data object for Technician Schedule information.
 */
class TechnicianScheduleData extends Data
{
    public function __construct(
        public ?int $id,
        public int $technician_id,

        #[Max(255)]
        public string $day_of_week, // Monday, Tuesday, etc.

        public string $start_time, // HH:MM
        public string $end_time,   // HH:MM

        public bool $is_available = true,

        public ?string $notes,
    ) {
    }
}
