<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Acme Corp
        $serviceAgreement = \App\Models\ServiceAgreement::firstOrCreate(
            ['name' => 'Comprehensive Support'],
            [
                'agreement_type' => 'comprehensive',
                'response_time_minutes' => 120,
                'resolution_time_minutes' => 480,
                'monthly_fee' => 299.00,
                'is_active' => true,
            ]
        );

        $acme = Organization::firstOrCreate(
            ['name' => 'Acme Corporation'],
            [
                'type' => 'business',
                'status' => 'active',
                'service_agreement_id' => $serviceAgreement->id,
            ]
        );

        // Assign some key users to Acme
        $clientOwner = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Jamie Client',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'organization_id' => $acme->id,
                'job_title' => 'Office Manager',
            ]
        );
        $clientOwner->assignRole(\App\Support\RoleCatalog::CLIENT);

        // 2. Create Random Organizations
        Organization::factory(10)->create()->each(function ($org) {
            // Create a primary contact for each
            $contact = User::factory()->create([
                'organization_id' => $org->id,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'name' => $org->primary_contact_name ?? fake()->name(),
                'email' => $org->primary_contact_email ?? fake()->unique()->safeEmail(),
            ]);
            $contact->assignRole(\App\Support\RoleCatalog::CLIENT);
        });
    }
}
