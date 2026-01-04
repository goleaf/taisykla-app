<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\Equipment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;

class ServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_can_be_created_with_valid_attributes()
    {
        $serviceRequest = ServiceRequest::factory()->create([
            'status' => ServiceRequest::STATUS_PENDING,
            'priority' => ServiceRequest::PRIORITY_HIGH,
        ]);

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => ServiceRequest::STATUS_PENDING,
            'priority' => ServiceRequest::PRIORITY_HIGH,
        ]);
    }

    #[Test]
    public function it_casts_dates_and_decimals_mostly_correctly()
    {
        $serviceRequest = ServiceRequest::factory()->create([
            'scheduled_at' => '2024-01-01 10:00:00',
            'estimated_cost' => 100.50,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $serviceRequest->scheduled_at);
        $this->assertEquals('2024-01-01 10:00:00', $serviceRequest->scheduled_at->format('Y-m-d H:i:s'));
        // Note: decimal casts in Laravel usually return strings or floats depending on driver/version, checking valid value loop
        $this->assertEquals(100.50, $serviceRequest->estimated_cost);
    }

    #[Test]
    public function it_belongs_to_a_customer()
    {
        $customer = Organization::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Organization::class, $serviceRequest->customer);
        $this->assertEquals($customer->id, $serviceRequest->customer->id);
    }

    #[Test]
    public function it_belongs_to_equipment()
    {
        $equipment = Equipment::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['equipment_id' => $equipment->id]);

        $this->assertInstanceOf(Equipment::class, $serviceRequest->equipment);
        $this->assertEquals($equipment->id, $serviceRequest->equipment->id);
    }

    #[Test]
    public function it_belongs_to_a_technician()
    {
        $technician = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['technician_id' => $technician->id]);

        $this->assertInstanceOf(User::class, $serviceRequest->technician);
        $this->assertEquals($technician->id, $serviceRequest->technician->id);
    }

    #[Test]
    public function it_has_a_status_label_accessor()
    {
        $serviceRequest = ServiceRequest::factory()->make(['status' => ServiceRequest::STATUS_IN_PROGRESS]);
        $this->assertEquals('In Progress', $serviceRequest->status_label);

        $serviceRequest->status = ServiceRequest::STATUS_PENDING;
        $this->assertEquals('Pending', $serviceRequest->status_label);
    }

    #[Test]
    public function it_validates_priority_mutator()
    {
        $serviceRequest = new ServiceRequest();

        $serviceRequest->priority = 'INVALID_PRIORITY';
        // Should default to medium
        $this->assertEquals(ServiceRequest::PRIORITY_MEDIUM, $serviceRequest->priority);

        $serviceRequest->priority = ServiceRequest::PRIORITY_URGENT;
        $this->assertEquals(ServiceRequest::PRIORITY_URGENT, $serviceRequest->priority);

        $serviceRequest->priority = 'HIGH'; // Case insensitive check
        $this->assertEquals(ServiceRequest::PRIORITY_HIGH, $serviceRequest->priority);
    }

    #[Test]
    public function it_scopes_active_requests()
    {
        ServiceRequest::factory()->create(['status' => ServiceRequest::STATUS_PENDING]);
        ServiceRequest::factory()->create(['status' => ServiceRequest::STATUS_IN_PROGRESS]);
        ServiceRequest::factory()->create(['status' => ServiceRequest::STATUS_COMPLETED]);
        ServiceRequest::factory()->create(['status' => ServiceRequest::STATUS_CANCELLED]);

        $this->assertEquals(2, ServiceRequest::active()->count());
    }

    #[Test]
    public function it_scopes_high_priority_requests()
    {
        ServiceRequest::factory()->create(['priority' => ServiceRequest::PRIORITY_LOW]);
        ServiceRequest::factory()->create(['priority' => ServiceRequest::PRIORITY_MEDIUM]);
        ServiceRequest::factory()->create(['priority' => ServiceRequest::PRIORITY_HIGH]);
        ServiceRequest::factory()->create(['priority' => ServiceRequest::PRIORITY_URGENT]);

        $this->assertEquals(2, ServiceRequest::highPriority()->count());
    }

    #[Test]
    public function logs_status_changes_via_observer()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $serviceRequest = ServiceRequest::factory()->create(['status' => ServiceRequest::STATUS_PENDING]);

        // Clear initial logs from creation if any (Observer also has created event)
        $serviceRequest->activityLogs()->delete();

        $serviceRequest->update(['status' => ServiceRequest::STATUS_IN_PROGRESS]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => ServiceRequest::class,
            'subject_id' => $serviceRequest->id,
            'action' => 'status_change',
            'user_id' => $user->id,
        ]);

        $log = $serviceRequest->activityLogs()->where('action', 'status_change')->first();
        $this->assertStringContainsString('pending to in_progress', $log->description);
        $this->assertEquals(ServiceRequest::STATUS_PENDING, $log->meta['from']);
        $this->assertEquals(ServiceRequest::STATUS_IN_PROGRESS, $log->meta['to']);
    }

    #[Test]
    public function logs_creation_via_observer()
    {
        DB::listen(function ($query) {
            dump($query->sql, $query->bindings);
        });

        $user = User::factory()->create();
        $this->actingAs($user);

        $serviceRequest = ServiceRequest::factory()->create(['status' => ServiceRequest::STATUS_PENDING]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => ServiceRequest::class,
            'subject_id' => $serviceRequest->id,
            'action' => 'created',
            'user_id' => $user->id,
        ]);
    }
}
