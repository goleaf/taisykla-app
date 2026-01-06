@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6" x-data="scheduleEditForm()">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Schedule</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Modify "{{ $schedule->name }}" for {{ $technician->name }}
            </p>
        </div>

        <form action="{{ route('admin.technicians.schedule.update', [$technician, $schedule]) }}" method="POST"
            class="max-w-2xl">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div
                    class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Schedule Details</h2>
                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                        @if($schedule->type === 'availability') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($schedule->type === 'blocked') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @endif">
                        {{ ucfirst($schedule->type) }}
                    </span>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Schedule Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $schedule->name) }}"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="2"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">{{ old('description', $schedule->description) }}</textarea>
                    </div>

                    {{-- Date Range --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                value="{{ old('start_date', $schedule->start_date?->format('Y-m-d')) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                End Date
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                value="{{ old('end_date', $schedule->end_date?->format('Y-m-d')) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- Time Periods --}}
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Time Periods <span class="text-red-500">*</span>
                            </label>
                            <button type="button" @click="addPeriod()"
                                class="inline-flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Period
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(period, index) in periods" :key="index">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Start
                                            Time</label>
                                        <input type="time" :name="'periods[' + index + '][start_time]'"
                                            x-model="period.start_time"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">End Time</label>
                                        <input type="time" :name="'periods[' + index + '][end_time]'"
                                            x-model="period.end_time"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    </div>
                                    <button type="button" @click="removePeriod(index)" x-show="periods.length > 1"
                                        class="mt-5 p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div
                    class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-between">
                    <form action="{{ route('admin.technicians.schedule.destroy', [$technician, $schedule]) }}" method="POST"
                        onsubmit="return confirm('Delete this schedule?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 font-medium rounded-lg transition">
                            Delete Schedule
                        </button>
                    </form>

                    <div class="flex gap-3">
                        <a href="{{ route('admin.technicians.schedule.index', $technician) }}"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-medium rounded-lg transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function scheduleEditForm() {
                return {
                    periods: @json(old('periods', $schedule->periods->map(fn($p) => ['start_time' => substr($p->start_time, 0, 5), 'end_time' => substr($p->end_time, 0, 5)])->toArray())),

                    addPeriod() {
                        this.periods.push({ start_time: '09:00', end_time: '17:00' });
                    },

                    removePeriod(index) {
                        this.periods.splice(index, 1);
                    }
                }
            }
        </script>
    @endpush
@endsection