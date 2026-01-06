<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TechnicianCalendarController extends Controller
{
    /**
     * Display technician's calendar.
     */
    public function show(User $technician, Request $request)
    {
        $this->authorize('viewSchedule', $technician);

        $view = $request->get('view', 'week');
        $date = $request->get('date', now()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);

        // Calculate date range based on view type
        [$startDate, $endDate] = match ($view) {
            'day' => [$currentDate->copy(), $currentDate->copy()],
            'week' => [
                $currentDate->copy()->startOfWeek(Carbon::MONDAY),
                $currentDate->copy()->endOfWeek(Carbon::SUNDAY)
            ],
            'month' => [
                $currentDate->copy()->startOfMonth(),
                $currentDate->copy()->endOfMonth()
            ],
            default => [
                $currentDate->copy()->startOfWeek(Carbon::MONDAY),
                $currentDate->copy()->endOfWeek(Carbon::SUNDAY)
            ],
        };

        // Get schedules for the date range
        $schedules = $technician->schedules()
            ->with('periods')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                            ->where('end_date', '>=', $endDate->format('Y-m-d'));
                    });
            })
            ->get();

        // Group schedules by type for easy access
        $groupedSchedules = $schedules->groupBy('type');

        // Generate calendar data structure
        $calendarData = $this->generateCalendarData(
            $view,
            $startDate,
            $endDate,
            $schedules
        );

        // Navigation dates
        $navigation = $this->getNavigationDates($view, $currentDate);

        return view('technicians.calendar', compact(
            'technician',
            'view',
            'currentDate',
            'startDate',
            'endDate',
            'schedules',
            'groupedSchedules',
            'calendarData',
            'navigation'
        ));
    }

    /**
     * Get events as JSON for AJAX calendar updates.
     */
    public function events(User $technician, Request $request)
    {
        $this->authorize('viewSchedule', $technician);

        $start = Carbon::parse($request->get('start', now()->startOfMonth()));
        $end = Carbon::parse($request->get('end', now()->endOfMonth()));

        $schedules = $technician->schedules()
            ->with('periods')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
            })
            ->get();

        $events = $schedules->map(function ($schedule) {
            $color = match ($schedule->type) {
                'availability' => '#10B981', // green
                'blocked' => '#EF4444', // red
                'appointment' => '#3B82F6', // blue
                default => '#6B7280', // gray
            };

            return [
                'id' => $schedule->id,
                'title' => $schedule->name,
                'start' => $schedule->start_date,
                'end' => $schedule->end_date,
                'color' => $color,
                'type' => $schedule->type,
                'periods' => $schedule->periods->map(fn($p) => [
                    'start' => $p->start_time,
                    'end' => $p->end_time,
                ]),
                'metadata' => $schedule->metadata,
            ];
        });

        return response()->json($events);
    }

    /**
     * Generate calendar data structure for the view.
     */
    protected function generateCalendarData(string $view, Carbon $startDate, Carbon $endDate, $schedules): array
    {
        $data = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateKey = $current->format('Y-m-d');

            // Find schedules for this date
            $daySchedules = $schedules->filter(function ($schedule) use ($current) {
                $scheduleStart = Carbon::parse($schedule->start_date);
                $scheduleEnd = Carbon::parse($schedule->end_date ?? $schedule->start_date);

                return $current->between($scheduleStart, $scheduleEnd);
            });

            $data[$dateKey] = [
                'date' => $current->copy(),
                'isToday' => $current->isToday(),
                'isCurrentMonth' => $current->month === $startDate->month,
                'dayOfWeek' => $current->dayOfWeek,
                'schedules' => $daySchedules->values(),
            ];

            $current->addDay();
        }

        return $data;
    }

    /**
     * Get navigation dates for prev/next buttons.
     */
    protected function getNavigationDates(string $view, Carbon $currentDate): array
    {
        return match ($view) {
            'day' => [
                'prev' => $currentDate->copy()->subDay()->format('Y-m-d'),
                'next' => $currentDate->copy()->addDay()->format('Y-m-d'),
                'label' => $currentDate->format('l, F j, Y'),
            ],
            'week' => [
                'prev' => $currentDate->copy()->subWeek()->format('Y-m-d'),
                'next' => $currentDate->copy()->addWeek()->format('Y-m-d'),
                'label' => $currentDate->copy()->startOfWeek()->format('M j') . ' - ' .
                    $currentDate->copy()->endOfWeek()->format('M j, Y'),
            ],
            'month' => [
                'prev' => $currentDate->copy()->subMonth()->format('Y-m-d'),
                'next' => $currentDate->copy()->addMonth()->format('Y-m-d'),
                'label' => $currentDate->format('F Y'),
            ],
            default => [
                'prev' => $currentDate->copy()->subWeek()->format('Y-m-d'),
                'next' => $currentDate->copy()->addWeek()->format('Y-m-d'),
                'label' => $currentDate->format('F Y'),
            ],
        };
    }
}
