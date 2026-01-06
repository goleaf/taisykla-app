<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Scheduling\TechnicianScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Zap\Facades\Zap;

class ServiceRequestSchedulingController extends Controller
{
    public function __construct(
        protected TechnicianScheduleService $scheduleService
    ) {
    }

    /**
     * Show available time slots for scheduling a service request.
     */
    public function showAvailability(ServiceRequest $serviceRequest)
    {
        $this->authorize('assign', $serviceRequest);

        // Get estimated duration in minutes
        $estimatedHours = $serviceRequest->estimated_hours ?? 2;
        $duration = (int) ($estimatedHours * 60);

        // Get available technicians (active with technician role)
        $technicians = User::whereHas('roles', function ($q) {
            $q->where('name', 'technician');
        })->where('is_active', true)->get();

        // Get availability for next 7 days for each technician
        $availability = [];

        foreach ($technicians as $technician) {
            $techAvailability = [];

            for ($i = 0; $i < 7; $i++) {
                $date = now()->addDays($i)->format('Y-m-d');

                $slots = $this->scheduleService->getAvailableSlots(
                    $technician,
                    $date,
                    $duration,
                    15 // buffer minutes
                );

                if (!empty($slots)) {
                    $techAvailability[$date] = $slots;
                }
            }

            if (!empty($techAvailability)) {
                $availability[$technician->id] = [
                    'technician' => $technician,
                    'slots' => $techAvailability,
                ];
            }
        }

        return view('service-requests.schedule', compact(
            'serviceRequest',
            'availability',
            'technicians',
            'duration'
        ));
    }

    /**
     * Schedule a service request.
     */
    public function schedule(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize('assign', $serviceRequest);

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $technician = User::findOrFail($validated['technician_id']);

        // Verify technician is available
        $isAvailable = $this->scheduleService->isAvailable(
            $technician,
            $validated['scheduled_date'],
            $validated['start_time'],
            $validated['end_time']
        );

        if (!$isAvailable) {
            return back()->withErrors(['conflict' => 'The selected time slot is no longer available.']);
        }

        DB::transaction(function () use ($serviceRequest, $technician, $validated) {
            // Create appointment schedule
            Zap::for($technician)
                ->named("SR #{$serviceRequest->id} - " . ($serviceRequest->title ?? 'Service Request'))
                ->description("Service for " . ($serviceRequest->customer->name ?? 'Customer'))
                ->appointment()
                ->from($validated['scheduled_date'])
                ->addPeriod($validated['start_time'], $validated['end_time'])
                ->withMetadata([
                    'service_request_id' => $serviceRequest->id,
                    'customer_id' => $serviceRequest->customer_id,
                    'equipment_id' => $serviceRequest->equipment_id,
                    'priority' => $serviceRequest->priority,
                ])
                ->save();

            // Update service request
            $serviceRequest->update([
                'technician_id' => $technician->id,
                'scheduled_at' => $validated['scheduled_date'] . ' ' . $validated['start_time'],
                'status' => ServiceRequest::STATUS_ASSIGNED,
            ]);
        });

        return redirect()
            ->route('service-requests.show', $serviceRequest)
            ->with('success', 'Service request scheduled successfully.');
    }

    /**
     * Show reschedule form.
     */
    public function showReschedule(ServiceRequest $serviceRequest)
    {
        $this->authorize('update', $serviceRequest);

        if (!$serviceRequest->technician) {
            return redirect()
                ->route('service-requests.schedule.availability', $serviceRequest)
                ->with('info', 'Please assign a technician first.');
        }

        $technician = $serviceRequest->technician;
        $estimatedHours = $serviceRequest->estimated_hours ?? 2;
        $duration = (int) ($estimatedHours * 60);

        // Get availability for next 14 days
        $availability = [];
        for ($i = 0; $i < 14; $i++) {
            $date = now()->addDays($i)->format('Y-m-d');

            $slots = $this->scheduleService->getAvailableSlots(
                $technician,
                $date,
                $duration,
                15
            );

            if (!empty($slots)) {
                $availability[$date] = $slots;
            }
        }

        return view('service-requests.reschedule', compact(
            'serviceRequest',
            'availability',
            'technician',
            'duration'
        ));
    }

    /**
     * Reschedule a service request.
     */
    public function reschedule(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize('update', $serviceRequest);

        $validated = $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:500',
        ]);

        $technician = $serviceRequest->technician;

        if (!$technician) {
            return back()->withErrors(['technician' => 'No technician assigned.']);
        }

        // Verify availability
        $isAvailable = $this->scheduleService->isAvailable(
            $technician,
            $validated['scheduled_date'],
            $validated['start_time'],
            $validated['end_time']
        );

        if (!$isAvailable) {
            return back()->withErrors(['conflict' => 'The selected time slot is not available.']);
        }

        DB::transaction(function () use ($serviceRequest, $technician, $validated) {
            // Delete old appointment schedule
            $technician->schedules()
                ->where('type', 'appointment')
                ->whereJsonContains('metadata->service_request_id', $serviceRequest->id)
                ->delete();

            // Create new appointment
            Zap::for($technician)
                ->named("SR #{$serviceRequest->id} (Rescheduled)")
                ->appointment()
                ->from($validated['scheduled_date'])
                ->addPeriod($validated['start_time'], $validated['end_time'])
                ->withMetadata([
                    'service_request_id' => $serviceRequest->id,
                    'customer_id' => $serviceRequest->customer_id,
                    'rescheduled' => true,
                    'reschedule_reason' => $validated['reason'] ?? null,
                ])
                ->save();

            // Update service request
            $serviceRequest->update([
                'scheduled_at' => $validated['scheduled_date'] . ' ' . $validated['start_time'],
            ]);
        });

        return redirect()
            ->route('service-requests.show', $serviceRequest)
            ->with('success', 'Service request rescheduled successfully.');
    }
}
