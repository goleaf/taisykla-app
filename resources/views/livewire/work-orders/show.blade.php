<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('work-orders.index') }}" class="text-sm text-indigo-600" wire:navigate>← Back to Work Orders</a>
                <h1 class="text-2xl font-semibold text-gray-900">Work Order #{{ $workOrder->id }}</h1>
                <p class="text-sm text-gray-500">{{ $workOrder->subject }}</p>
            </div>
            <div class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <p class="text-xs text-gray-500">Organization</p>
                            <p>{{ $workOrder->organization?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Equipment</p>
                            <p>{{ $workOrder->equipment?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Requested By</p>
                            <p>{{ $workOrder->requestedBy?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Assigned To</p>
                            <p>{{ $workOrder->assignedTo?->name ?? 'Unassigned' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Priority</p>
                            <p>{{ ucfirst($workOrder->priority) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Scheduled</p>
                            <p>{{ $workOrder->scheduled_start_at?->format('M d, H:i') ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-gray-700">
                        <p class="text-xs text-gray-500">Description</p>
                        <p>{{ $workOrder->description ?? 'No description provided.' }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Appointments</h2>
                    <div class="space-y-3">
                        @forelse ($workOrder->appointments as $appointment)
                            <div class="text-sm text-gray-700">
                                <p>{{ $appointment->scheduled_start_at?->format('M d, H:i') }} • {{ $appointment->status }}</p>
                                <p class="text-xs text-gray-500">{{ $appointment->assignedTo?->name ?? 'Unassigned' }} {{ $appointment->time_window ? '• '.$appointment->time_window : '' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No appointments scheduled yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Parts Used</h2>
                    <div class="space-y-3">
                        @forelse ($workOrder->parts as $part)
                            <div class="text-sm text-gray-700">
                                <p>{{ $part->part?->name ?? 'Part' }} • Qty {{ $part->quantity }}</p>
                                <p class="text-xs text-gray-500">Unit price: ${{ number_format($part->unit_price, 2) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No parts logged yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline Notes</h2>
                    <div class="space-y-3">
                        @forelse ($workOrder->events as $event)
                            <div>
                                <p class="text-sm text-gray-700">{{ $event->note }}</p>
                                <p class="text-xs text-gray-500">{{ $event->user?->name ?? 'System' }} • {{ $event->created_at?->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No notes yet.</p>
                        @endforelse
                    </div>

                    <form wire:submit.prevent="addNote" class="mt-4">
                        <textarea wire:model="note" class="w-full rounded-md border-gray-300" rows="3" placeholder="Add a note"></textarea>
                        @error('note') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <button class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md">Add Note</button>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>
                    <div class="space-y-3">
                        <select wire:model="status" class="w-full rounded-md border-gray-300">
                            @foreach ($statusOptions as $statusOption)
                                <option value="{{ $statusOption }}">{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                            @endforeach
                        </select>
                        <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="updateStatus">Update Status</button>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Assign Technician</h2>
                    <select wire:model="assignedToUserId" class="w-full rounded-md border-gray-300">
                        <option value="">Unassigned</option>
                        @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                        @endforeach
                    </select>
                    <button class="mt-3 w-full px-4 py-2 border border-gray-300 rounded-md" wire:click="assignTechnician">Save Assignment</button>
                </div>

                @if ($workOrder->feedback)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Feedback</h2>
                        <p class="text-sm text-gray-700">Rating: {{ $workOrder->feedback->rating }}/5</p>
                        <p class="text-sm text-gray-600 mt-2">{{ $workOrder->feedback->comments }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
