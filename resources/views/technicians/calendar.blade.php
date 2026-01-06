@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $technician->name }}'s Calendar</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $navigation['label'] }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- View Toggle --}}
                    <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
                        <a href="{{ route('technicians.calendar', ['technician' => $technician, 'view' => 'day', 'date' => $currentDate->format('Y-m-d')]) }}"
                            class="px-4 py-2 text-sm font-medium rounded-md transition {{ $view === 'day' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900' }}">
                            Day
                        </a>
                        <a href="{{ route('technicians.calendar', ['technician' => $technician, 'view' => 'week', 'date' => $currentDate->format('Y-m-d')]) }}"
                            class="px-4 py-2 text-sm font-medium rounded-md transition {{ $view === 'week' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900' }}">
                            Week
                        </a>
                        <a href="{{ route('technicians.calendar', ['technician' => $technician, 'view' => 'month', 'date' => $currentDate->format('Y-m-d')]) }}"
                            class="px-4 py-2 text-sm font-medium rounded-md transition {{ $view === 'month' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900' }}">
                            Month
                        </a>
                    </div>

                    {{-- Navigation --}}
                    <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
                        <a href="{{ route('technicians.calendar', ['technician' => $technician, 'view' => $view, 'date' => $navigation['prev']]) }}"
                            class="px-3 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                                </path>
                            </svg>
                        </a>
                        <a href="{{ route('technicians.calendar', ['technician' => $technician, 'view' => $view, 'date' => now()->format('Y-m-d')]) }}"
                            class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Today
                        </a>
                        <a href="{{ route('technicians.calendar', ['technician' => $technician, 'view' => $view, 'date' => $navigation['next']]) }}"
                            class="px-3 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                    </div>

                    {{-- Actions --}}
                    <a href="{{ route('admin.technicians.schedule.create', ['technician' => $technician, 'type' => 'availability']) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Schedule
                    </a>
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="mb-4 flex flex-wrap gap-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Availability</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Appointment</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Blocked</span>
            </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            @if($view === 'day')
                @include('technicians.partials.calendar-day')
            @elseif($view === 'week')
                @include('technicians.partials.calendar-week')
            @else
                @include('technicians.partials.calendar-month')
            @endif
        </div>
    </div>
@endsection