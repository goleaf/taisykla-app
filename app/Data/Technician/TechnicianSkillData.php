<?php

namespace App\Data\Technician;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

/**
 * Data object for Technician Skills and Certifications.
 */
class TechnicianSkillData extends Data
{
    public function __construct(
        public ?int $id,

        #[Max(255)]
        public string $name,

        public string $category, // 'hardware', 'software', 'networking', 'certification'

        public ?int $proficiency_level, // 1-5

        public ?string $certification_number,
        public ?string $issuing_organization,
        public ?\Carbon\CarbonImmutable $issued_date,
        public ?\Carbon\CarbonImmutable $expiry_date,

        public bool $is_active = true,
    ) {
    }
}
