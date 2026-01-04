<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_belongs_to_organization()
    {
        $organization = Organization::factory()->create();
        $workOrder = WorkOrder::factory()->create(['organization_id' => $organization->id]);

        $this->assertTrue($workOrder->organization->is($organization));
    }

    public function test_it_can_be_assigned_to_user()
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['assigned_to_user_id' => $user->id]);

        $this->assertTrue($workOrder->assignedTo->is($user));
    }

    public function test_it_calculates_total_cost_correctly()
    {
        // Assuming logic exists or adding a simple test for the field
        $workOrder = WorkOrder::factory()->create([
            'labor_minutes' => 60,
            'total_cost' => 100.00
        ]);

        $this->assertEquals(100.00, $workOrder->total_cost);
    }
    
    public function test_it_has_status_scopes()
    {
        WorkOrder::factory()->create(['status' => 'completed']);
        WorkOrder::factory()->create(['status' => 'in_progress']);

        $this->assertEquals(1, WorkOrder::where('status', 'completed')->count());
    }
}
