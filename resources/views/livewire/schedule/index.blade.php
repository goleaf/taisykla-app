<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Schedule</h1>
                <p class="text-sm text-gray-500">Plan technician appointments and travel windows.</p>
            </div>
            @if ($user->hasAnyRole(['admin', 'dispatch']))
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="$toggle('showCreate')">New Appointment</button>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs text-gray-500">Date</label>
                    <input type="date" wire:model="dateFilter" class="mt-1 w-full rounded-md border-gray-300" />
                </div>
                <div>
                    <label class="text-xs text-gray-500">Technician</label>
                    <select wire:model="technicianFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All</option>
                        @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if ($showCreate && $user->hasAnyRole(['admin', 'dispatch']))
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Schedule Appointment</h2>
                <form wire:submit.prevent="createAppointment" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Work Order</label>
                        <select wire:model="new.work_order_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select work order</option>
                            @foreach ($workOrders as $order)
                                <option value="{{ $order->id }}">#{{ $order->id }} {{ $order->subject }}</option>
                            @endforeach
                        </select>
                        @error('new.work_order_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Technician</label>
                        <select wire:model="new.assigned_to_user_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Unassigned</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Time Window</label>
                        <input wire:model="new.time_window" class="mt-1 w-full rounded-md border-gray-300" placeholder="Morning" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Start</label>
                        <input type="datetime-local" wire:model="new.scheduled_start_at" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">End</label>
                        <input type="datetime-local" wire:model="new.scheduled_end_at" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Notes</label>
                        <textarea wire:model="new.notes" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-3">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Schedule</button>
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="resetNew">Reset</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Work Order</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Technician</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time Window</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">#{{ $appointment->workOrder?->id }} {{ $appointment->workOrder?->subject }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $appointment->assignedTo?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $appointment->scheduled_start_at?->format('M d, H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($appointment->status) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $appointment->time_window ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">
                {{ $appointments->links() }}
            </div>
        </div>
    </div>
</div>
