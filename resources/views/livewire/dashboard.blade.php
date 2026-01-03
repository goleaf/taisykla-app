@php
    use App\Support\RoleCatalog;
@endphp

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Welcome back, {{ $user->name }}</h1>
                <p class="text-sm text-gray-500">Role: {{ $roleLabel }} • {{ $todayLabel }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
            <p class="text-xs uppercase tracking-wide text-gray-500">Profile Overview</p>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div class="space-y-1">
                    <p>Name: {{ $user->name }}</p>
                    <p>Role: {{ $roles ? collect($roles)->map(fn ($role) => RoleCatalog::label($role))->implode(', ') : '—' }}</p>
                    <p>Department: {{ $user->department ?? '—' }}</p>
                    <p>Employee ID: {{ $user->employee_id ?? '—' }}</p>
                </div>
                <div class="space-y-1">
                    <p>Email: {{ $user->email }}</p>
                    <p>Phone: {{ $user->phone ?? '—' }}</p>
                    <p>Job Title: {{ $user->job_title ?? '—' }}</p>
                    <p>Permissions: {{ $permissionCount }}</p>
                </div>
            </div>
        </div>

        @if ($availability['show'] ?? false)
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Availability</p>
                    <p class="text-lg font-semibold {{ $availability['color'] ?? 'text-gray-500' }}">{{ $availability['label'] ?? 'Offline' }}</p>
                    <p class="text-xs text-gray-500">Updated {{ $availability['updated'] ?? 'just now' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($availabilityOptions as $value => $label)
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm" wire:click="updateAvailability('{{ $value }}')">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($summaryCards as $card)
                <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $card['value'] }}</p>
                    @if (! empty($card['subtext']))
                        <p class="text-xs text-gray-500">{{ $card['subtext'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($roleKey === 'technician' && $technicianData)
            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Today's Work Queue</h2>
                        <a class="text-sm text-indigo-600" href="{{ route('schedule.index') }}" wire:navigate>View schedule</a>
                    </div>
                    <div class="space-y-4">
                        @forelse ($technicianData['appointments'] as $appointment)
                            @php
                                $order = $appointment->workOrder;
                                $priorityClass = match ($order?->priority) {
                                    'urgent' => 'bg-red-100 text-red-700',
                                    'high' => 'bg-orange-100 text-orange-700',
                                    default => 'bg-blue-100 text-blue-700',
                                };
                                $estimatedMinutes = $appointment->scheduled_start_at && $appointment->scheduled_end_at
                                    ? $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at)
                                    : ($order?->estimated_minutes ?? $order?->labor_minutes);
                            @endphp
                            <details class="border border-gray-100 rounded-lg p-4">
                                <summary class="cursor-pointer">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $order?->organization?->name ?? $order?->requestedBy?->name ?? 'Customer' }}</p>
                                            <p class="text-xs text-gray-500">{{ $order?->location_address ?? 'No address provided' }}</p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            <span>{{ $appointment->scheduled_start_at?->format('H:i') ?? 'TBD' }} {{ $appointment->time_window ? '• '.$appointment->time_window : '' }}</span>
                                            <span>{{ $order?->category?->name ?? 'Service' }}</span>
                                            <span>{{ $estimatedMinutes ? $estimatedMinutes.' min' : '—' }}</span>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 {{ $priorityClass }}">{{ ucfirst($order?->priority ?? 'standard') }}</span>
                                        </div>
                                    </div>
                                </summary>
                                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4 text-sm text-gray-700">
                                    <div class="space-y-2">
                                        <p class="text-xs uppercase text-gray-500">Customer Details</p>
                                        <p>Contact: {{ $order?->organization?->primary_contact_name ?? $order?->requestedBy?->name ?? '—' }}</p>
                                        <p>Email: {{ $order?->organization?->primary_contact_email ?? $order?->requestedBy?->email ?? '—' }}</p>
                                        <p>Phone: {{ $order?->organization?->primary_contact_phone ?? $order?->requestedBy?->phone ?? '—' }}</p>
                                        <p>Location notes: {{ $appointment->notes ?? $order?->location_name ?? '—' }}</p>
                                        <p>Description: {{ $order?->description ?? 'No description.' }}</p>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-xs uppercase text-gray-500">Equipment</p>
                                        <p>{{ $order?->equipment?->name ?? '—' }} {{ $order?->equipment?->model ? '• '.$order->equipment->model : '' }}</p>
                                        <p>Serial: {{ $order?->equipment?->serial_number ?? '—' }}</p>
                                        <p>Location: {{ $order?->equipment?->location_name ?? '—' }}</p>
                                        <p class="text-xs uppercase text-gray-500 mt-3">Parts to Bring</p>
                                        @if ($order?->parts && $order->parts->isNotEmpty())
                                            <ul class="text-sm text-gray-700">
                                                @foreach ($order->parts as $part)
                                                    <li>{{ $part->part?->name ?? 'Part' }} • Qty {{ $part->quantity }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-sm text-gray-500">No parts assigned yet.</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-xs uppercase text-gray-500">Customer Photos</p>
                                    @if ($order?->attachments && $order->attachments->isNotEmpty())
                                        <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2">
                                            @foreach ($order->attachments->take(4) as $attachment)
                                                <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" rel="noreferrer">
                                                    <img class="h-20 w-full rounded-md object-cover border border-gray-200" src="{{ asset('storage/'.$attachment->file_path) }}" alt="{{ $attachment->label ?? 'Attachment' }}" />
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">No photos uploaded.</p>
                                    @endif
                                </div>
                                <div class="mt-4">
                                    <a class="text-sm text-indigo-600" href="{{ $order ? route('work-orders.show', $order) : '#' }}" wire:navigate>Open work order</a>
                                </div>
                            </details>
                        @empty
                            <p class="text-sm text-gray-500">No appointments scheduled for today.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Intelligent Route Planning</h2>
                        <div class="space-y-3">
                            @forelse ($technicianData['routeStops'] as $stop)
                                <div class="flex items-start justify-between gap-3 text-sm text-gray-700">
                                    <div>
                                        <p class="font-medium text-gray-900">Stop {{ $stop['sequence'] }} • {{ $stop['label'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $stop['address'] ?? 'No address' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $stop['time'] ? 'ETA window: '.$stop['time'] : 'ETA TBD' }}
                                            {{ $stop['travel_minutes'] ? '• Travel '.$stop['travel_minutes'].' min' : '' }}
                                        </p>
                                    </div>
                                    @if ($stop['map_url'])
                                        <a class="text-xs text-indigo-600" href="{{ $stop['map_url'] }}" target="_blank" rel="noreferrer">Open map</a>
                                    @else
                                        <span class="text-xs text-gray-400">No coords</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Route planning will appear once stops are scheduled with locations.</p>
                            @endforelse
                        </div>
                        <p class="mt-4 text-xs text-gray-500">Reorder stops from the schedule view if you need to optimize today’s route.</p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Time Tracking Summary</h2>
                        <div class="space-y-3 text-sm text-gray-700">
                            @if ($technicianData['timeSummary']['current'])
                                <div class="rounded-md bg-indigo-50 p-3">
                                    <p class="text-xs uppercase text-indigo-600">Active Job</p>
                                    <p class="font-medium text-indigo-900">{{ $technicianData['timeSummary']['current']['subject'] }}</p>
                                    <p class="text-xs text-indigo-700">
                                        Started {{ $technicianData['timeSummary']['current']['started_at']->format('H:i') }}
                                        • {{ $technicianData['timeSummary']['current']['elapsed_minutes'] }} min elapsed
                                        {{ $technicianData['timeSummary']['current']['estimated_minutes'] ? '• Est '.$technicianData['timeSummary']['current']['estimated_minutes'].' min' : '' }}
                                    </p>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No active job right now.</p>
                            @endif
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Work Time</p>
                                    <p class="text-base font-semibold text-gray-900">{{ $technicianData['timeSummary']['labor_minutes'] }} min</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Travel Time</p>
                                    <p class="text-base font-semibold text-gray-900">{{ $technicianData['timeSummary']['travel_minutes'] }} min</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Break Time</p>
                                    <p class="text-base font-semibold text-gray-900">{{ $technicianData['timeSummary']['break_minutes'] !== null ? $technicianData['timeSummary']['break_minutes'].' min' : 'Not tracked' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Billable</p>
                                    <p class="text-base font-semibold text-gray-900">{{ $technicianData['timeSummary']['billable_minutes'] }} min</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Communication Center</h2>
                        <div class="space-y-3">
                            @forelse ($technicianData['messages'] as $thread)
                                @php
                                    $lastMessage = $thread->messages->first();
                                @endphp
                                <div class="border border-gray-100 rounded-lg p-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <p class="font-medium text-gray-900">{{ $thread->subject ?: 'Conversation' }}</p>
                                        @if ($thread->is_unread)
                                            <span class="text-xs text-red-600">Unread</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        {{ $thread->workOrder ? 'WO #'.$thread->workOrder->id.' • ' : '' }}
                                        {{ $lastMessage?->user?->name ?? 'System' }} • {{ $lastMessage?->created_at?->diffForHumans() ?? '—' }}
                                    </p>
                                    <p class="text-sm text-gray-700 mt-2">{{ $lastMessage?->body ?? 'No messages yet.' }}</p>
                                    <form wire:submit.prevent="sendQuickReply({{ $thread->id }})" class="mt-3">
                                        <textarea wire:model.defer="quickReplies.{{ $thread->id }}" class="w-full rounded-md border-gray-300 text-sm" rows="2" placeholder="Quick reply"></textarea>
                                        @error('quickReplies.'.$thread->id) <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                        <div class="mt-2 flex items-center justify-between">
                                            <a class="text-xs text-indigo-600" href="{{ route('messages.index') }}" wire:navigate>Open thread</a>
                                            <button class="px-3 py-1 text-xs bg-indigo-600 text-white rounded-md">Send</button>
                                        </div>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No recent messages.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Parts & Inventory Quick Access</h2>
                        <div class="space-y-4">
                            @error('reservePart')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div>
                                <p class="text-xs uppercase text-gray-500">Parts Needed Today</p>
                                @if ($technicianData['parts']['needed'])
                                    <ul class="mt-2 text-sm text-gray-700 space-y-1">
                                        @foreach ($technicianData['parts']['needed'] as $part)
                                            <li class="flex items-center justify-between gap-2">
                                                <span>
                                                    {{ $part['name'] }}{{ $part['sku'] ? ' • '.$part['sku'] : '' }} • Qty {{ $part['quantity'] }}
                                                    <span class="text-xs text-gray-500">• Available {{ $part['available'] }}</span>
                                                </span>
                                                @if ($part['part_id'])
                                                    <button
                                                        type="button"
                                                        class="px-2 py-1 text-xs border border-gray-300 rounded-md"
                                                        wire:click="reservePart({{ $part['part_id'] }}, {{ $part['quantity'] }})"
                                                        @disabled($part['available'] < 1)
                                                    >
                                                        Reserve
                                                    </button>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-gray-500 mt-2">No parts assigned for today’s jobs.</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs uppercase text-gray-500">Commonly Used Parts</p>
                                @if ($technicianData['parts']['common'])
                                    <div class="mt-2 space-y-2 text-sm text-gray-700">
                                        @foreach ($technicianData['parts']['common'] as $part)
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $part['name'] }}</p>
                                                    <p class="text-xs text-gray-500">Usage: {{ $part['usage'] }} • SKU: {{ $part['sku'] ?? '—' }}</p>
                                                </div>
                                                <div class="text-xs text-gray-500 text-right">
                                                    <p>Available: {{ $part['available'] }}</p>
                                                    @if ($part['reorder_level'])
                                                        <p>Reorder @ {{ $part['reorder_level'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 mt-2">No usage data available yet.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($roleKey === 'dispatch' && $dispatchData)
            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Work Request Queue</h2>
                        <a class="text-sm text-indigo-600" href="{{ route('work-orders.index') }}" wire:navigate>Manage queue</a>
                    </div>
                    <div class="space-y-3">
                        @forelse ($dispatchData['queue'] as $item)
                            @php
                                $order = $item['order'];
                                $priorityClass = match ($order->priority) {
                                    'urgent' => 'bg-red-100 text-red-700',
                                    'high' => 'bg-orange-100 text-orange-700',
                                    default => 'bg-blue-100 text-blue-700',
                                };
                            @endphp
                            <div class="border border-gray-100 rounded-lg p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $order->organization?->name ?? 'Customer' }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->category?->name ?? 'Service' }} • Requested {{ $order->requested_at?->diffForHumans() ?? $order->created_at?->diffForHumans() }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <span>{{ $order->time_window ?? 'No window' }}</span>
                                        <span>SLA: {{ $item['sla_minutes'] ? $item['sla_minutes'].' min' : '—' }}</span>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 {{ $priorityClass }}">{{ ucfirst($order->priority) }}</span>
                                        <span class="text-gray-700 font-semibold">Score {{ $item['priority_score'] }}</span>
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    Waiting {{ $item['waiting_minutes'] }} min • Customer history: {{ $item['history_count'] }} requests
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No unassigned requests right now.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 lg:col-span-2">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Live Technician Status Board</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($dispatchData['technicians'] as $tech)
                                <div class="border border-gray-100 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-gray-900">{{ $tech['user']->name }}</p>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $tech['status']['class'] }}">{{ $tech['status']['label'] }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Jobs today: {{ $tech['appointments']->count() }} • Utilization {{ $tech['utilization'] }}%</p>
                                    <p class="text-xs text-gray-500">
                                        Avg duration: {{ $tech['avg_actual'] ? $tech['avg_actual'].' min' : '—' }}
                                        {{ $tech['avg_estimated'] ? ' vs est '.$tech['avg_estimated'].' min' : '' }}
                                    </p>
                                    <div class="mt-2 text-xs text-gray-500">
                                        @if ($tech['map_url'])
                                            <a class="text-indigo-600" href="{{ $tech['map_url'] }}" target="_blank" rel="noreferrer">
                                                Location: {{ $tech['user']->current_latitude }}, {{ $tech['user']->current_longitude }}
                                            </a>
                                        @else
                                            Location unavailable
                                        @endif
                                    </div>
                                    @if ($tech['has_overdue'])
                                        <p class="mt-2 text-xs text-red-600">Running behind schedule</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h2>
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="flex items-center justify-between">
                                <span>Completed today</span>
                                <span class="font-semibold">{{ $dispatchData['metrics']['completed_today'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>In progress</span>
                                <span class="font-semibold">{{ $dispatchData['metrics']['in_progress'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Avg response time</span>
                                <span class="font-semibold">{{ $dispatchData['metrics']['avg_response_minutes'] !== null ? $dispatchData['metrics']['avg_response_minutes'].' min' : '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Avg completion time</span>
                                <span class="font-semibold">{{ $dispatchData['metrics']['avg_completion_minutes'] !== null ? $dispatchData['metrics']['avg_completion_minutes'].' min' : '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Customer satisfaction</span>
                                <span class="font-semibold">{{ $dispatchData['metrics']['satisfaction'] !== null ? $dispatchData['metrics']['satisfaction'].' / 5' : '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Utilization</span>
                                <span class="font-semibold">{{ $dispatchData['metrics']['utilization'] }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Geographic Heat Map</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            @forelse ($dispatchData['heatMap'] as $zone)
                                <div class="flex items-center justify-between">
                                    <span>{{ $zone['label'] }}</span>
                                    <span class="font-semibold">{{ $zone['count'] }} requests</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No demand clusters detected.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Alert & Exception Panel</h2>
                        <div class="space-y-3">
                            @foreach ($dispatchData['alerts'] as $alert)
                                @php
                                    $alertClass = match ($alert['severity']) {
                                        'high' => 'bg-red-50 text-red-700',
                                        'medium' => 'bg-yellow-50 text-yellow-700',
                                        default => 'bg-green-50 text-green-700',
                                    };
                                @endphp
                                <div class="rounded-md p-3 {{ $alertClass }}">
                                    <p class="text-sm font-semibold">{{ $alert['label'] }}</p>
                                    <p class="text-xs">{{ $alert['detail'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Schedule Calendar View</h2>
                    <div class="space-y-3 text-sm text-gray-700">
                        @foreach ($dispatchData['technicians'] as $tech)
                            <div class="border border-gray-100 rounded-lg p-3">
                                <p class="font-medium text-gray-900">{{ $tech['user']->name }}</p>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600">
                                    @forelse ($tech['appointments'] as $appointment)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 bg-gray-100 text-gray-700">
                                            {{ $appointment->scheduled_start_at?->format('H:i') ?? 'TBD' }}-{{ $appointment->scheduled_end_at?->format('H:i') ?? '—' }}
                                            • {{ $appointment->workOrder?->subject ?? 'Work order' }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-500">No scheduled jobs.</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($roleKey === 'admin' && $adminData)
            <div class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 lg:col-span-2">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">System Health Monitoring</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Uptime</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $adminData['systemHealth']['uptime'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">DB Response</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $adminData['systemHealth']['db_ms'] !== null ? $adminData['systemHealth']['db_ms'].' ms' : 'Unavailable' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Storage Used</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $adminData['systemHealth']['storage']['percent'] !== null ? $adminData['systemHealth']['storage']['percent'].'%' : 'Unavailable' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Queue Backlog</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $adminData['systemHealth']['queue_backlog'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4 text-xs text-gray-500">
                            Failed jobs: {{ $adminData['systemHealth']['failed_jobs'] }} • Active sessions: {{ $adminData['systemHealth']['sessions'] }}
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Security Status</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p>MFA enabled users: {{ $adminData['mfaEnabled'] }}</p>
                            <p>Password resets (7d): {{ $adminData['passwordResets'] }}</p>
                            <p>Failed jobs: {{ $adminData['systemHealth']['failed_jobs'] }}</p>
                            <p class="text-xs text-gray-500">Review audit logs for unusual access patterns.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">User Management Summary</h2>
                        <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                            @foreach ($adminData['roleCounts'] as $role => $count)
                                <div class="rounded-md bg-gray-50 p-3">
                                    <p class="text-xs uppercase text-gray-500">{{ RoleCatalog::label($role) }}</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $count }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-xs text-gray-500">
                            Inactive users: {{ $adminData['inactiveUsers'] }} • Stale accounts: {{ $adminData['staleUsers'] }}
                        </div>
                        <div class="mt-4">
                            <p class="text-xs uppercase text-gray-500 mb-2">Recent Accounts</p>
                            <div class="space-y-2 text-sm text-gray-700">
                                @foreach ($adminData['recentUsers'] as $recentUser)
                                    <div class="flex items-center justify-between">
                                        <span>{{ $recentUser->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $recentUser->created_at?->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Business Intelligence Overview</h2>
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="flex items-center justify-between">
                                <span>Revenue this month</span>
                                <span class="font-semibold">${{ number_format($adminData['business']['revenue_month'], 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Jobs (7d)</span>
                                <span class="font-semibold">{{ $adminData['business']['jobs_week'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Jobs (prior 7d)</span>
                                <span class="font-semibold">{{ $adminData['business']['jobs_week_prior'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>New customers</span>
                                <span class="font-semibold">{{ $adminData['business']['new_customers'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Inactive customers</span>
                                <span class="font-semibold">{{ $adminData['business']['inactive_customers'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Avg revenue/job</span>
                                <span class="font-semibold">${{ number_format($adminData['business']['avg_revenue_per_job'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Compliance & Audit</h2>
                        <div class="space-y-2 text-sm text-gray-700">
                            <p>Backup status: {{ $adminData['compliance']['backup_last_run'] ?? 'Not recorded' }}</p>
                            <p>Audit events (24h): {{ $adminData['compliance']['audit_events'] }}</p>
                            <p>MFA coverage: {{ $adminData['compliance']['user_count'] ? round(($adminData['compliance']['mfa_coverage'] / $adminData['compliance']['user_count']) * 100) : 0 }}%</p>
                            <p>Security patch status: Manual review required</p>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">System Configuration Quick Access</h2>
                        <div class="space-y-2 text-sm">
                            @foreach ($adminData['quickLinks'] as $link)
                                <a class="block text-indigo-600" href="{{ $link['route'] }}" wire:navigate>{{ $link['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach ($sections as $section)
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">{{ $section['title'] }}</h2>
                        @if (! empty($section['action']))
                            <a class="text-sm text-indigo-600" href="{{ $section['action']['href'] }}" wire:navigate>
                                {{ $section['action']['label'] }}
                            </a>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @forelse ($section['items'] as $item)
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900">{{ $item['title'] }}</p>
                                        @if (! empty($item['badges']))
                                            @foreach ($item['badges'] as $badge)
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $badge['class'] }}">
                                                    {{ $badge['label'] }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $item['meta'] }}</p>
                                </div>
                                @if (! empty($item['href']))
                                    <a class="text-xs text-indigo-600" href="{{ $item['href'] }}" wire:navigate>View</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ $section['empty'] }}</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
