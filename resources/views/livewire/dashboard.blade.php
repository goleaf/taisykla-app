<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-900">Welcome back, {{ $user->name }}</h1>
            <p class="text-sm text-gray-500">Role: {{ ucfirst($role) }} • {{ now()->toDayDateTimeString() }}</p>
        </div>

        @if ($user->hasAnyRole(['technician', 'dispatch']))
            @php
                $availability = $user->availability_status ?? 'available';
                $availabilityColor = match ($availability) {
                    'available' => 'text-green-600',
                    'unavailable' => 'text-yellow-600',
                    'offline' => 'text-gray-500',
                    default => 'text-gray-500',
                };
            @endphp
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Availability</p>
                    <p class="text-lg font-semibold {{ $availabilityColor }}">{{ ucfirst($availability) }}</p>
                    <p class="text-xs text-gray-500">
                        Updated {{ $user->availability_updated_at?->diffForHumans() ?? 'just now' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button class="px-3 py-1 border border-gray-300 rounded-md text-sm" wire:click="updateAvailability('available')">Available</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-md text-sm" wire:click="updateAvailability('unavailable')">Unavailable</button>
                    <button class="px-3 py-1 border border-gray-300 rounded-md text-sm" wire:click="updateAvailability('offline')">Offline</button>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
            @foreach ($metrics as $label => $value)
                <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $label)) }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @if ($workOrders->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Work Orders</h2>
                    <div class="space-y-3">
                        @foreach ($workOrders as $order)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">#{{ $order->id }} {{ $order->subject }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->status }} • {{ $order->priority }}</p>
                                </div>
                                <a class="text-sm text-indigo-600" href="{{ route('work-orders.show', $order) }}" wire:navigate>View</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($appointments->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Appointments</h2>
                    <div class="space-y-3">
                        @foreach ($appointments as $appointment)
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ optional($appointment->workOrder)->subject ?? 'Work Order' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ optional($appointment->scheduled_start_at)->format('M d, H:i') }}
                                    @if ($appointment->assignedTo)
                                        • {{ $appointment->assignedTo->name }}
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($tickets->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Open Support Tickets</h2>
                    <div class="space-y-3">
                        @foreach ($tickets as $ticket)
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $ticket->subject }}</p>
                                <p class="text-xs text-gray-500">{{ $ticket->status }} • {{ $ticket->priority }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($equipment->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Equipment</h2>
                    <div class="space-y-3">
                        @foreach ($equipment as $item)
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->type }} • {{ $item->status }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($invoices->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Invoices</h2>
                    <div class="space-y-3">
                        @foreach ($invoices as $invoice)
                            <div>
                                <p class="text-sm font-medium text-gray-900">Invoice #{{ $invoice->id }}</p>
                                <p class="text-xs text-gray-500">{{ $invoice->status }} • ${{ number_format($invoice->total, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($threads->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Messages</h2>
                    <div class="space-y-3">
                        @foreach ($threads as $thread)
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $thread->subject ?? 'Conversation' }}</p>
                                <p class="text-xs text-gray-500">Updated {{ $thread->updated_at?->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
