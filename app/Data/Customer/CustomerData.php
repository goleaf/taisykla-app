<?php

namespace App\Data\Customer;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Data object for Customer (Organization) information.
 * 
 * Expanded to match Organization model structure.
 */
class CustomerData extends Data
{
    public function __construct(
        public int|Optional $id,

        #[Required, Max(255)]
        public string $name,

        #[Email, Max(255)]
        public ?string $email,

        #[Max(20)]
        public ?string $phone,

        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $postal_code,
        public ?string $country,

        #[Max(255)]
        public ?string $website,

        #[Max(100)]
        public ?string $tax_id,

        public ?string $type, // 'customer', 'vendor', 'partner', etc.

        public ?string $notes,

        public bool $is_active = true,

        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,
    ) {
    }

    /**
     * Create from Organization model
     */
    public static function fromOrganization(\App\Models\Organization $organization): self
    {
        return new self(
            id: $organization->id,
            name: $organization->name,
            email: $organization->email,
            phone: $organization->phone,
            address: $organization->address,
            city: $organization->city,
            state: $organization->state,
            postal_code: $organization->postal_code,
            country: $organization->country,
            website: $organization->website,
            tax_id: $organization->tax_id,
            type: $organization->type,
            notes: $organization->notes,
            is_active: $organization->is_active ?? true,
            created_at: $organization->created_at ? CarbonImmutable::parse($organization->created_at) : null,
            updated_at: $organization->updated_at ? CarbonImmutable::parse($organization->updated_at) : null,
        );
    }
}
