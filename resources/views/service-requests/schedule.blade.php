@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6" x-data="scheduleServiceRequest()">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Schedule Service Request #{{ str_pad($serviceRequest->id, 5, '0', STR_PAD_LEFT) }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select a technician and time slot for this {{ $duration }} minute appointment
                    </p>
                </div>
                <a href="{{ route('service-requests.show', $serviceRequest) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>

        {{-- Service Request Summary --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Customer</span>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $serviceRequest->customer->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Equipment</span>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $serviceRequest->equipment->model ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Priority</span>
                    <p class="mt-1">
                        @php
                            $priorityColors = [
                                'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                            ];
                        @endphp
                        <span
                            class="px-2 py-1 text-xs font-medium rounded-full {{ $priorityColors[$serviceRequest->priority] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($serviceRequest->priority) }}
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Est. Duration</span>
                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $duration }} minutes</p>
                </div>
            </div>
        </div>

        @if(count($availability) === 0)
            {{-- No Availability --}}
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-yellow-800 dark:text-yellow-200">No Available Slots</h3>
                <p class="mt-1 text-sm text-yellow-600 dark:text-yellow-400">
                    No technicians have availability for the next 7 days. Please try again later or contact dispatch.
                </p>
            </div>
        @else
            <form action="{{ route('service-requests.schedule.store', $serviceRequest) }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Technician Selection --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden sticky top-4">
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">1. Select Technician</h2>
                            </div>
                            <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                                @foreach($availability as $techId => $data)
                                    <label class="block p-4 rounded-lg border-2 cursor-pointer transition"
                                        :class="selectedTechnician == {{ $techId }} ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'">
                                        <input type="radio" name="technician_id" value="{{ $techId }}" x-model="selectedTechnician"
                                            @change="selectedDate = ''; selectedSlot = null" class="sr-only">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                                <span
                                                    class="text-white font-semibold">{{ substr($data['technician']->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $data['technician']->name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ count($data['slots']) }} days available
                                                </p>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Date & Time Selection --}}
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Date Selection --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">2. Select Date</h2>
                            </div>
                            <div class="p-4">
                                <template x-if="!selectedTechnician">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                        Please select a technician first
                                    </p>
                                </template>
                                <template x-if="selectedTechnician">
                                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-7 gap-2">
                                        @foreach($availability as $techId => $data)
                                            @foreach($data['slots'] as $date => $slots)
                                                <button type="button" x-show="selectedTechnician == {{ $techId }}"
                                                    @click="selectedDate = '{{ $date }}'; selectedSlot = null"
                                                    :class="selectedDate === '{{ $date }}' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600'"
                                                    class="p-3 rounded-lg text-center transition">
                                                    <span
                                                        class="block text-xs font-medium">{{ \Carbon\Carbon::parse($date)->format('D') }}</span>
                                                    <span
                                                        class="block text-lg font-bold">{{ \Carbon\Carbon::parse($date)->format('j') }}</span>
                                                    <span class="block text-xs">{{ \Carbon\Carbon::parse($date)->format('M') }}</span>
                                                </button>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Time Slot Selection --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">3. Select Time</h2>
                            </div>
                            <div class="p-4">
                                <template x-if="!selectedDate">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                        Please select a date first
                                    </p>
                                </template>
                                <template x-if="selectedDate">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                        @foreach($availability as $techId => $data)
                                            @foreach($data['slots'] as $date => $slots)
                                                @foreach($slots as $index => $slot)
                                                    <button type="button"
                                                        x-show="selectedTechnician == {{ $techId }} && selectedDate === '{{ $date }}'"
                                                        @click="selectSlot('{{ $slot['start_time'] ?? '' }}', '{{ $slot['end_time'] ?? '' }}')"
                                                        :class="selectedSlot && selectedSlot.start === '{{ $slot['start_time'] ?? '' }}' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-600'"
                                                        class="p-3 rounded-lg text-center transition">
                                                        <span class="block font-medium">{{ $slot['start_time'] ?? 'N/A' }}</span>
                                                        <span class="block text-xs opacity-75">to {{ $slot['end_time'] ?? 'N/A' }}</span>
                                                    </button>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Hidden inputs for form submission --}}
                        <input type="hidden" name="scheduled_date" x-model="selectedDate">
                        <input type="hidden" name="start_time" x-model="selectedSlot?.start">
                        <input type="hidden" name="end_time" x-model="selectedSlot?.end">

                        {{-- Submit --}}
                        <div class="flex justify-end">
                            <button type="submit" :disabled="!selectedTechnician || !selectedDate || !selectedSlot"
                                :class="(!selectedTechnician || !selectedDate || !selectedSlot) ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-8 py-3 text-white font-medium rounded-lg transition">
                                Schedule Appointment
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif

        @if($errors->any())
            <div class="fixed bottom-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ $errors->first() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function scheduleServiceRequest() {
                return {
                    selectedTechnician: null,
                    selectedDate: '',
                    selectedSlot: null,

                    selectSlot(start, end) {
                        this.selectedSlot = { start, end };
                    }
                }
            }
        </script>
    @endpush
@endsection