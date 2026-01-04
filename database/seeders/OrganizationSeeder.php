<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\ServiceAgreement;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Support\RoleCatalog;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Standard Service Agreements
        $agreements = [
            [
                'name' => 'Pay-As-You-Go',
                'agreement_type' => 'pay_per_service',
                'response_time_minutes' => 1440, // 24 hours
                'resolution_time_minutes' => 2880, // 48 hours
                'monthly_fee' => 0.00,
                'is_active' => true,
            ],
            [
                'name' => 'Standard Business',
                'agreement_type' => 'retainer',
                'response_time_minutes' => 240, // 4 hours
                'resolution_time_minutes' => 1440, // 24 hours
                'monthly_fee' => 150.00,
                'is_active' => true,
            ],
            [
                'name' => 'Comprehensive Support',
                'agreement_type' => 'comprehensive',
                'response_time_minutes' => 60, // 1 hour
                'resolution_time_minutes' => 480, // 8 hours
                'monthly_fee' => 499.00,
                'is_active' => true,
            ],
        ];

        $createdAgreements = [];
        foreach ($agreements as $data) {
            $createdAgreements[] = ServiceAgreement::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        // 2. Create Acme Corp (Key Customer)
        $acmeAgreement = collect($createdAgreements)->firstWhere('name', 'Comprehensive Support');
        $acme = Organization::firstOrCreate(
            ['name' => 'Acme Corporation'],
            [
                'type' => 'business',
                'status' => 'active',
                'service_agreement_id' => $acmeAgreement->id,
                'primary_contact_name' => 'Jamie Client',
                'primary_contact_email' => 'client@example.com',
                'billing_address' => '123 Acme Way, Metropolis',
            ]
        );

        // Assign key user to Acme
        $clientOwner = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Jamie Client',
                'password' => Hash::make('password'),
                'organization_id' => $acme->id,
                'job_title' => 'Office Manager',
                'email_verified_at' => now(),
            ]
        );
        $clientOwner->assignRole(RoleCatalog::CLIENT);

        // 3. Create Random Organizations
        Organization::factory(15)->create()->each(function ($org) use ($createdAgreements) {
            // Assign random agreement
            $org->update(['service_agreement_id' => collect($createdAgreements)->random()->id]);

            // Create a primary contact for each
            $contact = User::factory()->create([
                'organization_id' => $org->id,
                'password' => Hash::make('password'),
                'name' => $org->primary_contact_name ?? fake()->name(),
                'email' => $org->primary_contact_email ?? fake()->unique()->safeEmail(),
                'job_title' => 'Primary Contact',
            ]);
            $contact->assignRole(RoleCatalog::CLIENT);
        });
    }
}