<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Support\RoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear permission cache
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Create roles to test against if they don't exist
        Role::firstOrCreate(['name' => RoleCatalog::TECHNICIAN]);
        Role::firstOrCreate(['name' => RoleCatalog::ADMIN]);
    }

    public function test_it_can_check_if_technician()
    {
        $user = User::factory()->create();
        $user->assignRole(RoleCatalog::TECHNICIAN);

        $this->assertTrue($user->hasRole(RoleCatalog::TECHNICIAN));
    }

    public function test_it_has_full_name()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $this->assertEquals('John Doe', $user->name);
    }
}
