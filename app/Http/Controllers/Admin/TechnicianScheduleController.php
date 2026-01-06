<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Scheduling\TechnicianScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Zap\Facades\Zap;
use Zap\Models\Schedule;

class TechnicianScheduleController extends Controller
{
    public function __construct(
        protected TechnicianScheduleService $scheduleService
    ) {
    }

    /**
     * Display technician's schedules.
     */
    public function index(User $technician)
    {
        $this->authorize('viewSchedule', $technician);

        $schedules = $technician->schedules()
            ->with('periods')
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        // Get upcoming appointments for the week
        $upcomingAppointments = $technician->schedules()
            ->where('type', 'appointment')
            ->where('start_date', '>=', now()->startOfDay())
            ->where('start_date', '<=', now()->addWeek())
            ->with('periods')
            ->orderBy('start_date')
            ->get();

        return view('admin.technicians.schedule.index', compact(
            'technician',
            'schedules',
            'upcomingAppointments'
        ));
    }

    /**
     * Show form to create new schedule.
     */
    public function create(User $technician, Request $request)
    {
        $this->authorize('manageSchedule', $technician);

        $type = $request->query('type', 'availability');

        return view('admin.technicians.schedule.create', compact('technician', 'type'));
    }

    /**
     * Store a new schedule.
     */
    public function store(Request $request, User $technician)
    {
        $this->authorize('manageSchedule', $technician);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:availability,blocked',
            'description' => 'nullable|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_recurring' => 'boolean',
            'recurrence_days' => 'array',
            'recurrence_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'periods' => 'required|array|min:1',
            'periods.*.start_time' => 'required|date_format:H:i',
            'periods.*.end_time' => 'required|date_format:H:i|after:periods.*.start_time',
        ]);

        DB::transaction(function () use ($validated, $technician) {
            $schedule = Zap::for($technician)
                ->named($validated['name']);

            if (!empty($validated['description'])) {
                $schedule->description($validated['description']);
            }

            // Set schedule type
            if ($validated['type'] === 'availability') {
                $schedule->availability();
            } else {
                $schedule->blocked();
            }

            // Set date range
            $schedule->from($validated['start_date']);
            if (!empty($validated['end_date'])) {
                $schedule->to($validated['end_date']);
            } else {
                // Default to end of year if no end date
                $schedule->to(now()->endOfYear()->format('Y-m-d'));
            }

            // Add time periods
            foreach ($validated['periods'] as $period) {
                $schedule->addPeriod($period['start_time'], $period['end_time']);
            }

            // Add weekly recurrence if applicable
            if (!empty($validated['is_recurring']) && !empty($validated['recurrence_days'])) {
                $schedule->weekly($validated['recurrence_days']);
            }

            $schedule->save();
        });

        return redirect()
            ->route('admin.technicians.schedule.index', $technician)
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Show edit form for a schedule.
     */
    public function edit(User $technician, Schedule $schedule)
    {
        $this->authorize('manageSchedule', $technician);

        $schedule->load('periods');

        return view('admin.technicians.schedule.edit', compact('technician', 'schedule'));
    }

    /**
     * Update a schedule.
     */
    public function update(Request $request, User $technician, Schedule $schedule)
    {
        $this->authorize('manageSchedule', $technician);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'periods' => 'required|array|min:1',
            'periods.*.start_time' => 'required|date_format:H:i',
            'periods.*.end_time' => 'required|date_format:H:i|after:periods.*.start_time',
        ]);

        DB::transaction(function () use ($validated, $schedule) {
            $schedule->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? now()->endOfYear()->format('Y-m-d'),
            ]);

            // Update periods - delete existing and recreate
            $schedule->periods()->delete();

            foreach ($validated['periods'] as $period) {
                $schedule->periods()->create([
                    'start_time' => $period['start_time'],
                    'end_time' => $period['end_time'],
                ]);
            }
        });

        return redirect()
            ->route('admin.technicians.schedule.index', $technician)
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Delete a schedule.
     */
    public function destroy(User $technician, Schedule $schedule)
    {
        $this->authorize('manageSchedule', $technician);

        $schedule->delete();

        return redirect()
            ->route('admin.technicians.schedule.index', $technician)
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Quick action: Set default working hours.
     */
    public function setDefaultHours(Request $request, User $technician)
    {
        $this->authorize('manageSchedule', $technician);

        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'weekdays' => 'required|array|min:1',
            'weekdays.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ]);

        $this->scheduleService->setWorkingHours(
            $technician,
            ['start' => $validated['start_time'], 'end' => $validated['end_time']],
            $validated['weekdays']
        );

        return redirect()
            ->route('admin.technicians.schedule.index', $technician)
            ->with('success', 'Default working hours set successfully.');
    }

    /**
     * Quick action: Block time off.
     */
    public function blockTimeOff(Request $request, User $technician)
    {
        $this->authorize('manageSchedule', $technician);

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $this->scheduleService->blockTime(
            $technician,
            \Carbon\Carbon::parse($validated['start_date']),
            \Carbon\Carbon::parse($validated['end_date']),
            $validated['reason']
        );

        return redirect()
            ->route('admin.technicians.schedule.index', $technician)
            ->with('success', 'Time off blocked successfully.');
    }
}
