<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create specific system users
        $users = [
            ['email' => 'admin@example.com', 'name' => 'Admin User', 'role' => \App\Support\RoleCatalog::ADMIN],
            ['email' => 'dispatch@example.com', 'name' => 'Dispatch Manager', 'role' => \App\Support\RoleCatalog::DISPATCH],
            ['email' => 'ops@example.com', 'name' => 'Operations Manager', 'role' => \App\Support\RoleCatalog::OPERATIONS_MANAGER],
            ['email' => 'tech@example.com', 'name' => 'Alex Technician', 'role' => \App\Support\RoleCatalog::TECHNICIAN],
            ['email' => 'inventory@example.com', 'name' => 'Pat Inventory', 'role' => \App\Support\RoleCatalog::INVENTORY_SPECIALIST],
            ['email' => 'qa@example.com', 'name' => 'Quinn Quality', 'role' => \App\Support\RoleCatalog::QA_MANAGER],
            ['email' => 'billing@example.com', 'name' => 'Finley Billing', 'role' => \App\Support\RoleCatalog::BILLING_SPECIALIST],
            ['email' => 'support@example.com', 'name' => 'Support Manager', 'role' => \App\Support\RoleCatalog::SUPPORT],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'job_title' => $data['role'],
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole($data['role']);
        }

        // 2. Create random additional technicians
        User::factory(10)->create([
            'password' => Hash::make('password'),
            'job_title' => 'Field Technician',
        ])->each(function (User $user) {
            $user->assignRole(\App\Support\RoleCatalog::TECHNICIAN);
        });

        // 3. Create random additional customers (will be assigned organizations in OrganizationSeeder if needed, or we leave them as consumers)
        User::factory(5)->create([
            'password' => Hash::make('password'),
            'job_title' => 'Customer',
        ])->each(function (User $user) {
            $user->assignRole(\App\Support\RoleCatalog::CONSUMER);
        });
    }
}
