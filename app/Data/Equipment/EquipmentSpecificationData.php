<?php

namespace App\Data\Equipment;

use Spatie\LaravelData\Data;

/**
 * Data object for Equipment Specifications.
 * 
 * Used for nested JSON specifications within Equipment.
 */
class EquipmentSpecificationData extends Data
{
    public function __construct(
        public ?string $cpu,
        public ?string $ram,
        public ?string $storage,
        public ?string $gpu,
        public ?string $display,
        public ?string $network,
        public ?string $power,
        public ?string $operating_system,
        public ?string $software_version,
        public ?array $ports,
        public ?array $capabilities,
        public ?array $custom,
    ) {
    }
}
