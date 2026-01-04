<?php

namespace Tests\Feature;

use App\Models\PriorityLevel;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Organization;
use App\Services\SLAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SLATest extends TestCase
{
    use RefreshDatabase;

    public function test_sla_targets_calculated_from_priority_level()
    {
        // Setup
        PriorityLevel::create([
            'name' => 'High',
            'color' => '#ff0000',
            'response_time_minutes' => 60,
            'resolution_time_minutes' => 240,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        // Act
        $workOrder = WorkOrder::create([
            'subject' => 'Urgent Issue',
            'status' => 'submitted',
            'priority' => 'High',
            'requested_by_user_id' => $user->id,
        ]);

        // Assert
        $this->assertNotNull($workOrder->target_response_at);
        $this->assertNotNull($workOrder->target_resolution_at);

        // Allow for 1 second difference
        $this->assertEqualsWithDelta(
            $workOrder->created_at->addMinutes(60)->timestamp,
            $workOrder->target_response_at->timestamp,
            1,
            'Response target should be 60 mins from creation'
        );

        $this->assertEqualsWithDelta(
            $workOrder->created_at->addMinutes(240)->timestamp,
            $workOrder->target_resolution_at->timestamp,
            1,
            'Resolution target should be 240 mins from creation'
        );
    }

    public function test_sla_breach_detection()
    {
        // Setup
        $workOrder = WorkOrder::create([
            'subject' => 'Old Issue',
            'status' => 'submitted',
            'priority' => 'Normal',
            'target_response_at' => now()->subMinutes(10), // Passed default
            'sla_status' => 'pending',
        ]);

        // Act
        app(SLAService::class)->checkBreaches();

        // Assert
        $workOrder->refresh();
        $this->assertEquals('breached', $workOrder->sla_status);
        $this->assertNotNull($workOrder->sla_breached_at);
    }

    public function test_sla_targets_calculated_from_service_agreement()
    {
        // Setup
        $organization = Organization::create(['name' => 'Test Org']);
        $agreement = \App\Models\ServiceAgreement::create([
            'name' => 'VIP Support',
            'agreement_type' => 'monthly',
            'monthly_fee' => 100,
            'response_time_minutes' => 30, // Tighter than High priority (60)
            'resolution_time_minutes' => 120, // Tighter than High priority (240)
        ]);
        $organization->service_agreement_id = $agreement->id;
        $organization->save();

        PriorityLevel::create([
            'name' => 'High',
            'color' => '#ff0000',
            'response_time_minutes' => 60,
            'resolution_time_minutes' => 240,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        // Act
        $workOrder = WorkOrder::create([
            'subject' => 'VIP Issue',
            'status' => 'submitted',
            'priority' => 'High',
            'organization_id' => $organization->id,
            'requested_by_user_id' => $user->id,
        ]);

        // Assert
        $this->assertEqualsWithDelta(
            $workOrder->created_at->addMinutes(30)->timestamp,
            $workOrder->target_response_at->timestamp,
            1,
            'Response target should use Agreement time (30m)'
        );

        $this->assertEqualsWithDelta(
            $workOrder->created_at->addMinutes(120)->timestamp,
            $workOrder->target_resolution_at->timestamp,
            1,
            'Resolution target should use Agreement time (120m)'
        );
    }
}
