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

        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Current Status</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $statusSummary['title'] ?? 'Status update' }}</p>
                    <p class="text-sm text-gray-600">{{ $statusSummary['description'] ?? '' }}</p>
                </div>
                <div class="text-xs text-gray-500">
                    Updated {{ $workOrder->updated_at?->diffForHumans() }}
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    @foreach ($statusSteps as $step)
                        <span>{{ $step['label'] }}</span>
                    @endforeach
                </div>
                <div class="mt-2 flex items-center">
                    @foreach ($statusSteps as $step)
                        @php
                            $circleColor = match ($step['state']) {
                                'complete' => 'bg-green-500',
                                'current' => 'bg-indigo-600',
                                default => 'bg-gray-300',
                            };
                            $lineColor = match ($step['state']) {
                                'complete' => 'bg-green-400',
                                'current' => 'bg-indigo-300',
                                default => 'bg-gray-200',
                            };
                        @endphp
                        <div class="flex items-center">
                            <div class="h-3 w-3 rounded-full {{ $circleColor }}"></div>
                            @if (! $loop->last)
                                <div class="h-0.5 w-10 md:w-16 {{ $lineColor }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if ($workOrder->status === 'on_hold')
                    <p class="mt-3 text-sm text-yellow-700">On hold: {{ $workOrder->on_hold_reason ?? 'Awaiting next steps.' }}</p>
                @endif
                @if ($workOrder->status === 'canceled')
                    <p class="mt-3 text-sm text-red-600">This request has been canceled.</p>
                @endif
            </div>
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
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Report</h2>
                    @if (in_array($workOrder->status, ['completed', 'closed'], true))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Arrived</p>
                                <p>{{ $serviceMetrics['arrived_at']?->format('M d, H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Started</p>
                                <p>{{ $serviceMetrics['started_at']?->format('M d, H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Completed</p>
                                <p>{{ $serviceMetrics['completed_at']?->format('M d, H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Time</p>
                                <p>{{ $serviceMetrics['duration_minutes'] ? $serviceMetrics['duration_minutes'].' minutes' : '—' }}</p>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-700">
                            <p class="text-xs text-gray-500">Parts Used</p>
                            <div class="space-y-2 mt-2">
                                @forelse ($workOrder->parts as $part)
                                    <div>
                                        <p>{{ $part->part?->name ?? 'Part' }} • Qty {{ $part->quantity }}</p>
                                        <p class="text-xs text-gray-500">Unit price: ${{ number_format($part->unit_price, 2) }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No parts logged.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-700">
                            <p class="text-xs text-gray-500">Technician Notes</p>
                            <div class="space-y-2 mt-2">
                                @forelse ($workOrder->events->where('type', 'note') as $event)
                                    <div>
                                        <p>{{ $event->note }}</p>
                                        <p class="text-xs text-gray-500">{{ $event->user?->name ?? 'System' }} • {{ $event->created_at?->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No technician notes available.</p>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">The service report will appear once the work is completed.</p>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline & Updates</h2>
                    <div class="space-y-3">
                        @forelse ($timeline as $entry)
                            <div>
                                <p class="text-sm text-gray-700">{{ $entry['summary'] }}</p>
                                <p class="text-xs text-gray-500">{{ $entry['actor'] }} • {{ $entry['timestamp']?->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No updates yet.</p>
                        @endforelse
                    </div>

                    @if ($viewer && $viewer->hasAnyRole(['admin', 'dispatch', 'technician']))
                        <form wire:submit.prevent="addNote" class="mt-4">
                            <textarea wire:model="note" class="w-full rounded-md border-gray-300" rows="3" placeholder="Add a note"></textarea>
                            @error('note') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <button class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md">Add Note</button>
                        </form>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Messages</h2>
                    @if ($messageThread && $messageThread->messages->isNotEmpty())
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            @foreach ($messageThread->messages as $message)
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $message->user?->name ?? 'User' }}</p>
                                    <p class="text-sm text-gray-700">{{ $message->body }}</p>
                                    <p class="text-xs text-gray-400">{{ $message->created_at?->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No messages yet. Send a note to your technician or dispatcher.</p>
                    @endif
                    <form wire:submit.prevent="sendMessage" class="mt-4">
                        <textarea wire:model="messageBody" class="w-full rounded-md border-gray-300" rows="2" placeholder="Type a message"></textarea>
                        @error('messageBody') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <button class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md">Send Message</button>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Assigned Technician</h2>
                    @if ($workOrder->assignedTo)
                        <p class="text-sm font-medium text-gray-900">{{ $workOrder->assignedTo->name }}</p>
                        <p class="text-xs text-gray-500">{{ $workOrder->assignedTo->job_title ?? 'Technician' }}</p>
                        <div class="mt-3 space-y-1 text-sm text-gray-700">
                            <p>Phone: {{ $workOrder->assignedTo->phone ?? '—' }}</p>
                            <p>Email: {{ $workOrder->assignedTo->email }}</p>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Availability: {{ ucfirst($workOrder->assignedTo->availability_status ?? 'unknown') }}
                        </p>
                    @else
                        <p class="text-sm text-gray-500">No technician assigned yet.</p>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Next Appointment</h2>
                    @if ($nextAppointment)
                        <p class="text-sm text-gray-700">
                            {{ $nextAppointment->scheduled_start_at?->format('M d, H:i') ?? '—' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ ucfirst($nextAppointment->status) }}{{ $nextAppointment->time_window ? ' • '.$nextAppointment->time_window : '' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $nextAppointment->assignedTo?->name ?? 'Unassigned' }}
                        </p>
                    @else
                        <p class="text-sm text-gray-500">No appointment scheduled yet.</p>
                    @endif
                </div>

                @if ($viewer && $viewer->hasAnyRole(['admin', 'dispatch', 'technician']))
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Actions</h2>
                        <div class="space-y-3">
                            <select wire:model="status" class="w-full rounded-md border-gray-300">
                                @foreach ($statusOptions as $statusOption)
                                    <option value="{{ $statusOption }}">{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                                @endforeach
                            </select>
                            <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="updateStatus">Update Status</button>
                            @if ($viewer->hasAnyRole(['technician', 'dispatch']))
                                <button class="w-full px-4 py-2 border border-gray-300 rounded-md" wire:click="markArrived" @disabled($workOrder->arrived_at)>
                                    {{ $workOrder->arrived_at ? 'Arrived Recorded' : 'Mark Arrived' }}
                                </button>
                                <p class="text-xs text-gray-500">
                                    Arrival: {{ $workOrder->arrived_at?->format('M d, H:i') ?? 'Not recorded' }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($viewer && $viewer->hasAnyRole(['admin', 'dispatch']))
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
                @endif

                @if ($viewer && ($viewer->hasRole('client') || $workOrder->requested_by_user_id === $viewer->id))
                    @if (in_array($workOrder->status, ['completed', 'closed'], true))
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Sign-Off</h2>
                            @if ($workOrder->customer_signature_at)
                                <p class="text-sm text-gray-700">Signed by {{ $workOrder->customer_signature_name ?? 'Customer' }}</p>
                                <p class="text-xs text-gray-500">Signed {{ $workOrder->customer_signature_at?->format('M d, H:i') }}</p>
                            @else
                                <form wire:submit.prevent="submitSignoff" class="space-y-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Signature Name</label>
                                        <input wire:model="signatureName" class="mt-1 w-full rounded-md border-gray-300" placeholder="Enter your name" />
                                        @error('signatureName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md">Approve Work</button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endif

                @if ($workOrder->feedback)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Feedback</h2>
                        <p class="text-sm text-gray-700">Rating: {{ $workOrder->feedback->rating }}/5</p>
                        <p class="text-sm text-gray-600 mt-2">{{ $workOrder->feedback->comments }}</p>
                    </div>
                @elseif ($viewer && ($viewer->hasRole('client') || $workOrder->requested_by_user_id === $viewer->id))
                    @if (in_array($workOrder->status, ['completed', 'closed'], true))
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Leave Feedback</h2>
                            <form wire:submit.prevent="submitFeedback" class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-500">Rating</label>
                                    <select wire:model="feedbackRating" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="0">Select rating</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                    @error('feedbackRating') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Comments</label>
                                    <textarea wire:model="feedbackComments" class="mt-1 w-full rounded-md border-gray-300" rows="3" placeholder="Share your experience"></textarea>
                                    @error('feedbackComments') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md">Submit Feedback</button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
