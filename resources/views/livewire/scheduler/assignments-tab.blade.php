{{-- Assignments Tab --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Unassigned Work Orders --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-amber-50">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Unassigned Jobs</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        {{ $this->unassignedWorkOrders->count() }}
                    </span>
                </div>
            </div>

            <div class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
                @forelse ($this->unassignedWorkOrders as $wo)
                    <div class="p-4 hover:bg-gray-50 transition cursor-pointer" wire:click="selectWorkOrderForAssignment({{ $wo->id }})">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <span class="text-xs font-mono text-gray-400">#{{ $wo->id }}</span>
                                <h3 class="font-medium text-gray-900 text-sm">{{ Str::limit($wo->subject, 40) }}</h3>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $wo->priority === 'urgent' ? 'bg-red-100 text-red-700' : 
                                   ($wo->priority === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ ucfirst($wo->priority) }}
                            </span>
                        </div>
                        @if ($wo->organization)
                            <p class="text-xs text-gray-500">{{ $wo->organization->name }}</p>
                        @endif
                        @if ($wo->scheduled_start_at)
                            <p class="text-xs text-gray-400 mt-1">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $wo->scheduled_start_at->format('M j, g:i A') }}
                            </p>
                        @endif
                        <div class="flex items-center gap-2 mt-2">
                            <button wire:click.stop="selectWorkOrderForAssignment({{ $wo->id }})" 
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                Find Technician
                            </button>
                            <button wire:click.stop="openEmergencyInsert({{ $wo->id }})" 
                                class="text-xs text-red-600 hover:text-red-800 font-medium">
                                Emergency
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <p class="text-sm">All jobs assigned!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Technician Recommendations or Schedule View --}}
    <div class="lg:col-span-2">
        @if ($showRecommendations && !empty($recommendations))
            {{-- Recommendation Results --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold text-gray-900">Technician Recommendations</h2>
                            <p class="text-sm text-gray-500">For Work Order #{{ $selectedWorkOrderId }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="autoAssign" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Auto-Assign Best
                            </button>
                            <button wire:click="$set('showRecommendations', false)" class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ($recommendations as $rec)
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-4">
                                {{-- Rank Badge --}}
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                    {{ $rec['rank'] === 1 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                    <span class="text-lg font-bold">#{{ $rec['rank'] }}</span>
                                </div>

                                {{-- Technician Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <h3 class="font-semibold text-gray-900">{{ $rec['name'] }}</h3>
                                            <p class="text-sm text-gray-500">
                                                {{ $rec['availability_status'] === 'available' ? '🟢 Available' : '🟡 Busy' }}
                                                @if ($rec['travel_time'])
                                                    • ~{{ $rec['travel_time'] }} min travel
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold {{ $rec['match_percentage'] >= 80 ? 'text-green-600' : ($rec['match_percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $rec['match_percentage'] }}%
                                            </div>
                                            <div class="text-xs text-gray-500">Match Score</div>
                                        </div>
                                    </div>

                                    {{-- Score Breakdown --}}
                                    <div class="grid grid-cols-4 gap-2 mb-3">
                                        @foreach (['skills', 'proximity', 'availability', 'workload'] as $factor)
                                            <div class="text-center">
                                                <div class="text-xs text-gray-500 mb-1">{{ ucfirst($factor) }}</div>
                                                <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full {{ $rec['factors'][$factor] >= 70 ? 'bg-green-500' : ($rec['factors'][$factor] >= 40 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                        style="width: {{ $rec['factors'][$factor] }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Pros & Cons --}}
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @foreach ($rec['pros'] as $pro)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-50 text-green-700">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $pro }}
                                            </span>
                                        @endforeach
                                        @foreach ($rec['cons'] as $con)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-50 text-red-700">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $con }}
                                            </span>
                                        @endforeach
                                    </div>

                                    {{-- Impact & Estimated Start --}}
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="text-gray-500">
                                            <span class="font-medium">Impact:</span>
                                            {{ $rec['impact']['current_utilization'] }} → {{ $rec['impact']['utilization_after'] }} utilization
                                        </div>
                                        @if ($rec['estimated_start'])
                                            <div class="text-gray-500">
                                                <span class="font-medium">Est. Start:</span>
                                                {{ $rec['estimated_start']->format('g:i A') }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Conflicts Warning --}}
                                    @if ($rec['has_conflicts'])
                                        <div class="mt-2 p-2 bg-red-50 rounded-lg text-sm text-red-700">
                                            ⚠️ Scheduling conflicts detected
                                        </div>
                                    @endif
                                </div>

                                {{-- Assign Button --}}
                                <div class="flex-shrink-0">
                                    <button wire:click="assignTechnician({{ $rec['technician_id'] }})" 
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition {{ $rec['has_conflicts'] ? 'opacity-50' : '' }}"
                                        {{ $rec['has_conflicts'] ? 'disabled' : '' }}>
                                        Assign
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Default Schedule View --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Today's Schedule</h2>
                        <div class="flex items-center gap-2">
                            <button wire:click="selectAllAppointments" class="text-sm text-gray-600 hover:text-gray-900">
                                Select All
                            </button>
                            @if (!empty($selectedAppointments))
                                <span class="text-sm text-gray-400">|</span>
                                <button wire:click="openBulkModal('reschedule')" class="text-sm text-indigo-600 hover:text-indigo-800">
                                    Bulk Reschedule ({{ count($selectedAppointments) }})
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
                    @forelse ($this->todaysAppointments as $appointment)
                        <div class="p-4 hover:bg-gray-50 transition flex items-center gap-4 {{ in_array($appointment->id, $selectedAppointments) ? 'bg-indigo-50' : '' }}"
                            draggable="true"
                            @dragstart="$wire.draggedAppointmentId = {{ $appointment->id }}"
                            @dragend="$wire.draggedAppointmentId = null">
                            
                            <input type="checkbox" 
                                wire:click="toggleAppointmentSelection({{ $appointment->id }})"
                                {{ in_array($appointment->id, $selectedAppointments) ? 'checked' : '' }}
                                class="w-4 h-4 text-indigo-600 rounded border-gray-300">

                            <div class="flex-shrink-0 w-20 text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ $appointment->scheduled_start_at->format('g:i A') }}</div>
                                <div class="text-xs text-gray-500">{{ $appointment->scheduled_end_at->format('g:i A') }}</div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 text-sm">{{ $appointment->workOrder?->subject ?? 'Appointment' }}</h3>
                                <p class="text-xs text-gray-500">{{ $appointment->workOrder?->organization?->name }}</p>
                            </div>

                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $appointment->assignedTo?->name ?? 'Unassigned' }}
                                </span>
                            </div>

                            <div class="flex-shrink-0">
                                <button class="p-1 text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm">No appointments for this date</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
