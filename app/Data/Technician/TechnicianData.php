<?php

namespace App\Data\Technician;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Data object for Technician (User) information.
 * 
 * Expanded to match User model structure for technician-specific needs.
 */
class TechnicianData extends Data
{
    public function __construct(
        public int|Optional $id,

        #[Required, Max(255)]
        public string $name,

        #[Required, Email, Max(255)]
        public string $email,

        #[Max(20)]
        public ?string $phone,

        public ?string $avatar,
        public ?string $bio,

        public ?string $specialization,

        public ?array $skills,
        public ?array $certifications,

        public bool $is_available = true,
        public bool $is_active = true,

        public ?string $current_location,
        public ?string $current_status,

        public ?int $completed_jobs_count,
        public ?float $average_rating,

        public ?CarbonImmutable $email_verified_at,
        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,
    ) {
    }

    /**
     * Create from User model
     */
    public static function fromUser(\App\Models\User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            phone: $user->phone ?? null,
            avatar: $user->avatar ?? null,
            bio: $user->bio ?? null,
            specialization: $user->specialization ?? null,
            skills: $user->skills ?? [],
            certifications: $user->certifications ?? [],
            is_available: $user->is_available ?? true,
            is_active: true,
            current_location: null,
            current_status: null,
            completed_jobs_count: null,
            average_rating: null,
            email_verified_at: $user->email_verified_at ? CarbonImmutable::parse($user->email_verified_at) : null,
            created_at: $user->created_at ? CarbonImmutable::parse($user->created_at) : null,
            updated_at: $user->updated_at ? CarbonImmutable::parse($user->updated_at) : null,
        );
    }
}
