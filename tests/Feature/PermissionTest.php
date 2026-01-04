<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RbacSeeder::class);
    }

    public function test_admin_has_all_permissions()
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleCatalog::ADMIN);

        $this->assertTrue($admin->can(PermissionCatalog::WORK_ORDERS_VIEW));
        
        $this->assertTrue($admin->can(PermissionCatalog::SETTINGS_MANAGE));
    }

    public function test_technician_permissions()
    {
        $tech = User::factory()->create();
        $tech->assignRole(RoleCatalog::TECHNICIAN);

        $this->assertTrue($tech->can(PermissionCatalog::WORK_ORDERS_VIEW));
        $this->assertTrue($tech->can(PermissionCatalog::WORK_ORDERS_UPDATE));
        $this->assertFalse($tech->can(PermissionCatalog::SETTINGS_MANAGE));
        $this->assertFalse($tech->can(PermissionCatalog::BILLING_MANAGE));
    }

    public function test_work_order_policy_scoping()
    {
        // Technician with VIEW_ASSIGNED
        $tech = User::factory()->create();
        $tech->assignRole(RoleCatalog::TECHNICIAN); // Has WORK_ORDERS_VIEW_ASSIGNED

        $assignedWorkOrder = WorkOrder::factory()->create(['assigned_to_user_id' => $tech->id]);
        $otherUser = User::factory()->create();
        $otherWorkOrder = WorkOrder::factory()->create(['assigned_to_user_id' => $otherUser->id]);

        $this->assertTrue($tech->can('view', $assignedWorkOrder));
        $this->assertFalse($tech->can('view', $otherWorkOrder));
    }

    public function test_equipment_policy_scoping()
    {
         // Dispatch with VIEW_ALL
        $dispatch = User::factory()->create();
        $dispatch->assignRole(RoleCatalog::DISPATCH);

        $equipment = Equipment::factory()->create();

        $this->assertTrue($dispatch->can('view', $equipment));
    }
}
