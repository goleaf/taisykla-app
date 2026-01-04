{{-- Modals --}}

{{-- Conflict Warning Modal --}}
<div x-data="{ show: false }" @show-conflicts.window="show = true" x-show="show" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div x-show="show" @click="show = false" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>

        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Scheduling Conflicts Detected</h3>
                </div>

                <div class="space-y-3 mb-6 max-h-60 overflow-y-auto">
                    @foreach ($detectedConflicts as $conflict)
                        <div
                            class="p-3 rounded-lg 
                                {{ ($conflict['severity'] ?? 'warning') === 'critical' ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200' }}">
                            <div class="flex items-start gap-2">
                                @if (($conflict['severity'] ?? 'warning') === 'critical')
                                    <svg class="w-4 h-4 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium {{ ($conflict['severity'] ?? 'warning') === 'critical' ? 'text-red-700' : 'text-amber-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $conflict['type'] ?? 'Conflict')) }}
                                    </p>
                                    <p
                                        class="text-sm {{ ($conflict['severity'] ?? 'warning') === 'critical' ? 'text-red-600' : 'text-amber-600' }}">
                                        {{ $conflict['message'] ?? 'Unknown conflict' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <button @click="show = false"
                        class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button @click="show = false; $wire.confirmReschedule()"
                        class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700 transition">
                        Proceed Anyway
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Impact Analysis Modal --}}
<div x-data="{ show: false }" @show-impact-analysis.window="show = true" x-show="show" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <div x-show="show" @click="show = false" class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>

        <div x-show="show"
            class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-xl sm:w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule Change Impact</h3>

                @if (!empty($impactAnalysis))
                    {{-- Proposed Change --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Proposed Change</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">From:</p>
                                <p class="font-medium text-gray-900">
                                    {{ $impactAnalysis['proposed_change']['from']['technician'] ?? '' }}</p>
                                <p class="text-gray-600">{{ $impactAnalysis['proposed_change']['from']['start'] ?? '' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">To:</p>
                                <p class="font-medium text-gray-900">
                                    {{ $impactAnalysis['proposed_change']['to']['technician'] ?? '' }}</p>
                                <p class="text-gray-600">{{ $impactAnalysis['proposed_change']['to']['start'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Impact Summary --}}
                    <div class="space-y-3 mb-4">
                        @if (!empty($impactAnalysis['impact']))
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Original Technician Impact:</span>
                                <span
                                    class="text-gray-900">{{ $impactAnalysis['impact']['original_technician']['utilization_change'] ?? '' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">New Technician Impact:</span>
                                <span
                                    class="text-gray-900">{{ $impactAnalysis['impact']['new_technician']['utilization_change'] ?? '' }}</span>
                            </div>
                        @endif
                        @if (!empty($impactAnalysis['customer_impact']))
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Customer Time Change:</span>
                                <span
                                    class="text-gray-900">{{ $impactAnalysis['customer_impact']['time_difference'] ?? '' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Warnings --}}
                    @if (!empty($impactAnalysis['warnings']))
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                            <p class="text-sm font-medium text-amber-800 mb-1">Warnings</p>
                            @foreach ($impactAnalysis['warnings'] as $warning)
                                <p class="text-sm text-amber-700">• {{ $warning['message'] ?? '' }}</p>
                            @endforeach
                        </div>
                    @endif
                @endif

                <div class="flex gap-3">
                    <button @click="show = false; $wire.cancelReschedule()"
                        class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button @click="show = false; $wire.confirmReschedule()"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                        Confirm Change
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recurring Schedule Modal --}}
@if ($showRecurringModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div wire:click="$set('showRecurringModal', false)" class="fixed inset-0 bg-gray-500/75 transition-opacity">
            </div>

            <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Create Recurring Schedule</h3>
                        <button wire:click="$set('showRecurringModal', false)" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        {{-- Frequency --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select wire:model.live="recurringFormData.frequency"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Every 2 Weeks</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        {{-- Days of Week (for weekly) --}}
                        @if (in_array($recurringFormData['frequency'], ['weekly', 'custom']))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Days</label>
                                <div class="flex gap-2">
                                    @foreach (['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7] as $label => $day)
                                                    <button type="button"
                                                        wire:click="$set('recurringFormData.days_of_week', {{ in_array($day, $recurringFormData['days_of_week'])
                                        ? json_encode(array_values(array_diff($recurringFormData['days_of_week'], [$day])))
                                        : json_encode(array_merge($recurringFormData['days_of_week'], [$day])) }})" class="w-10 h-10 rounded-lg text-sm font-medium transition
                                                                        {{ in_array($day, $recurringFormData['days_of_week'])
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                                        {{ $label }}
                                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Start Date/Time --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                            <input type="datetime-local" wire:model.live="recurringFormData.starts_at"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Duration --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                            <input type="number" wire:model.live="recurringFormData.duration_minutes"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="15" step="15">
                        </div>

                        {{-- End Condition --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date (optional)</label>
                                <input type="date" wire:model.live="recurringFormData.ends_at"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Or # Occurrences</label>
                                <input type="number" wire:model.live="recurringFormData.occurrence_count"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1">
                            </div>
                        </div>

                        {{-- Preview --}}
                        <div>
                            <button type="button" wire:click="previewRecurringSchedule"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                Preview Occurrences
                            </button>
                            @if (!empty($recurringPreview))
                                <div class="mt-2 bg-gray-50 rounded-lg p-3 max-h-32 overflow-y-auto">
                                    @foreach ($recurringPreview as $occ)
                                        <div class="text-sm text-gray-600 py-1">
                                            {{ $occ['formatted_date'] }} • {{ $occ['start_time'] }} - {{ $occ['end_time'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="$set('showRecurringModal', false)"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">
                            Cancel
                        </button>
                        <button wire:click="createRecurringSchedule"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                            Create Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Bulk Reschedule Modal --}}
@if ($showBulkModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ 
                newDate: '{{ now()->format('Y-m-d') }}',
                newTime: '09:00',
                newTechnicianId: null
            }">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div wire:click="$set('showBulkModal', false)" class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-md sm:w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Bulk {{ ucfirst($bulkAction) }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        {{ count($selectedAppointments) }} appointment(s) selected
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Date</label>
                            <input type="date" x-model="newDate"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Time</label>
                            <input type="time" x-model="newTime"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reassign to (optional)</label>
                            <select x-model="newTechnicianId"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Keep current assignment</option>
                                @foreach ($this->technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="$set('showBulkModal', false)"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">
                            Cancel
                        </button>
                        <button
                            @click="$wire.executeBulkAction({ new_date: newDate, new_time: newTime, new_technician_id: newTechnicianId })"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                            Apply Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Emergency Insert Modal --}}
@if ($showEmergencyModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div wire:click="$set('showEmergencyModal', false)" class="fixed inset-0 bg-gray-500/75 transition-opacity">
            </div>

            <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Emergency Insertion</h3>
                    </div>

                    <p class="text-sm text-gray-500 mb-4">
                        Insert this job as an emergency. Lower priority appointments may be bumped.
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Technician</label>
                            <select wire:model.live="emergencyTechnicianId"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select technician</option>
                                @foreach ($this->technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Time</label>
                            <input type="datetime-local" wire:model.live="emergencyTime"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        @if (!empty($emergencyBumpOptions))
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <p class="text-sm font-medium text-amber-800 mb-2">Appointments to be affected:</p>
                                @foreach ($emergencyBumpOptions as $option)
                                    <div class="text-sm text-amber-700 py-1">
                                        • {{ $option['work_order']?->subject ?? 'Appointment' }}
                                        ({{ $option['can_bump'] ? 'Can bump' : 'Cannot bump - ' . ($option['reason'] ?? '') }})
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="$set('showEmergencyModal', false)"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">
                            Cancel
                        </button>
                        <button wire:click="previewEmergencyInsert"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                            Insert Emergency
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif