@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $technician->name }}'s Schedule</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage working hours, availability, and time off</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.technicians.show', $technician) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
                <a href="{{ route('admin.technicians.schedule.create', ['technician' => $technician, 'type' => 'availability']) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Availability
                </a>
                <a href="{{ route('admin.technicians.schedule.create', ['technician' => $technician, 'type' => 'blocked']) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                    Block Time
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Quick Actions Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden" x-data="{ showWorkingHours: false, showTimeOff: false }">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h2>
                </div>
                <div class="p-6 space-y-4">
                    {{-- Set Default Working Hours --}}
                    <div>
                        <button @click="showWorkingHours = !showWorkingHours" 
                                class="w-full flex items-center justify-between px-4 py-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium text-blue-700 dark:text-blue-300">Set Default Working Hours</span>
                            </span>
                            <svg class="w-5 h-5 text-blue-600 transform transition-transform" :class="showWorkingHours && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <form x-show="showWorkingHours" x-transition action="{{ route('admin.technicians.schedule.default-hours', $technician) }}" method="POST" class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            @csrf
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Time</label>
                                    <input type="time" name="start_time" value="08:00" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Time</label>
                                    <input type="time" name="end_time" value="17:00" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Working Days</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="weekdays[]" value="{{ $day }}" 
                                               {{ in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($day) }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Save Working Hours
                            </button>
                        </form>
                    </div>

                    {{-- Block Time Off --}}
                    <div>
                        <button @click="showTimeOff = !showTimeOff" 
                                class="w-full flex items-center justify-between px-4 py-3 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium text-red-700 dark:text-red-300">Block Time Off / Vacation</span>
                            </span>
                            <svg class="w-5 h-5 text-red-600 transform transition-transform" :class="showTimeOff && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <form x-show="showTimeOff" x-transition action="{{ route('admin.technicians.schedule.block-time', $technician) }}" method="POST" class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            @csrf
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                    <input type="date" name="start_date" value="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                                    <input type="date" name="end_date" value="{{ now()->addDay()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason (Optional)</label>
                                <input type="text" name="reason" placeholder="e.g., Vacation, Medical Leave" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </div>
                            <button type="submit" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                                Block Time Off
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Schedules List --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Schedules</h2>
                </div>

                @if($schedules->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No schedules yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding availability or blocking time.</p>
                </div>
                @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($schedules as $schedule)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                @if($schedule->type === 'availability')
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                @elseif($schedule->type === 'blocked')
                                <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                </div>
                                @else
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                @endif

                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $schedule->name }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}
                                        @if($schedule->end_date)
                                        - {{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') }}
                                        @endif
                                    </p>
                                    @if($schedule->periods->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($schedule->periods as $period)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200">
                                            {{ $period->start_time }} - {{ $period->end_time }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($schedule->type === 'availability') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($schedule->type === 'blocked') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($schedule->type === 'appointment') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @endif">
                                    {{ ucfirst($schedule->type) }}
                                </span>

                                <div class="flex gap-1">
                                    <a href="{{ route('admin.technicians.schedule.edit', [$technician, $schedule]) }}" 
                                       class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.technicians.schedule.destroy', [$technician, $schedule]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this schedule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                    {{ $schedules->links() }}
                </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Upcoming Appointments --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming (7 Days)</h2>
                </div>

                @if($upcomingAppointments->isEmpty())
                <div class="p-6 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming appointments</p>
                </div>
                @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-80 overflow-y-auto">
                    @foreach($upcomingAppointments as $appointment)
                    <div class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $appointment->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($appointment->start_date)->format('M d') }}
                                    @if($appointment->periods->first())
                                    {{ $appointment->periods->first()->start_time }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Stats Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Schedule Stats</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Availability Schedules</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $schedules->where('type', 'availability')->count() }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Blocked Times</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $schedules->where('type', 'blocked')->count() }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Appointments</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $schedules->where('type', 'appointment')->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
