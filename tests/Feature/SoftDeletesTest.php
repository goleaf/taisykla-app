<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Organization;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeletesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_soft_delete()
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    public function test_work_order_soft_delete()
    {
        // Manually create dependencies
        $user = User::factory()->create();
        $org = new Organization();
        $org->name = 'Test Org';
        $org->save();

        $workOrder = new WorkOrder();
        $workOrder->organization_id = $org->id;
        $workOrder->requested_by_user_id = $user->id;
        $workOrder->status = 'open';
        $workOrder->priority = 'normal';
        $workOrder->subject = 'Test WO';
        $workOrder->save();

        $workOrder->delete();

        $this->assertSoftDeleted($workOrder);
        $this->assertDatabaseHas('work_orders', ['id' => $workOrder->id]);
    }

    public function test_organization_soft_delete()
    {
        $org = new Organization();
        $org->name = 'Another Test Org';
        $org->save();

        $org->delete();

        $this->assertSoftDeleted($org);
    }
}
