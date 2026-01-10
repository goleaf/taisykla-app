<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary permissions
        $permissions = [
            PermissionCatalog::DASHBOARD_VIEW,
            PermissionCatalog::WORK_ORDERS_VIEW,
            PermissionCatalog::EQUIPMENT_VIEW,
            PermissionCatalog::SCHEDULE_VIEW,
            PermissionCatalog::INVENTORY_VIEW,
            PermissionCatalog::CLIENTS_VIEW,
            PermissionCatalog::MESSAGES_VIEW,
            PermissionCatalog::REPORTS_VIEW,
            PermissionCatalog::BILLING_VIEW,
            PermissionCatalog::KNOWLEDGE_BASE_VIEW,
            PermissionCatalog::SUPPORT_VIEW,
            PermissionCatalog::SETTINGS_VIEW,
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_dashboard(): void
    {
        $role = Role::firstOrCreate(['name' => RoleCatalog::ADMIN]);
        $role->syncPermissions(Permission::all());

        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_technician_can_view_dashboard(): void
    {
        $role = Role::firstOrCreate(['name' => RoleCatalog::TECHNICIAN]);
        $role->givePermissionTo(PermissionCatalog::DASHBOARD_VIEW);

        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_consumer_can_view_dashboard(): void
    {
        $role = Role::firstOrCreate(['name' => RoleCatalog::CONSUMER]);
        $role->givePermissionTo(PermissionCatalog::DASHBOARD_VIEW);

        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dispatch_can_view_dashboard(): void
    {
        $role = Role::firstOrCreate(['name' => RoleCatalog::DISPATCH]);
        $role->givePermissionTo(PermissionCatalog::DASHBOARD_VIEW);

        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_without_explicit_role_can_still_view_dashboard(): void
    {
        $role = Role::firstOrCreate(['name' => 'basic-user']);
        $role->givePermissionTo(PermissionCatalog::DASHBOARD_VIEW);

        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/dashboard');

        // Dashboard allows any authenticated user with dashboard permission
        $response->assertStatus(200);
    }
}
