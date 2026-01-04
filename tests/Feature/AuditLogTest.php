<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\WorkOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_is_created_when_work_order_is_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workOrder = WorkOrder::factory()->create();

        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => WorkOrder::class,
            'subject_id' => $workOrder->id,
            'action' => 'created',
            'user_id' => $user->id,
        ]);
    }

    public function test_audit_log_is_created_when_work_order_is_updated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workOrder = WorkOrder::factory()->create(['status' => 'pending']);

        // Update
        $workOrder->update(['status' => 'in_progress']);

        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => WorkOrder::class,
            'subject_id' => $workOrder->id,
            'action' => 'updated',
            'user_id' => $user->id,
        ]);
        
        $log = AuditLog::where('subject_type', WorkOrder::class)
            ->where('subject_id', $workOrder->id)
            ->where('action', 'updated')
            ->orderByDesc('id')
            ->first();
            
        $this->assertNotNull($log);
        $this->assertEquals('pending', $log->meta['old']['status']);
        $this->assertEquals('in_progress', $log->meta['new']['status']);
    }
}
