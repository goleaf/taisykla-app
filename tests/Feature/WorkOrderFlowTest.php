<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Organization;
use App\Models\Equipment;
use App\Models\WorkOrderCategory;
use App\Support\RoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;

class WorkOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed essential roles
        $this->seed(\Database\Seeders\RbacSeeder::class);
    }

    public function test_technician_can_view_assigned_work_orders()
    {
        $technician = User::factory()->create();
        $technician->assignRole(RoleCatalog::TECHNICIAN);

        $otherTech = User::factory()->create();
        
        $myJob = WorkOrder::factory()->create(['assigned_to_user_id' => $technician->id]);
        $otherJob = WorkOrder::factory()->create(['assigned_to_user_id' => $otherTech->id]);

        $response = $this->actingAs($technician)->get(route('work-orders.index'));

        $response->assertStatus(200);
        $response->assertSee($myJob->subject);
        // Depending on permission setup, they might not see others
    }

    public function test_work_order_lifecycle()
    {
        // 1. Dispatcher creates Work Order
        $dispatcher = User::factory()->create();
        $dispatcher->assignRole(RoleCatalog::DISPATCH);

        $org = Organization::factory()->create();
        $category = WorkOrderCategory::create(['name' => 'Repair']);

        Livewire::actingAs($dispatcher)
            ->test('work-orders.create-wizard')
            ->set('form.organization_id', $org->id)
            ->set('form.subject', 'Broken Printer')
            ->set('form.category_id', $category->id)
            ->call('save');

        $this->assertDatabaseHas('work_orders', [
            'subject' => 'Broken Printer',
            'status' => 'submitted', // or whatever default is
        ]);

        $workOrder = WorkOrder::where('subject', 'Broken Printer')->first();

        // 2. Technician assigned
        $technician = User::factory()->create();
        $technician->assignRole(RoleCatalog::TECHNICIAN);

        $workOrder->update(['assigned_to_user_id' => $technician->id, 'status' => 'assigned']);

        // 3. Technician starts work (via Dashboard)
        Livewire::actingAs($technician)
            ->test('technician-dashboard')
            ->call('checkIn', $workOrder->id);

        $this->assertEquals('in_progress', $workOrder->fresh()->status);
        $this->assertNotNull($workOrder->fresh()->started_at);

        // 4. Technician completes work
        Livewire::actingAs($technician)
            ->test('technician-dashboard')
            ->call('checkOut', $workOrder->id);

        $this->assertEquals('completed', $workOrder->fresh()->status);
        $this->assertNotNull($workOrder->fresh()->completed_at);
    }
}
