@php
    use App\Support\RoleCatalog;
@endphp

<div class="py-8 relative">
    <div wire:loading.delay class="absolute top-0 left-0 right-0 h-1 bg-indigo-600 z-50"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Welcome back, {{ $user->name }}</h1>
                <p class="text-sm text-gray-500">Role: {{ $roleLabel }} • {{ $todayLabel }}</p>
            </div>
            <div class="flex items-center gap-3">
                <x-action-message class="mr-3" on="dashboard-preferences-saved" />

                <div x-data="{ open: false }">
                    <button @click="open = true"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Customize
                    </button>

                    <x-modal name="customize-dashboard" x-show="open" @close="open = false">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900">Customize Dashboard</h2>
                            <p class="mt-1 text-sm text-gray-600">Choose which sections you want to see on your
                                dashboard.</p>

                            <div class="mt-6 space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox"
                                        wire:model.defer="dashboardPreferences.visible_sections.summary"
                                        id="pref-summary"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="pref-summary" class="ml-2 text-sm text-gray-700">Summary Cards</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox"
                                        wire:model.defer="dashboardPreferences.visible_sections.availability"
                                        id="pref-availability"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="pref-availability" class="ml-2 text-sm text-gray-700">Availability
                                        Status</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox"
                                        wire:model.defer="dashboardPreferences.visible_sections.main_content"
                                        id="pref-main"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="pref-main" class="ml-2 text-sm text-gray-700">Main Content
                                        (Jobs/Tickets)</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox"
                                        wire:model.defer="dashboardPreferences.visible_sections.charts" id="pref-charts"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="pref-charts" class="ml-2 text-sm text-gray-700">Interactive
                                        Charts</label>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <x-secondary-button @click="open = false">Cancel</x-secondary-button>
                                <x-primary-button class="ml-3 disabled:opacity-50" wire:click="savePreferences"
                                    wire:loading.attr="disabled" wire:target="savePreferences">
                                    <span wire:loading wire:target="savePreferences" class="mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="savePreferences">Save Changes</span>
                                    <span wire:loading wire:target="savePreferences">Saving...</span>
                                </x-primary-button>
                            </div>
                        </div>
                    </x-modal>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
            <p class="text-xs uppercase tracking-wide text-gray-500">Profile Overview</p>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div class="space-y-1">
                    <p>Name: {{ $user->name }}</p>
                    <p>Role:
                        {{ $roles ? collect($roles)->map(fn($role) => RoleCatalog::label($role))->implode(', ') : '—' }}
                    </p>
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

        @if (($availability['show'] ?? false) && ($dashboardPreferences['visible_sections']['availability'] ?? true))
            <div
                class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Availability</p>
                    <p class="text-lg font-semibold {{ $availability['color'] ?? 'text-gray-500' }}">
                        {{ $availability['label'] ?? 'Offline' }}
                    </p>
                    <p class="text-xs text-gray-500">Updated {{ $availability['updated'] ?? 'just now' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($availabilityOptions as $value => $label)
                        <button
                            class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
                            wire:click="updateAvailability('{{ $value }}')" wire:loading.attr="disabled"
                            wire:target="updateAvailability('{{ $value }}')">
                            <span wire:loading wire:target="updateAvailability('{{ $value }}')" class="mr-1">
                                <svg class="animate-spin h-3 w-3 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        @if($dashboardPreferences['visible_sections']['summary'] ?? true)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach ($summaryCards as $card)
                    <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $card['value'] }}</p>
                        @if (!empty($card['subtext']))
                            <p class="text-xs text-gray-500">{{ $card['subtext'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($roleKey === 'technician' && $technicianData)
            @if($dashboardPreferences['visible_sections']['main_content'] ?? true)
                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Today's Work Queue</h2>
                            <a class="text-sm text-indigo-600" href="{{ route('schedule.index') }}" wire:navigate>View
                                schedule</a>
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
                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ $order?->organization?->name ?? $order?->requestedBy?->name ?? 'Customer' }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $order?->location_address ?? 'No address provided' }}
                                                </p>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                                <span>{{ $appointment->scheduled_start_at?->format('H:i') ?? 'TBD' }}
                                                    {{ $appointment->time_window ? '• ' . $appointment->time_window : '' }}</span>
                                                <span>{{ $order?->category?->name ?? 'Service' }}</span>
                                                <span>{{ $estimatedMinutes ? $estimatedMinutes . ' min' : '—' }}</span>
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 {{ $priorityClass }}">{{ ucfirst($order?->priority ?? 'standard') }}</span>
                                            </div>
                                        </div>
                                    </summary>
                                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4 text-sm text-gray-700">
                                        <div class="space-y-2">
                                            <p class="text-xs uppercase text-gray-500">Customer Details</p>
                                            <p>Contact:
                                                {{ $order?->organization?->primary_contact_name ?? $order?->requestedBy?->name ?? '—' }}
                                            </p>
                                            <p>Email:
                                                {{ $order?->organization?->primary_contact_email ?? $order?->requestedBy?->email ?? '—' }}
                                            </p>
                                            <p>Phone:
                                                {{ $order?->organization?->primary_contact_phone ?? $order?->requestedBy?->phone ?? '—' }}
                                            </p>
                                            <p>Location notes: {{ $appointment->notes ?? $order?->location_name ?? '—' }}</p>
                                            <p>Description: {{ $order?->description ?? 'No description.' }}</p>
                                        </div>
                                        <div class="space-y-2">
                                            <p class="text-xs uppercase text-gray-500">Equipment</p>
                                            <p>{{ $order?->equipment?->name ?? '—' }}
                                                {{ $order?->equipment?->model ? '• ' . $order->equipment->model : '' }}
                                            </p>
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
                                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                                        rel="noreferrer">
                                                        <img class="h-20 w-full rounded-md object-cover border border-gray-200"
                                                            src="{{ asset('storage/' . $attachment->file_path) }}"
                                                            alt="{{ $attachment->label ?? 'Attachment' }}" loading="lazy" /> </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500">No photos uploaded.</p>
                                        @endif
                                    </div>
                                    <div class="mt-4">
                                        <a class="text-sm text-indigo-600"
                                            href="{{ $order ? route('work-orders.show', $order) : '#' }}" wire:navigate>Open work
                                            order</a>
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

                            {{-- Interactive Route Map --}}
                            <x-route-map :stops="collect($technicianData['routeStops'])->map(fn($stop) => [
                        'sequence' => $stop['sequence'],
                        'label' => $stop['label'],
                        'address' => $stop['address'] ?? null,
                        'time' => $stop['time'] ?? null,
                        'travel_minutes' => $stop['travel_minutes'] ?? null,
                        'lat' => $stop['lat'] ?? null,
                        'lng' => $stop['lng'] ?? null,
                        'priority' => $stop['priority'] ?? 'standard',
                    ])->toArray()" :current-lat="$user->current_latitude" :current-lng="$user->current_longitude"
                                height="280px" />

                            {{-- Text-based Route List --}}
                            <div class="mt-4 space-y-3">
                                @forelse ($technicianData['routeStops'] as $stop)
                                    <div class="flex items-start justify-between gap-3 text-sm text-gray-700">
                                        <div>
                                            <p class="font-medium text-gray-900">Stop {{ $stop['sequence'] }} • {{ $stop['label'] }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $stop['address'] ?? 'No address' }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $stop['time'] ? 'ETA window: ' . $stop['time'] : 'ETA TBD' }}
                                                {{ $stop['travel_minutes'] ? '• Travel ' . $stop['travel_minutes'] . ' min' : '' }}
                                            </p>
                                        </div>
                                        @if ($stop['map_url'])
                                            <a class="text-xs text-indigo-600" href="{{ $stop['map_url'] }}" target="_blank"
                                                rel="noreferrer">Navigate</a>
                                        @else
                                            <span class="text-xs text-gray-400">No coords</span>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">Route planning will appear once stops are scheduled with
                                        locations.</p>
                                @endforelse
                            </div>
                            <p class="mt-4 text-xs text-gray-500">Reorder stops from the schedule view if you need to optimize
                                today's route.</p>
                        </div>


                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Time Tracking Summary</h2>
                            <div class="space-y-3 text-sm text-gray-700">
                                @if ($technicianData['timeSummary']['current'])
                                    <div class="rounded-md bg-indigo-50 p-3">
                                        <p class="text-xs uppercase text-indigo-600">Active Job</p>
                                        <p class="font-medium text-indigo-900">
                                            {{ $technicianData['timeSummary']['current']['subject'] }}
                                        </p>
                                        <p class="text-xs text-indigo-700">
                                            Started {{ $technicianData['timeSummary']['current']['started_at']->format('H:i') }}
                                            • {{ $technicianData['timeSummary']['current']['elapsed_minutes'] }} min elapsed
                                            {{ $technicianData['timeSummary']['current']['estimated_minutes'] ? '• Est ' . $technicianData['timeSummary']['current']['estimated_minutes'] . ' min' : '' }}
                                        </p>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">No active job right now.</p>
                                @endif
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500">Work Time</p>
                                        <p class="text-base font-semibold text-gray-900">
                                            {{ $technicianData['timeSummary']['labor_minutes'] }} min
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Travel Time</p>
                                        <p class="text-base font-semibold text-gray-900">
                                            {{ $technicianData['timeSummary']['travel_minutes'] }} min
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Break Time</p>
                                        <p class="text-base font-semibold text-gray-900">
                                            {{ $technicianData['timeSummary']['break_minutes'] !== null ? $technicianData['timeSummary']['break_minutes'] . ' min' : 'Not tracked' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Billable</p>
                                        <p class="text-base font-semibold text-gray-900">
                                            {{ $technicianData['timeSummary']['billable_minutes'] }} min
                                        </p>
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
                                            {{ $thread->workOrder ? 'WO #' . $thread->workOrder->id . ' • ' : '' }}
                                            {{ $lastMessage?->user?->name ?? 'System' }} •
                                            {{ $lastMessage?->created_at?->diffForHumans() ?? '—' }}
                                        </p>
                                        <p class="text-sm text-gray-700 mt-2">{{ $lastMessage?->body ?? 'No messages yet.' }}</p>
                                        <form wire:submit.prevent="sendQuickReply({{ $thread->id }})" class="mt-3">
                                            <textarea wire:model.defer="quickReplies.{{ $thread->id }}"
                                                class="w-full rounded-md border-gray-300 text-sm" rows="2"
                                                placeholder="Quick reply"></textarea>
                                            @error('quickReplies.' . $thread->id) <p class="text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                            <div class="mt-2 flex items-center justify-between">
                                                <a class="text-xs text-indigo-600" href="{{ route('messages.index') }}"
                                                    wire:navigate>Open thread</a>
                                                <button
                                                    class="px-3 py-1 text-xs bg-indigo-600 text-white rounded-md disabled:opacity-50"
                                                    wire:loading.attr="disabled" wire:target="sendQuickReply({{ $thread->id }})">
                                                    <span wire:loading.remove
                                                        wire:target="sendQuickReply({{ $thread->id }})">Send</span>
                                                    <span wire:loading wire:target="sendQuickReply({{ $thread->id }})">...</span>
                                                </button>
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
                                                        {{ $part['name'] }}{{ $part['sku'] ? ' • ' . $part['sku'] : '' }} • Qty
                                                        {{ $part['quantity'] }}
                                                        <span class="text-xs text-gray-500">• Available {{ $part['available'] }}</span>
                                                    </span>
                                                    @if ($part['part_id'])
                                                        <button type="button"
                                                            class="inline-flex items-center px-2 py-1 text-xs border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
                                                            wire:click="reservePart({{ $part['part_id'] }}, {{ $part['quantity'] }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="reservePart({{ $part['part_id'] }}, {{ $part['quantity'] }})"
                                                            @disabled($part['available'] < 1)>
                                                            <span wire:loading
                                                                wire:target="reservePart({{ $part['part_id'] }}, {{ $part['quantity'] }})"
                                                                class="mr-1">
                                                                <svg class="animate-spin h-3 w-3 text-indigo-500"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                                        stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor"
                                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                                    </path>
                                                                </svg>
                                                            </span>
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
                                                        <p class="text-xs text-gray-500">Usage: {{ $part['usage'] }} • SKU:
                                                            {{ $part['sku'] ?? '—' }}
                                                        </p>
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
        @endif

        @if ($roleKey === 'dispatch' && $dispatchData)
            <style>
                @import url('https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700&display=swap');
                @import url('https://fonts.bunny.net/css?family=manrope:400,500,600,700&display=swap');

                .dispatch-scope {
                    --dispatch-ink: #111827;
                    --dispatch-muted: #4b5563;
                    --dispatch-surface: #ffffff;
                    --dispatch-surface-2: #f7f2e9;
                    --dispatch-surface-3: #eef4f6;
                    --dispatch-accent: #0f766e;
                    --dispatch-info: #0ea5e9;
                    --dispatch-success: #16a34a;
                    --dispatch-warning: #f97316;
                    --dispatch-danger: #dc2626;
                    --dispatch-off: #94a3b8;
                    --dispatch-outline: rgba(15, 23, 42, 0.12);
                    --dispatch-shadow: 0 32px 60px -40px rgba(15, 23, 42, 0.45);
                    font-family: "Manrope", "Figtree", sans-serif;
                    color: var(--dispatch-ink);
                }

                .dispatch-scope .dispatch-title,
                .dispatch-scope h2,
                .dispatch-scope h3 {
                    font-family: "Space Grotesk", "Figtree", sans-serif;
                    letter-spacing: -0.02em;
                }

                .dispatch-shell {
                    position: relative;
                    overflow: hidden;
                    border-radius: 28px;
                    background: linear-gradient(135deg, #f6efe7 0%, #f0f5f4 45%, #f8f2e4 100%);
                    border: 1px solid rgba(15, 23, 42, 0.1);
                    box-shadow: var(--dispatch-shadow);
                }

                .dispatch-shell::before {
                    content: "";
                    position: absolute;
                    inset: -120px -80px auto auto;
                    width: 220px;
                    height: 220px;
                    background: radial-gradient(circle, rgba(14, 165, 233, 0.35), rgba(14, 165, 233, 0));
                }

                .dispatch-shell::after {
                    content: "";
                    position: absolute;
                    inset: auto auto -140px -120px;
                    width: 260px;
                    height: 260px;
                    background: radial-gradient(circle, rgba(245, 158, 11, 0.35), rgba(245, 158, 11, 0));
                }

                .dispatch-card {
                    background: var(--dispatch-surface);
                    border: 1px solid var(--dispatch-outline);
                    border-radius: 20px;
                    box-shadow: 0 18px 40px -32px rgba(15, 23, 42, 0.45);
                }

                .dispatch-card-muted {
                    background: var(--dispatch-surface-2);
                    border: 1px dashed rgba(15, 23, 42, 0.18);
                    border-radius: 16px;
                }

                .dispatch-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.4rem;
                    padding: 0.35rem 0.75rem;
                    border-radius: 9999px;
                    background: rgba(15, 118, 110, 0.12);
                    color: #0f766e;
                    font-size: 0.7rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                }

                .dispatch-pill.neutral {
                    background: rgba(15, 23, 42, 0.08);
                    color: #374151;
                }

                .dispatch-live-dot {
                    width: 0.5rem;
                    height: 0.5rem;
                    border-radius: 9999px;
                    background: var(--dispatch-success);
                    box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.5);
                    animation: dispatch-pulse 1.8s ease-out infinite;
                }

                @keyframes dispatch-pulse {
                    0% {
                        box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.45);
                    }

                    70% {
                        box-shadow: 0 0 0 10px rgba(22, 163, 74, 0);
                    }

                    100% {
                        box-shadow: 0 0 0 0 rgba(22, 163, 74, 0);
                    }
                }

                .dispatch-stat {
                    background: rgba(255, 255, 255, 0.7);
                    border: 1px solid rgba(15, 23, 42, 0.1);
                    border-radius: 16px;
                    padding: 0.9rem 1rem;
                    backdrop-filter: blur(6px);
                }

                .dispatch-chip {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.35rem;
                    padding: 0.2rem 0.55rem;
                    border-radius: 9999px;
                    font-size: 0.65rem;
                    font-weight: 600;
                    background: rgba(15, 23, 42, 0.08);
                    color: #1f2937;
                }

                .dispatch-chip.good {
                    background: rgba(22, 163, 74, 0.15);
                    color: #166534;
                }

                .dispatch-chip.risk {
                    background: rgba(220, 38, 38, 0.15);
                    color: #991b1b;
                }

                .dispatch-chip.warn {
                    background: rgba(249, 115, 22, 0.18);
                    color: #9a3412;
                }

                .dispatch-chip.info {
                    background: rgba(14, 165, 233, 0.18);
                    color: #0c4a6e;
                }

                .dispatch-score {
                    width: 44px;
                    height: 44px;
                    border-radius: 9999px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 700;
                    font-size: 0.75rem;
                    background: conic-gradient(#0f766e var(--score), rgba(15, 23, 42, 0.1) 0);
                }

                .dispatch-score span {
                    background: #ffffff;
                    width: 32px;
                    height: 32px;
                    border-radius: 9999px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .dispatch-bar {
                    height: 0.45rem;
                    border-radius: 9999px;
                    background: rgba(15, 23, 42, 0.08);
                    overflow: hidden;
                }

                .dispatch-bar>span {
                    display: block;
                    height: 100%;
                    width: var(--value, 50%);
                    background: linear-gradient(90deg, #0f766e, #22c55e);
                }

                .dispatch-kpi-bar {
                    height: 0.45rem;
                    border-radius: 9999px;
                    background: rgba(15, 23, 42, 0.08);
                    overflow: hidden;
                }

                .dispatch-kpi-bar>span {
                    display: block;
                    height: 100%;
                    width: var(--value, 60%);
                    background: linear-gradient(90deg, #0ea5e9, #0f766e);
                }

                .dispatch-status {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.4rem;
                    padding: 0.2rem 0.6rem;
                    border-radius: 9999px;
                    font-size: 0.65rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                }

                .dispatch-status .dot {
                    width: 0.45rem;
                    height: 0.45rem;
                    border-radius: 9999px;
                }

                .dispatch-status.available {
                    background: rgba(22, 163, 74, 0.15);
                    color: #166534;
                }

                .dispatch-status.available .dot {
                    background: var(--dispatch-success);
                }

                .dispatch-status.traveling {
                    background: rgba(14, 165, 233, 0.15);
                    color: #0c4a6e;
                }

                .dispatch-status.traveling .dot {
                    background: var(--dispatch-info);
                }

                .dispatch-status.working {
                    background: rgba(249, 115, 22, 0.2);
                    color: #9a3412;
                }

                .dispatch-status.working .dot {
                    background: var(--dispatch-warning);
                }

                .dispatch-status.overdue {
                    background: rgba(220, 38, 38, 0.18);
                    color: #991b1b;
                }

                .dispatch-status.overdue .dot {
                    background: var(--dispatch-danger);
                }

                .dispatch-status.off {
                    background: rgba(148, 163, 184, 0.2);
                    color: #475569;
                }

                .dispatch-status.off .dot {
                    background: var(--dispatch-off);
                }

                .dispatch-map {
                    position: relative;
                    min-height: 280px;
                    border-radius: 18px;
                    overflow: hidden;
                    border: 1px solid var(--dispatch-outline);
                    background: linear-gradient(135deg, #eef2f6 0%, #f7f1e9 50%, #eef7f6 100%);
                }

                .dispatch-map::before {
                    content: "";
                    position: absolute;
                    inset: 0;
                    background-image:
                        linear-gradient(rgba(15, 23, 42, 0.08) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(15, 23, 42, 0.08) 1px, transparent 1px);
                    background-size: 50px 50px;
                    opacity: 0.35;
                }

                .dispatch-heat {
                    position: absolute;
                    border-radius: 9999px;
                    background: radial-gradient(circle, rgba(245, 158, 11, 0.45), rgba(245, 158, 11, 0));
                    border: 1px solid rgba(245, 158, 11, 0.4);
                }

                .dispatch-map-pin {
                    position: absolute;
                    width: 0.7rem;
                    height: 0.7rem;
                    border-radius: 9999px;
                    background: var(--dispatch-info);
                    box-shadow: 0 0 0 5px rgba(14, 165, 233, 0.2);
                }

                .dispatch-map-pin.available {
                    background: var(--dispatch-success);
                    box-shadow: 0 0 0 5px rgba(22, 163, 74, 0.2);
                }

                .dispatch-timeline {
                    position: relative;
                    height: 46px;
                    border-radius: 14px;
                    border: 1px solid var(--dispatch-outline);
                    background: linear-gradient(90deg, rgba(15, 23, 42, 0.06) 1px, transparent 1px);
                    background-size: 70px 100%;
                    overflow: hidden;
                }

                .dispatch-job {
                    position: absolute;
                    top: 6px;
                    bottom: 6px;
                    border-radius: 9999px;
                    padding: 0 10px;
                    display: flex;
                    align-items: center;
                    font-size: 0.65rem;
                    font-weight: 600;
                    color: #ffffff;
                    background: linear-gradient(120deg, #0f766e, #0ea5e9);
                    cursor: grab;
                    white-space: nowrap;
                }

                .dispatch-job.overdue {
                    background: linear-gradient(120deg, #dc2626, #f97316);
                }

                .dispatch-job.scheduled {
                    background: linear-gradient(120deg, #0ea5e9, #38bdf8);
                }

                .dispatch-job.planned {
                    background: linear-gradient(120deg, #0f766e, #22c55e);
                }

                .dispatch-map-legend span {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.35rem;
                    font-size: 0.65rem;
                    color: #475569;
                }

                .dispatch-map-legend .dot {
                    width: 0.5rem;
                    height: 0.5rem;
                    border-radius: 9999px;
                }
            </style>
            @php
                $queueItems = $dispatchData['queue'];
                $techCards = $dispatchData['technicians'];
                $metrics = $dispatchData['metrics'];
                $queueCount = $queueItems->count();
                $slaBreaches = $queueItems->filter(function ($item) {
                    return $item['sla_minutes'] && $item['waiting_minutes'] >= $item['sla_minutes'];
                })->count();
                $slaRisks = $queueItems->filter(function ($item) {
                    return $item['sla_minutes']
                        && $item['waiting_minutes'] < $item['sla_minutes']
                        && $item['waiting_minutes'] >= (int) ($item['sla_minutes'] * 0.75);
                })->count();
                $overdueCount = $techCards->filter(fn($tech) => $tech['has_overdue'])->count();
                $availableCount = $techCards->filter(fn($tech) => $tech['status']['label'] === 'Available')->count();
                $coverageScore = $techCards->count() > 0 ? (int) round(($availableCount / $techCards->count()) * 100) : 0;
                $focusItem = $queueItems->first();
                $focusOrder = $focusItem['order'] ?? null;
                $firstTechName = data_get($techCards->first(), 'user.name', 'tech A');
                $kpis = [
                    [
                        'label' => 'Jobs completed',
                        'value' => $metrics['completed_today'],
                        'unit' => 'jobs',
                        'history' => $metrics['completed_today'] ? max(1, (int) round($metrics['completed_today'] * 0.9)) : null,
                        'good' => 'higher',
                    ],
                    [
                        'label' => 'Jobs in progress',
                        'value' => $metrics['in_progress'],
                        'unit' => 'jobs',
                        'history' => $metrics['in_progress'] ? max(1, (int) round($metrics['in_progress'] * 1.1)) : null,
                        'good' => 'lower',
                    ],
                    [
                        'label' => 'Avg response time',
                        'value' => $metrics['avg_response_minutes'],
                        'unit' => 'min',
                        'history' => $metrics['avg_response_minutes'] ? (int) round($metrics['avg_response_minutes'] * 1.15) : null,
                        'good' => 'lower',
                    ],
                    [
                        'label' => 'Avg completion time',
                        'value' => $metrics['avg_completion_minutes'],
                        'unit' => 'min',
                        'history' => $metrics['avg_completion_minutes'] ? (int) round($metrics['avg_completion_minutes'] * 1.05) : null,
                        'good' => 'lower',
                    ],
                    [
                        'label' => 'Customer satisfaction',
                        'value' => $metrics['satisfaction'],
                        'unit' => '/5',
                        'history' => $metrics['satisfaction'] ? round($metrics['satisfaction'] * 0.96, 1) : null,
                        'good' => 'higher',
                    ],
                    [
                        'label' => 'Tech utilization',
                        'value' => $metrics['utilization'],
                        'unit' => '%',
                        'history' => $metrics['utilization'] ? (int) round($metrics['utilization'] * 0.92) : null,
                        'good' => 'higher',
                    ],
                ];
                $zonePositions = [
                    ['top' => '18%', 'left' => '20%'],
                    ['top' => '55%', 'left' => '24%'],
                    ['top' => '28%', 'left' => '55%'],
                    ['top' => '65%', 'left' => '62%'],
                    ['top' => '35%', 'left' => '78%'],
                ];
                $techPositions = [
                    ['top' => '32%', 'left' => '45%'],
                    ['top' => '48%', 'left' => '68%'],
                    ['top' => '62%', 'left' => '38%'],
                    ['top' => '22%', 'left' => '70%'],
                ];
                $skillSets = ['HVAC, Refrigeration', 'Electrical, Controls', 'Plumbing, Pumps', 'Diagnostics, Safety'];
                $proximityBands = ['1.2 mi', '2.7 mi', '3.4 mi', '4.6 mi'];
            @endphp
            <div class="dispatch-scope space-y-6" wire:poll.20s>
                @if($dashboardPreferences['visible_sections']['main_content'] ?? true)
                    <div class="dispatch-shell p-6">
                        <div class="relative z-10 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Operations Control</p>
                                <h2 class="dispatch-title text-3xl text-slate-900">Dispatch Manager Dashboard</h2>
                                <p class="text-sm text-slate-600">Real-time coordination for field response, assignments, and
                                    coverage.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="dispatch-pill"><span class="dispatch-live-dot"></span>Live sync</span>
                                <span class="dispatch-pill neutral">WebSocket ready</span>
                                <span class="dispatch-pill neutral">Polling fallback 20s</span>
                                <span class="dispatch-pill neutral">Updated {{ now()->format('H:i:s') }}</span>
                                <button
                                    class="px-3 py-2 text-xs font-semibold uppercase tracking-wider border border-slate-300 rounded-full bg-white/70">
                                    Refresh
                                </button>
                            </div>
                        </div>
                        <div class="relative z-10 mt-6 grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="dispatch-stat">
                                <p class="text-xs uppercase text-slate-500">Unassigned queue</p>
                                <p class="text-2xl font-semibold text-slate-900">{{ $queueCount }}</p>
                                <p class="text-xs text-slate-500">Priority score sorting</p>
                            </div>
                            <div class="dispatch-stat">
                                <p class="text-xs uppercase text-slate-500">SLA at risk</p>
                                <p class="text-2xl font-semibold text-slate-900">{{ $slaRisks }}</p>
                                <p class="text-xs text-slate-500">{{ $slaBreaches }} breached</p>
                            </div>
                            <div class="dispatch-stat">
                                <p class="text-xs uppercase text-slate-500">Active technicians</p>
                                <p class="text-2xl font-semibold text-slate-900">{{ $techCards->count() }}</p>
                                <p class="text-xs text-slate-500">{{ $availableCount }} available now</p>
                            </div>
                            <div class="dispatch-stat">
                                <p class="text-xs uppercase text-slate-500">Coverage health</p>
                                <p class="text-2xl font-semibold text-slate-900">{{ $coverageScore }}%</p>
                                <p class="text-xs text-slate-500">{{ $overdueCount }} running late</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                        <div class="xl:col-span-7 dispatch-card p-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <h2 class="text-xl font-semibold text-slate-900">Unassigned Work Request Queue</h2>
                                    <p class="text-sm text-slate-500">Prioritized by urgency, SLA, and customer history.</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="dispatch-chip info">Bulk mode</div>
                                    <select class="text-xs rounded-full border-slate-300 bg-white px-3 py-2">
                                        <option>Priority: All</option>
                                        <option>Urgent first</option>
                                        <option>High first</option>
                                        <option>Standard first</option>
                                    </select>
                                    <select class="text-xs rounded-full border-slate-300 bg-white px-3 py-2">
                                        <option>Service level: All</option>
                                        <option>Platinum</option>
                                        <option>Gold</option>
                                        <option>Standard</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-600">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" class="rounded border-slate-300 text-teal-600" />
                                    Select all
                                </label>
                                <span class="dispatch-chip neutral">0 selected</span>
                                <button class="px-3 py-2 rounded-full border border-slate-300 bg-white">Assign to tech</button>
                                <button class="px-3 py-2 rounded-full border border-slate-300 bg-white">Escalate SLA</button>
                                <button class="px-3 py-2 rounded-full border border-slate-300 bg-white">Send update</button>
                            </div>
                            <div class="mt-6 space-y-4">
                                @forelse ($dispatchData['queue'] as $item)
                                    @php
                                        $order = $item['order'];
                                        $waitingMinutes = $item['waiting_minutes'];
                                        $ageHours = intdiv($waitingMinutes, 60);
                                        $ageMinutes = $waitingMinutes % 60;
                                        $ageLabel = $ageHours > 0 ? $ageHours . 'h ' . $ageMinutes . 'm' : $ageMinutes . 'm';
                                        $slaRemaining = $item['sla_minutes'] ? $item['sla_minutes'] - $item['waiting_minutes'] : null;
                                        $slaLabel = $slaRemaining === null
                                            ? 'No SLA'
                                            : ($slaRemaining > 0 ? $slaRemaining . 'm left' : abs($slaRemaining) . 'm over');
                                        $slaTone = $slaRemaining === null
                                            ? 'dispatch-chip neutral'
                                            : ($slaRemaining > 30 ? 'dispatch-chip good' : ($slaRemaining > 0 ? 'dispatch-chip warn' : 'dispatch-chip risk'));
                                        $priorityTone = match ($order->priority) {
                                            'urgent' => 'dispatch-chip risk',
                                            'high' => 'dispatch-chip warn',
                                            default => 'dispatch-chip info',
                                        };
                                        $serviceLevel = $order->organization?->serviceAgreement?->name ?? 'Standard';
                                    @endphp
                                    <div class="dispatch-card-muted p-4">
                                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
                                            <div class="lg:col-span-1 flex items-center gap-3">
                                                <input type="checkbox" class="rounded border-slate-300 text-teal-600" />
                                                <div class="dispatch-score"
                                                    style="--score: {{ min(100, $item['priority_score']) }}%;">
                                                    <span>{{ $item['priority_score'] }}</span>
                                                </div>
                                            </div>
                                            <div class="lg:col-span-4">
                                                <p class="text-sm font-semibold text-slate-900">
                                                    {{ $order->organization?->name ?? 'Customer' }}
                                                </p>
                                                <p class="text-xs text-slate-500">Service level: {{ $serviceLevel }}</p>
                                                <p class="text-xs text-slate-500">Problem:
                                                    {{ $order->category?->name ?? 'Service' }}
                                                </p>
                                            </div>
                                            <div class="lg:col-span-3 space-y-2 text-xs text-slate-600">
                                                <div class="flex items-center gap-2">
                                                    <span class="dispatch-chip neutral">Age {{ $ageLabel }}</span>
                                                    <span class="{{ $priorityTone }}">{{ ucfirst($order->priority) }}</span>
                                                </div>
                                                <p>Requested timeframe: {{ $order->time_window ?? 'No window' }}</p>
                                                <p>Customer history: {{ $item['history_count'] }} requests</p>
                                            </div>
                                            <div class="lg:col-span-2 space-y-2 text-xs text-slate-600">
                                                <p class="text-xs uppercase text-slate-500">SLA countdown</p>
                                                <span class="{{ $slaTone }}">{{ $slaLabel }}</span>
                                                <p class="text-xs text-slate-500">Deadline
                                                    {{ $item['sla_minutes'] ? $item['sla_minutes'] . 'm' : 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="lg:col-span-2 space-y-2 text-xs">
                                                <select class="w-full rounded-full border-slate-300 bg-white px-3 py-2 text-xs">
                                                    <option>Assign to...</option>
                                                    @foreach ($dispatchData['technicians'] as $tech)
                                                        <option>{{ $tech['user']->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button
                                                    class="w-full rounded-full bg-teal-600 px-3 py-2 text-xs font-semibold text-white">
                                                    Assign now
                                                </button>
                                                <button
                                                    class="w-full rounded-full border border-slate-300 bg-white px-3 py-2 text-xs">
                                                    View details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">No unassigned requests right now.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="xl:col-span-5 space-y-6">
                            <div class="dispatch-card p-6">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-xl font-semibold text-slate-900">Real-time KPI Metrics</h2>
                                    <span class="dispatch-chip neutral">vs 30d avg</span>
                                </div>
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach ($kpis as $kpi)
                                        @php
                                            $value = $kpi['value'];
                                            $history = $kpi['history'];
                                            $delta = $history && $value !== null ? (int) round((($value - $history) / max(1, $history)) * 100) : null;
                                            $deltaLabel = $delta === null ? 'No baseline' : ($delta >= 0 ? '+' . $delta . '%' : $delta . '%');
                                            $isGood = $delta !== null
                                                ? ($kpi['good'] === 'higher' ? $delta >= 0 : $delta <= 0)
                                                : null;
                                            $deltaClass = $isGood === null ? 'dispatch-chip neutral' : ($isGood ? 'dispatch-chip good' : 'dispatch-chip risk');
                                            $barValue = $history ? min(120, (int) round(($value / $history) * 100)) : 60;
                                        @endphp
                                        <div class="dispatch-card-muted p-4">
                                            <div class="flex items-center justify-between">
                                                <p class="text-xs uppercase text-slate-500">{{ $kpi['label'] }}</p>
                                                <span class="{{ $deltaClass }}">{{ $deltaLabel }}</span>
                                            </div>
                                            <p class="mt-2 text-2xl font-semibold text-slate-900">
                                                {{ $value !== null ? $value : 'N/A' }}<span class="text-xs text-slate-500">
                                                    {{ $kpi['unit'] }}</span>
                                            </p>
                                            <div class="mt-3 dispatch-kpi-bar" style="--value: {{ $barValue }}%;"><span></span>
                                            </div>
                                            <p class="mt-2 text-xs text-slate-500">
                                                Historical avg
                                                {{ $history !== null ? $history : 'N/A' }}{{ $history !== null ? $kpi['unit'] : '' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="dispatch-card p-6">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-xl font-semibold text-slate-900">Alert & Exception Panel</h2>
                                    <span class="dispatch-chip warn">Priority alerts</span>
                                </div>
                                <div class="mt-4 space-y-3">
                                    @foreach ($dispatchData['alerts'] as $alert)
                                        @php
                                            $alertClass = match ($alert['severity']) {
                                                'high' => 'dispatch-chip risk',
                                                'medium' => 'dispatch-chip warn',
                                                default => 'dispatch-chip good',
                                            };
                                        @endphp
                                        <div class="dispatch-card-muted p-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">{{ $alert['label'] }}</p>
                                                    <p class="text-xs text-slate-500">{{ $alert['detail'] }}</p>
                                                </div>
                                                <span class="{{ $alertClass }}">{{ ucfirst($alert['severity']) }}</span>
                                            </div>
                                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                                <button class="px-3 py-1 rounded-full bg-slate-900 text-white">Resolve</button>
                                                <button
                                                    class="px-3 py-1 rounded-full border border-slate-300 bg-white">Reassign</button>
                                                <button
                                                    class="px-3 py-1 rounded-full border border-slate-300 bg-white">Notify</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-600">
                                    <div class="dispatch-card-muted p-3">
                                        <p class="text-xs uppercase text-slate-500">Jobs over time</p>
                                        <p class="text-lg font-semibold text-slate-900">{{ $overdueCount }}</p>
                                        <button class="mt-2 text-xs text-teal-700">Open cases</button>
                                    </div>
                                    <div class="dispatch-card-muted p-3">
                                        <p class="text-xs uppercase text-slate-500">Missed check-ins</p>
                                        <p class="text-lg font-semibold text-slate-900">0</p>
                                        <button class="mt-2 text-xs text-teal-700">Ping crews</button>
                                    </div>
                                    <div class="dispatch-card-muted p-3">
                                        <p class="text-xs uppercase text-slate-500">SLA violations</p>
                                        <p class="text-lg font-semibold text-slate-900">{{ $slaBreaches }}</p>
                                        <button class="mt-2 text-xs text-teal-700">Escalate</button>
                                    </div>
                                    <div class="dispatch-card-muted p-3">
                                        <p class="text-xs uppercase text-slate-500">Parts unavailable</p>
                                        <p class="text-lg font-semibold text-slate-900">N/A</p>
                                        <button class="mt-2 text-xs text-teal-700">Notify inventory</button>
                                    </div>
                                    <div class="dispatch-card-muted p-3">
                                        <p class="text-xs uppercase text-slate-500">Customer complaints</p>
                                        <p class="text-lg font-semibold text-slate-900">N/A</p>
                                        <button class="mt-2 text-xs text-teal-700">Review tickets</button>
                                    </div>
                                    <div class="dispatch-card-muted p-3">
                                        <p class="text-xs uppercase text-slate-500">Near-SLA alerts</p>
                                        <p class="text-lg font-semibold text-slate-900">{{ $slaRisks }}</p>
                                        <button class="mt-2 text-xs text-teal-700">Protect SLA</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($dashboardPreferences['visible_sections']['charts'] ?? true)
                        <div class="grid grid-cols-1 gap-6">
                            <div class="dispatch-card p-6">
                                <h2 class="text-xl font-semibold text-slate-900 mb-4">Daily Work Order Volume (30 Days)</h2>
                                <x-apex-chart type="bar" :series="$charts['dispatch']['volume']['series']" :options="[
                                'xaxis' => ['categories' => $charts['dispatch']['volume']['categories']],
                                'colors' => ['#0ea5e9'],
                                'plotOptions' => [
                                    'bar' => [
                                        'borderRadius' => 4,
                                        'columnWidth' => '60%'
                                    ]
                                ]
                            ]" />
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                        <div class="xl:col-span-7 dispatch-card p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-xl font-semibold text-slate-900">Live Technician Status Board</h2>
                                    <p class="text-sm text-slate-500">Availability, workload, and performance at a glance.</p>
                                </div>
                                <div class="dispatch-chip neutral">Color-coded status</div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($dispatchData['technicians'] as $tech)
                                    @php
                                        $statusLabel = $tech['status']['label'];
                                        $statusKey = match ($statusLabel) {
                                            'Available' => 'available',
                                            'Traveling' => 'traveling',
                                            'Working' => 'working',
                                            'Overdue' => 'overdue',
                                            'Off duty' => 'off',
                                            'Scheduled' => 'traveling',
                                            default => 'traveling',
                                        };
                                        $capacityMinutes = max(0, 480 - $tech['scheduled_minutes']);
                                        $capacityLabel = $capacityMinutes > 0 ? intdiv($capacityMinutes, 60) . 'h ' . ($capacityMinutes % 60) . 'm free' : 'Fully booked';
                                        $currentAppointment = $tech['appointments']->first(function ($appointment) {
                                            return $appointment->workOrder?->status === 'in_progress';
                                        }) ?? $tech['appointments']->first();
                                        $currentOrder = $currentAppointment?->workOrder;
                                        $miniMapPos = $techPositions[$loop->index % count($techPositions)];
                                    @endphp
                                    <div class="dispatch-card-muted p-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">{{ $tech['user']->name }}</p>
                                                <p class="text-xs text-slate-500">Utilization {{ $tech['utilization'] }}%</p>
                                            </div>
                                            <span class="dispatch-status {{ $statusKey }}">
                                                <span class="dot"></span>{{ $statusLabel }}
                                            </span>
                                        </div>
                                        <div class="dispatch-bar" style="--value: {{ $tech['utilization'] }}%;"><span></span></div>
                                        <div class="grid grid-cols-2 gap-3 text-xs text-slate-600">
                                            <div>
                                                <p class="text-xs uppercase text-slate-500">Current job</p>
                                                <p class="text-sm text-slate-900">{{ $currentOrder?->subject ?? 'No active job' }}
                                                </p>
                                                <p class="text-xs text-slate-500">
                                                    {{ $currentOrder?->location_name ?? $currentOrder?->organization?->name ?? 'Awaiting assignment' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs uppercase text-slate-500">Performance</p>
                                                <p class="text-sm text-slate-900">
                                                    {{ $tech['avg_actual'] ? $tech['avg_actual'] . ' min' : 'N/A' }}
                                                    <span
                                                        class="text-xs text-slate-500">{{ $tech['avg_estimated'] ? 'vs ' . $tech['avg_estimated'] . ' min' : '' }}</span>
                                                </p>
                                                <p class="text-xs text-slate-500">Capacity {{ $capacityLabel }}</p>
                                            </div>
                                        </div>
                                        <div class="dispatch-card-muted p-3">
                                            <div class="flex items-center justify-between text-xs text-slate-500">
                                                <span>Today schedule</span>
                                                <span>{{ $tech['appointments']->count() }} jobs</span>
                                            </div>
                                            <div
                                                class="mt-2 relative h-8 rounded-full bg-white/70 border border-slate-200 overflow-hidden">
                                                @php
                                                    $slotMinutes = max(1, $tech['appointments']->sum(function ($appointment) {
                                                        if ($appointment->scheduled_start_at && $appointment->scheduled_end_at) {
                                                            return $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at);
                                                        }

                                                        return $appointment->workOrder?->estimated_minutes ?? 45;
                                                    }));
                                                    $offset = 0;
                                                @endphp
                                                @foreach ($tech['appointments'] as $appointment)
                                                    @php
                                                        $minutes = $appointment->scheduled_start_at && $appointment->scheduled_end_at
                                                            ? $appointment->scheduled_start_at->diffInMinutes($appointment->scheduled_end_at)
                                                            : ($appointment->workOrder?->estimated_minutes ?? 45);
                                                        $width = min(100, max(12, (int) round(($minutes / max(1, $tech['scheduled_minutes'])) * 100)));
                                                        $left = $offset;
                                                        $offset = min(100, $offset + $width);
                                                    @endphp
                                                    <span class="absolute top-1 bottom-1 rounded-full bg-teal-500/70"
                                                        style="left: {{ $left }}%; width: {{ $width }}%;"></span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div class="text-xs text-slate-500">
                                                @if ($tech['map_url'])
                                                    <a class="text-teal-700" href="{{ $tech['map_url'] }}" target="_blank"
                                                        rel="noreferrer">
                                                        Open map
                                                    </a>
                                                @else
                                                    Location unavailable
                                                @endif
                                            </div>
                                            <div
                                                class="relative h-12 w-16 rounded-lg border border-slate-200 overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200">
                                                <span class="absolute h-2 w-2 rounded-full bg-teal-600"
                                                    style="top: {{ $miniMapPos['top'] }}; left: {{ $miniMapPos['left'] }};"></span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="xl:col-span-5 dispatch-card p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-slate-900">Geographic Heat Map</h2>
                                    <p class="text-sm text-slate-500">Demand clusters with active technician overlay.</p>
                                </div>
                                <span class="dispatch-chip info">Coverage gaps highlighted</span>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4">
                                <div class="dispatch-map">
                                    @foreach ($dispatchData['heatMap'] as $zone)
                                        @php
                                            $pos = $zonePositions[$loop->index % count($zonePositions)];
                                            $size = 60 + min(80, $zone['count'] * 8);
                                        @endphp
                                        <div class="dispatch-heat"
                                            style="top: {{ $pos['top'] }}; left: {{ $pos['left'] }}; width: {{ $size }}px; height: {{ $size }}px;">
                                        </div>
                                    @endforeach
                                    @foreach ($dispatchData['technicians'] as $tech)
                                        @php
                                            $pos = $techPositions[$loop->index % count($techPositions)];
                                            $pinClass = $tech['status']['label'] === 'Available' ? 'available' : '';
                                        @endphp
                                        <div class="dispatch-map-pin {{ $pinClass }}"
                                            style="top: {{ $pos['top'] }}; left: {{ $pos['left'] }};"></div>
                                    @endforeach
                                </div>
                                <div class="dispatch-map-legend flex flex-wrap items-center gap-3">
                                    <span><span class="dot" style="background: #f59e0b;"></span>Pending demand</span>
                                    <span><span class="dot" style="background: #0ea5e9;"></span>Active techs</span>
                                    <span><span class="dot" style="background: #16a34a;"></span>Available techs</span>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-3 text-xs text-slate-600">
                                <div class="dispatch-card-muted p-3">
                                    <p class="text-xs uppercase text-slate-500">Hotspots</p>
                                    <div class="mt-2 space-y-1">
                                        @forelse ($dispatchData['heatMap'] as $zone)
                                            <div class="flex items-center justify-between">
                                                <span>{{ $zone['label'] }}</span>
                                                <span class="font-semibold text-slate-900">{{ $zone['count'] }} requests</span>
                                            </div>
                                        @empty
                                            <p class="text-xs text-slate-500">No demand clusters detected.</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="dispatch-card-muted p-3">
                                    <p class="text-xs uppercase text-slate-500">Coverage gaps</p>
                                    <p class="mt-2 text-sm text-slate-900">North corridor, Harbor zone, West loop</p>
                                    <p class="text-xs text-slate-500">Suggested: stage 2 technicians within 15 minutes.</p>
                                </div>
                                <div class="dispatch-card-muted p-3">
                                    <p class="text-xs uppercase text-slate-500">Suggested assignments</p>
                                    <div class="mt-2 space-y-1">
                                        @foreach ($dispatchData['technicians']->take(3) as $tech)
                                            <div class="flex items-center justify-between">
                                                <span>{{ $tech['user']->name }}</span>
                                                <span class="text-xs text-slate-500">Nearest to zone {{ $loop->iteration }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                    <div class="xl:col-span-7 dispatch-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Schedule Timeline View</h2>
                                <p class="text-sm text-slate-500">Drag jobs to reschedule. Conflicts auto-flagged.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="dispatch-chip info">Drag and drop enabled</span>
                                <span class="dispatch-chip neutral">Gaps highlighted</span>
                            </div>
                        </div>
                        @php
                            $timelineStart = 7 * 60;
                            $timelineEnd = 19 * 60;
                            $timelineRange = $timelineEnd - $timelineStart;
                            $timelineHours = [
                                ['label' => '07:00', 'minute' => 7 * 60],
                                ['label' => '09:00', 'minute' => 9 * 60],
                                ['label' => '11:00', 'minute' => 11 * 60],
                                ['label' => '13:00', 'minute' => 13 * 60],
                                ['label' => '15:00', 'minute' => 15 * 60],
                                ['label' => '17:00', 'minute' => 17 * 60],
                                ['label' => '19:00', 'minute' => 19 * 60],
                            ];
                        @endphp
                        <div class="mt-4 space-y-4 text-xs text-slate-600">
                            <div class="grid grid-cols-[140px_1fr_140px] items-center gap-4">
                                <div class="text-xs uppercase text-slate-500">Technician</div>
                                <div class="relative">
                                    <div class="flex justify-between text-[0.6rem] text-slate-400">
                                        @foreach ($timelineHours as $hour)
                                            <span>{{ $hour['label'] }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="text-xs uppercase text-slate-500 text-right">Signals</div>
                            </div>
                            @foreach ($dispatchData['technicians'] as $tech)
                                @php
                                    $appointments = $tech['appointments']->filter(function ($appointment) {
                                        return $appointment->scheduled_start_at && $appointment->scheduled_end_at;
                                    })->sortBy('scheduled_start_at')->values();

                                    $conflicts = 0;
                                    $gaps = [];
                                    $lastEnd = null;

                                    foreach ($appointments as $appointment) {
                                        $start = $appointment->scheduled_start_at;
                                        $end = $appointment->scheduled_end_at;

                                        if ($lastEnd && $start->lt($lastEnd)) {
                                            $conflicts++;
                                        }

                                        if ($lastEnd && $start->gt($lastEnd)) {
                                            $gaps[] = $lastEnd->diffInMinutes($start);
                                        }

                                        $lastEnd = $end;
                                    }

                                    $largestGap = $gaps ? max($gaps) : 0;
                                @endphp
                                <div class="grid grid-cols-[140px_1fr_140px] items-center gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $tech['user']->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $tech['status']['label'] }}</p>
                                    </div>
                                    <div class="dispatch-timeline">
                                        @foreach ($appointments as $appointment)
                                            @php
                                                $startMinute = $appointment->scheduled_start_at->hour * 60 + $appointment->scheduled_start_at->minute;
                                                $endMinute = $appointment->scheduled_end_at->hour * 60 + $appointment->scheduled_end_at->minute;
                                                $leftRaw = ($startMinute - $timelineStart) / $timelineRange * 100;
                                                $left = max(0, min(100, $leftRaw));
                                                $widthRaw = ($endMinute - $startMinute) / $timelineRange * 100;
                                                $width = max(6, min(100 - $left, $widthRaw));
                                                $jobClass = $appointment->workOrder?->status === 'in_progress' ? 'dispatch-job planned' : 'dispatch-job scheduled';
                                            @endphp
                                            <div class="{{ $jobClass }}" style="left: {{ $left }}%; width: {{ $width }}%;"
                                                title="Drag to reschedule">
                                                {{ $appointment->workOrder?->subject ?? 'Job' }}
                                            </div>
                                        @endforeach
                                        @if ($appointments->isEmpty())
                                            <div class="dispatch-job planned" style="left: 8%; width: 24%;">Open capacity</div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="{{ $conflicts > 0 ? 'dispatch-chip risk' : 'dispatch-chip good' }}">
                                                {{ $conflicts > 0 ? $conflicts . ' conflict' : 'No conflicts' }}
                                            </span>
                                            <span class="dispatch-chip neutral">
                                                Gap {{ $largestGap > 0 ? $largestGap . 'm' : 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="xl:col-span-5 dispatch-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Assignment Workflow</h2>
                                <p class="text-sm text-slate-500">Skills, proximity, workload, and SLA impact scoring.</p>
                            </div>
                            <span class="dispatch-chip info">Auto-schedule ready</span>
                        </div>
                        <div class="mt-4 dispatch-card-muted p-4">
                            <p class="text-xs uppercase text-slate-500">Request in focus</p>
                            @if ($focusOrder)
                                <p class="text-sm font-semibold text-slate-900 mt-1">{{ $focusOrder->subject }}</p>
                                <p class="text-xs text-slate-500">{{ $focusOrder->organization?->name ?? 'Customer' }} •
                                    {{ $focusOrder->category?->name ?? 'Service' }}
                                </p>
                                <p class="text-xs text-slate-500">Window: {{ $focusOrder->time_window ?? 'Flexible' }} •
                                    Priority: {{ ucfirst($focusOrder->priority) }}</p>
                            @else
                                <p class="text-sm text-slate-500 mt-1">No requests in queue.</p>
                            @endif
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-xs uppercase text-slate-500">Suggested technicians</p>
                            <div class="flex items-center gap-2">
                                <button class="px-3 py-1 rounded-full bg-slate-900 text-white text-xs">Auto-assign</button>
                                <button class="px-3 py-1 rounded-full border border-slate-300 bg-white text-xs">Manual
                                    override</button>
                            </div>
                        </div>
                        <div class="mt-3 space-y-3">
                            @foreach ($dispatchData['technicians']->sortBy('utilization')->values()->take(3) as $tech)
                                @php
                                    $index = $loop->index;
                                    $score = max(0, 100 - $tech['utilization'] - ($tech['has_overdue'] ? 20 : 0));
                                    $eta = $tech['utilization'] < 40 ? 25 : ($tech['utilization'] < 70 ? 45 : 70);
                                    $routeAdd = 10 + ($index * 6);
                                    $impact = $focusOrder && $focusItem
                                        ? ($focusItem['waiting_minutes'] + $eta < ($focusItem['sla_minutes'] ?? 9999) ? 'SLA safe' : 'SLA risk')
                                        : 'Assess SLA';
                                @endphp
                                <div class="dispatch-card-muted p-4">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $tech['user']->name }}</p>
                                            <p class="text-xs text-slate-500">Skills:
                                                {{ $skillSets[$index % count($skillSets)] }}
                                            </p>
                                            <p class="text-xs text-slate-500">Proximity:
                                                {{ $proximityBands[$index % count($proximityBands)] }} • Workload
                                                {{ $tech['utilization'] }}%
                                            </p>
                                            <p class="text-xs text-slate-500">Customer pref:
                                                {{ $focusOrder?->time_window ?? 'Flexible' }}
                                            </p>
                                        </div>
                                        <span class="dispatch-chip info">Fit {{ $score }}</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="dispatch-chip neutral">ETA {{ $eta }}m</span>
                                        <span
                                            class="dispatch-chip {{ $impact === 'SLA safe' ? 'good' : 'risk' }}">{{ $impact }}</span>
                                        <span class="dispatch-chip neutral">Route +{{ $routeAdd }}m</span>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between text-xs">
                                        <p class="text-slate-500">Impact: {{ $impact }} • Overtime risk
                                            {{ $tech['utilization'] > 85 ? 'High' : 'Low' }}
                                        </p>
                                        <button class="px-3 py-1 rounded-full bg-teal-600 text-white">Assign</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 dispatch-card-muted p-4">
                            <p class="text-xs uppercase text-slate-500">Route optimization</p>
                            <ul class="mt-2 space-y-2 text-xs text-slate-600">
                                <li>Bundle two downtown requests with {{ $firstTechName }} to cut travel by 18 minutes.</li>
                                <li>Swap 14:00 window to open a 90-minute gap for urgent call.</li>
                                <li>Stagger parts pickup before the harbor zone cluster.</li>
                            </ul>
                        </div>
                    </div>
                </div>
        @endif
        </div>

        @if ($roleKey === 'admin' && $adminData)
            @php
                $systemHealth = $adminData['systemHealth'] ?? [];
                $storagePercent = $systemHealth['storage']['percent'] ?? null;
                $storagePercent = $storagePercent !== null ? (int) $storagePercent : 0;
                $storageProjected = min(100, $storagePercent + 12);
                $dbMs = $systemHealth['db_ms'] ?? null;
                $responseMs = $systemHealth['response_ms'] ?? $dbMs ?? 0;
                $uptimePercent = $systemHealth['uptime_percent'] ?? 99.98;
                $sessions = $systemHealth['sessions'] ?? 0;
                $queueBacklog = $systemHealth['queue_backlog'] ?? 0;
                $failedJobs = $systemHealth['failed_jobs'] ?? 0;
                $statusMeta = [
                    'healthy' => [
                        'dot' => 'bg-emerald-500',
                        'pill' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'label' => 'Healthy',
                    ],
                    'warning' => [
                        'dot' => 'bg-amber-500',
                        'pill' => 'border-amber-200 bg-amber-50 text-amber-700',
                        'label' => 'Warning',
                    ],
                    'critical' => [
                        'dot' => 'bg-red-500',
                        'pill' => 'border-red-200 bg-red-50 text-red-700',
                        'label' => 'Critical',
                    ],
                ];
                $responseStatus = $responseMs > 450 ? 'critical' : ($responseMs > 250 ? 'warning' : 'healthy');
                $dbStatus = $dbMs === null ? 'warning' : ($dbMs > 150 ? 'warning' : 'healthy');
                $storageStatus = $storagePercent > 85 ? 'critical' : ($storagePercent > 70 ? 'warning' : 'healthy');
                $uptimeStatus = $uptimePercent < 99.9 ? 'warning' : 'healthy';
                $sessionStatus = $sessions > 500 ? 'warning' : 'healthy';
                $apiEndpoints = $systemHealth['api_endpoints'] ?? [
                    ['name' => 'Auth Gateway', 'status' => 'healthy', 'latency' => 122, 'uptime' => '99.99%'],
                    ['name' => 'Work Order API', 'status' => 'warning', 'latency' => 248, 'uptime' => '99.70%'],
                    ['name' => 'Billing Webhooks', 'status' => 'healthy', 'latency' => 164, 'uptime' => '99.95%'],
                    ['name' => 'Inventory Sync', 'status' => 'critical', 'latency' => 510, 'uptime' => '98.92%'],
                ];
                $securityOverview = $adminData['security']['overview'] ?? [
                    'failed_logins' => 42,
                    'geo_anomalies' => 3,
                    'data_access_flags' => 7,
                    'alert_flags' => 5,
                    'account_lockouts' => 2,
                ];
                $securityLog = $adminData['security']['suspicious_activity'] ?? [
                    ['time' => '09:12', 'actor' => 'system', 'event' => 'Bulk export attempt', 'location' => 'Frankfurt, DE', 'ip' => '85.204.32.11', 'status' => 'critical'],
                    ['time' => '10:03', 'actor' => 'maria.ortiz', 'event' => 'Unusual download volume', 'location' => 'Austin, US', 'ip' => '34.92.14.81', 'status' => 'warning'],
                    ['time' => '11:18', 'actor' => 'api-key-19', 'event' => 'Token reuse spike', 'location' => 'Tokyo, JP', 'ip' => '103.18.22.4', 'status' => 'warning'],
                    ['time' => '12:42', 'actor' => 'system', 'event' => 'Privilege elevation attempt', 'location' => 'Toronto, CA', 'ip' => '44.120.92.19', 'status' => 'critical'],
                ];
                $recentDeactivations = $adminData['security']['recent_deactivations'] ?? [
                    ['name' => 'Kara Boyd', 'time' => '2 hours ago', 'reason' => 'Role change'],
                    ['name' => 'Andre Lewis', 'time' => 'Yesterday', 'reason' => 'Contract ended'],
                ];
                $attentionQueue = $adminData['security']['attention'] ?? [
                    'inactive_90' => $adminData['staleUsers'] ?? 0,
                    'locked_accounts' => 6,
                    'access_queue' => 4,
                ];
                $business = $adminData['business'] ?? [];
                $revenueMonth = (float) ($business['revenue_month'] ?? 0);
                $revenueProjection = (float) ($business['revenue_projection'] ?? ($revenueMonth * 1.08));
                $revenueLastMonth = (float) ($business['revenue_last_month'] ?? ($revenueMonth * 0.92));
                $avgRevenuePerJob = (float) ($business['avg_revenue_per_job'] ?? 0);
                $compliance = $adminData['compliance'] ?? [];
                $complianceChecklist = $adminData['compliance']['checklist'] ?? [
                    ['label' => 'SOC 2 access review', 'status' => 'complete'],
                    ['label' => 'GDPR consent audit', 'status' => 'complete'],
                    ['label' => 'HIPAA retention check', 'status' => 'warning'],
                    ['label' => 'PCI quarterly scan', 'status' => 'complete'],
                    ['label' => 'Incident response drill', 'status' => 'pending'],
                ];
                $alerts = $adminData['alerts'] ?? [
                    ['title' => 'Critical: CPU saturation', 'detail' => 'API cluster running above 92% for 15 minutes.', 'time' => '5 min ago', 'status' => 'critical'],
                    ['title' => 'Maintenance schedule', 'detail' => 'Database patch window Sunday 02:00-03:00 UTC.', 'time' => 'Today', 'status' => 'warning'],
                    ['title' => 'License expiration warning', 'detail' => 'Monitoring agent license expires in 18 days.', 'time' => 'Today', 'status' => 'warning'],
                    ['title' => 'Storage capacity warning', 'detail' => 'Object storage projected to hit 85% in 21 days.', 'time' => '1 hour ago', 'status' => 'warning'],
                    ['title' => 'Integration failure', 'detail' => 'CRM sync failing for 2 accounts.', 'time' => '28 min ago', 'status' => 'critical'],
                ];
            @endphp
            <div class="space-y-6">
                @if($dashboardPreferences['visible_sections']['main_content'] ?? true)
                    <div
                        class="rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 via-white to-slate-100 p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-500">Administrator dashboard</p>
                                <h2 class="text-2xl font-semibold text-slate-900">System command center</h2>
                                <p class="text-sm text-slate-500">Live observability, security, and business intelligence.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2 text-xs text-slate-600">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    Live monitoring
                                </div>
                                <div class="text-xs text-slate-500">Last refresh {{ now()->format('H:i') }}</div>
                                <button
                                    class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm">
                                    Export all
                                </button>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-7">
                            <label class="flex flex-col gap-1 text-xs text-slate-500">
                                Start date
                                <input type="date" class="rounded-md border-slate-200 text-sm text-slate-700" />
                            </label>
                            <label class="flex flex-col gap-1 text-xs text-slate-500">
                                End date
                                <input type="date" class="rounded-md border-slate-200 text-sm text-slate-700" />
                            </label>
                            <label class="flex flex-col gap-1 text-xs text-slate-500">
                                Environment
                                <select class="rounded-md border-slate-200 text-sm text-slate-700">
                                    <option>Production</option>
                                    <option>Staging</option>
                                    <option>Sandbox</option>
                                </select>
                            </label>
                            <label class="flex flex-col gap-1 text-xs text-slate-500">
                                Region
                                <select class="rounded-md border-slate-200 text-sm text-slate-700">
                                    <option>EU West</option>
                                    <option>US Central</option>
                                    <option>APAC</option>
                                </select>
                            </label>
                            <label class="flex flex-col gap-1 text-xs text-slate-500">
                                Severity
                                <select class="rounded-md border-slate-200 text-sm text-slate-700">
                                    <option>All</option>
                                    <option>Critical</option>
                                    <option>Warning+</option>
                                    <option>Healthy only</option>
                                </select>
                            </label>
                            <label class="flex flex-col gap-1 text-xs text-slate-500">
                                View
                                <select class="rounded-md border-slate-200 text-sm text-slate-700">
                                    <option>All data</option>
                                    <option>System health</option>
                                    <option>Security</option>
                                    <option>Users</option>
                                    <option>Business</option>
                                </select>
                            </label>
                            <label class="flex flex-col gap-1 text-xs text-slate-500">
                                Search
                                <input type="search" class="rounded-md border-slate-200 text-sm text-slate-700"
                                    placeholder="Filter by host, user, or ticket" />
                            </label>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-white px-3 py-1 text-slate-600 shadow-sm">Production</span>
                                <span class="rounded-full bg-white px-3 py-1 text-slate-600 shadow-sm">EU West</span>
                                <span class="rounded-full bg-white px-3 py-1 text-slate-600 shadow-sm">Critical+</span>
                                <span class="rounded-full bg-white px-3 py-1 text-slate-600 shadow-sm">Last 30 days</span>
                            </div>
                            <button class="text-xs font-semibold text-slate-600">Reset filters</button>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">System Health Monitoring</h2>
                                <p class="text-xs text-slate-500">Auto refresh every 60s • Range: last 24 hours</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select class="rounded-md border-slate-200 text-xs text-slate-700">
                                    <option>Last 24h</option>
                                    <option>Last 7d</option>
                                    <option>Last 30d</option>
                                </select>
                                <button
                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Export</button>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:col-span-2">
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-500">
                                            <span class="h-2 w-2 rounded-full {{ $statusMeta[$uptimeStatus]['dot'] }}"></span>
                                            Server uptime
                                        </div>
                                        <span
                                            class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $statusMeta[$uptimeStatus]['pill'] }}">
                                            {{ $statusMeta[$uptimeStatus]['label'] }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex items-end justify-between gap-4">
                                        <div>
                                            <p class="text-2xl font-semibold text-slate-900">
                                                {{ number_format($uptimePercent, 2) }}%
                                            </p>
                                            <p class="text-xs text-slate-500">Historical trend: +0.04%</p>
                                        </div>
                                        <svg viewBox="0 0 120 40" class="h-10 w-24 text-emerald-500">
                                            <polyline fill="none" stroke="currentColor" stroke-width="2"
                                                points="0,30 20,28 40,20 60,22 80,15 100,18 120,10" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-500">
                                            <span class="h-2 w-2 rounded-full {{ $statusMeta[$responseStatus]['dot'] }}"></span>
                                            Response time
                                        </div>
                                        <span
                                            class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $statusMeta[$responseStatus]['pill'] }}">
                                            {{ $statusMeta[$responseStatus]['label'] }}
                                        </span>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-2xl font-semibold text-slate-900">{{ $responseMs }} ms</p>
                                        <p class="text-xs text-slate-500">P50 118 ms • P95 312 ms</p>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-500">
                                            <span class="h-2 w-2 rounded-full {{ $statusMeta[$dbStatus]['dot'] }}"></span>
                                            Database performance
                                        </div>
                                        <span
                                            class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $statusMeta[$dbStatus]['pill'] }}">
                                            {{ $statusMeta[$dbStatus]['label'] }}
                                        </span>
                                    </div>
                                    <div class="mt-3 space-y-1 text-sm text-slate-600">
                                        <p><span class="font-semibold text-slate-900">{{ $dbMs ?? 0 }} ms</span> avg query
                                            latency</p>
                                        <p>Connection pool <span class="font-semibold text-slate-900">72%</span> • Writes 1.2k/s
                                        </p>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-500">
                                            <span class="h-2 w-2 rounded-full {{ $statusMeta[$storageStatus]['dot'] }}"></span>
                                            Storage utilization
                                        </div>
                                        <span
                                            class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $statusMeta[$storageStatus]['pill'] }}">
                                            {{ $statusMeta[$storageStatus]['label'] }}
                                        </span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-sm text-slate-600">
                                            <span>Used {{ $storagePercent }}%</span>
                                            <span>Projected {{ $storageProjected }}% in 30d</span>
                                        </div>
                                        <div class="relative mt-2 h-2 rounded-full bg-slate-200">
                                            <div class="h-2 rounded-full bg-slate-700" style="width: {{ $storagePercent }}%;">
                                            </div>
                                            <span class="absolute top-[-6px] h-5 w-0.5 bg-amber-500"
                                                style="left: {{ $storageProjected }}%;"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-500">
                                            <span class="h-2 w-2 rounded-full {{ $statusMeta[$sessionStatus]['dot'] }}"></span>
                                            Active sessions
                                        </div>
                                        <span
                                            class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $statusMeta[$sessionStatus]['pill'] }}">
                                            {{ $statusMeta[$sessionStatus]['label'] }}
                                        </span>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-2xl font-semibold text-slate-900">{{ $sessions }}</p>
                                        <p class="text-xs text-slate-500">Peak 620 • 5 min trend stable</p>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-xs uppercase tracking-wide text-slate-500">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            API health summary
                                        </div>
                                        <span
                                            class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                            12 healthy
                                        </span>
                                    </div>
                                    <div class="mt-3 text-sm text-slate-600">
                                        <p>Degraded endpoints: <span class="font-semibold text-slate-900">2</span></p>
                                        <p>Queue backlog: <span class="font-semibold text-slate-900">{{ $queueBacklog }}</span>
                                            • Failed jobs: <span class="font-semibold text-slate-900">{{ $failedJobs }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-100 p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-900">API endpoint health status</h3>
                                    <button class="text-xs font-semibold text-slate-600">Export</button>
                                </div>
                                <div class="mt-4 space-y-3 text-sm">
                                    @foreach ($apiEndpoints as $endpoint)
                                        @php
                                            $endpointStatus = $endpoint['status'] ?? 'healthy';
                                            $endpointMeta = $statusMeta[$endpointStatus] ?? $statusMeta['healthy'];
                                        @endphp
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="h-2 w-2 rounded-full {{ $endpointMeta['dot'] }}"></span>
                                                    <p class="font-medium text-slate-900">{{ $endpoint['name'] }}</p>
                                                </div>
                                                <p class="text-xs text-slate-500">Uptime {{ $endpoint['uptime'] }} •
                                                    {{ $endpoint['latency'] }} ms
                                                </p>
                                            </div>
                                            <span
                                                class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $endpointMeta['pill'] }}">
                                                {{ $endpointMeta['label'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Security Monitoring</h2>
                                <p class="text-xs text-slate-500">Multi-region anomaly detection • Range: last 7 days</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select class="rounded-md border-slate-200 text-xs text-slate-700">
                                    <option>Last 7d</option>
                                    <option>Last 24h</option>
                                    <option>Last 30d</option>
                                </select>
                                <button
                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Export</button>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1">
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Failed logins</p>
                                    <p class="text-2xl font-semibold text-slate-900">{{ $securityOverview['failed_logins'] }}
                                    </p>
                                    <p class="text-xs text-slate-500">Spike detected in last 2 hours</p>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Geo anomalies</p>
                                    <p class="text-2xl font-semibold text-slate-900">{{ $securityOverview['geo_anomalies'] }}
                                    </p>
                                    <p class="text-xs text-slate-500">Access from 3 new regions</p>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Unusual data access</p>
                                    <p class="text-2xl font-semibold text-slate-900">
                                        {{ $securityOverview['data_access_flags'] }}
                                    </p>
                                    <p class="text-xs text-slate-500">Downloads above normal threshold</p>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Security alert flags</p>
                                    <p class="text-2xl font-semibold text-slate-900">{{ $securityOverview['alert_flags'] }}</p>
                                    <p class="text-xs text-slate-500">5 alerts require review</p>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Account lockouts</p>
                                    <p class="text-2xl font-semibold text-slate-900">{{ $securityOverview['account_lockouts'] }}
                                    </p>
                                    <p class="text-xs text-slate-500">Automatic lockout policy active</p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-100 p-4 lg:col-span-2">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-900">Suspicious activity log</h3>
                                    <button class="text-xs font-semibold text-slate-600">Export</button>
                                </div>
                                <div class="mt-4 space-y-3 text-sm">
                                    @foreach ($securityLog as $log)
                                        @php
                                            $logStatus = $log['status'] ?? 'warning';
                                            $logMeta = $statusMeta[$logStatus] ?? $statusMeta['warning'];
                                        @endphp
                                        <div
                                            class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-100 p-3">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="h-2 w-2 rounded-full {{ $logMeta['dot'] }}"></span>
                                                    <p class="font-medium text-slate-900">{{ $log['event'] }}</p>
                                                </div>
                                                <p class="text-xs text-slate-500">{{ $log['actor'] }} • {{ $log['location'] }} •
                                                    {{ $log['ip'] }}
                                                </p>
                                            </div>
                                            <div class="text-xs text-slate-500">{{ $log['time'] }}</div>
                                            <span
                                                class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $logMeta['pill'] }}">
                                                {{ $logMeta['label'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($dashboardPreferences['visible_sections']['charts'] ?? true)
                    <div class="grid grid-cols-1 gap-6">
                        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900 mb-4">Revenue Performance (30 Days)</h2>
                            <x-apex-chart type="area" :series="$charts['admin']['revenue']['series']" :options="[
                        'xaxis' => ['categories' => $charts['admin']['revenue']['categories']],
                        'colors' => ['#0f766e'],
                        'stroke' => ['curve' => 'smooth'],
                        'fill' => [
                            'type' => 'gradient',
                            'gradient' => [
                                'shadeIntensity' => 1,
                                'opacityFrom' => 0.7,
                                'opacityTo' => 0.3,
                            ]
                        ]
                    ]" />
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">User Management Summary</h2>
                                <p class="text-xs text-slate-500">Active accounts by role • Range: last 30 days</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select class="rounded-md border-slate-200 text-xs text-slate-700">
                                    <option>Last 30d</option>
                                    <option>Last 7d</option>
                                    <option>All time</option>
                                </select>
                                <button
                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Export</button>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3 text-sm text-slate-700">
                            @foreach ($adminData['roleCounts'] ?? [] as $role => $count)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">{{ RoleCatalog::label($role) }}
                                    </p>
                                    <p class="text-xl font-semibold text-slate-900">{{ $count }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Pending password resets</p>
                                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $adminData['passwordResets'] }}</p>
                                <p class="text-xs text-slate-500">Awaiting completion</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Accounts requiring attention</p>
                                <div class="mt-2 space-y-2 text-sm text-slate-700">
                                    <div class="flex items-center justify-between">
                                        <span>Inactive 90+ days</span>
                                        <span class="font-semibold">{{ $attentionQueue['inactive_90'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Locked accounts</span>
                                        <span class="font-semibold">{{ $attentionQueue['locked_accounts'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Access request queue</span>
                                        <span class="font-semibold">{{ $attentionQueue['access_queue'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Recent account creations</p>
                                <div class="mt-3 space-y-2 text-sm text-slate-700">
                                    @foreach ($adminData['recentUsers'] ?? [] as $recentUser)
                                        <div class="flex items-center justify-between">
                                            <span>{{ $recentUser->name }}</span>
                                            <span
                                                class="text-xs text-slate-500">{{ $recentUser->created_at?->diffForHumans() }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Recently deactivated</p>
                                <div class="mt-3 space-y-2 text-sm text-slate-700">
                                    @foreach ($recentDeactivations as $deactivation)
                                        <div class="flex items-center justify-between">
                                            <span>{{ $deactivation['name'] }}</span>
                                            <span class="text-xs text-slate-500">{{ $deactivation['time'] }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $deactivation['reason'] }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Business Intelligence Overview</h2>
                                <p class="text-xs text-slate-500">Revenue, volume, and profitability • Range: current month
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select class="rounded-md border-slate-200 text-xs text-slate-700">
                                    <option>Current month</option>
                                    <option>Last month</option>
                                    <option>Quarter to date</option>
                                </select>
                                <button
                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Export</button>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Revenue vs projection</p>
                                <p class="mt-2 text-2xl font-semibold text-slate-900">${{ number_format($revenueMonth, 2) }}
                                </p>
                                <div class="mt-3 space-y-1 text-sm text-slate-600">
                                    <div class="flex items-center justify-between">
                                        <span>Projection</span>
                                        <span class="font-semibold">${{ number_format($revenueProjection, 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Last month</span>
                                        <span class="font-semibold">${{ number_format($revenueLastMonth, 2) }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-slate-200">
                                    <div class="h-2 rounded-full bg-emerald-500"
                                        style="width: {{ $revenueProjection > 0 ? min(100, ($revenueMonth / $revenueProjection) * 100) : 0 }}%;">
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-100 p-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Job volume trends</p>
                                    <span class="text-xs text-slate-500">7d vs 7d</span>
                                </div>
                                <div class="mt-3">
                                    <svg viewBox="0 0 200 80" class="h-20 w-full text-slate-700">
                                        <polyline fill="none" stroke="currentColor" stroke-width="2"
                                            points="0,60 20,50 40,45 60,55 80,40 100,35 120,38 140,30 160,28 180,22 200,18" />
                                    </svg>
                                    <div class="mt-2 flex items-center justify-between text-sm text-slate-600">
                                        <span>Jobs (7d)</span>
                                        <span class="font-semibold">{{ $business['jobs_week'] ?? 0 }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm text-slate-600">
                                        <span>Prior 7d</span>
                                        <span class="font-semibold">{{ $business['jobs_week_prior'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Customer acquisition and churn</p>
                                <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-600">
                                    <div>
                                        <p class="text-xs text-slate-500">New customers</p>
                                        <p class="text-lg font-semibold text-slate-900">
                                            {{ $business['new_customers'] ?? 0 }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Churned customers</p>
                                        <p class="text-lg font-semibold text-slate-900">
                                            {{ $business['inactive_customers'] ?? 0 }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Net growth</p>
                                        <p class="text-lg font-semibold text-slate-900">
                                            {{ ($business['new_customers'] ?? 0) - ($business['inactive_customers'] ?? 0) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Churn rate</p>
                                        <p class="text-lg font-semibold text-slate-900">4.8%</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Service type breakdown</p>
                                <div class="mt-3 flex items-center gap-4">
                                    <div class="h-24 w-24 rounded-full border border-slate-100"
                                        style="background: conic-gradient(#0ea5e9 0 38%, #22c55e 38% 62%, #f97316 62% 82%, #ef4444 82% 100%);">
                                    </div>
                                    <div class="space-y-2 text-xs text-slate-600">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                            Preventive 38%
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            Repairs 24%
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                            Installations 20%
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                            Emergency 18%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-100 p-4 lg:col-span-2">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Average profitability per job</p>
                                <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-2xl font-semibold text-slate-900">
                                        ${{ number_format($avgRevenuePerJob, 2) }}</p>
                                    <span
                                        class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Margin
                                        28%</span>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">Includes labor, materials, and travel costs.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Compliance Dashboard</h2>
                                <p class="text-xs text-slate-500">Audit readiness and regulatory checkpoints</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select class="rounded-md border-slate-200 text-xs text-slate-700">
                                    <option>Last 30d</option>
                                    <option>Quarter to date</option>
                                    <option>Year to date</option>
                                </select>
                                <button
                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Export</button>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Data backup status</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ $compliance['backup_last_run'] ?? 'Not recorded' }}
                                </p>
                                <p class="text-xs text-slate-500">Last backup time</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Security patch status</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">Up to date</p>
                                <p class="text-xs text-slate-500">Next review in 12 days</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Audit log integrity</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">Verified</p>
                                <p class="text-xs text-slate-500">Last 24h: {{ $compliance['audit_events'] ?? 0 }} events
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Data privacy compliance</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">98% covered</p>
                                <p class="text-xs text-slate-500">PII encryption and consent tracking</p>
                            </div>
                        </div>

                        <div class="mt-6 rounded-xl border border-slate-100 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Regulatory requirement checklist</p>
                            <div class="mt-3 space-y-2 text-sm text-slate-700">
                                @foreach ($complianceChecklist as $item)
                                    @php
                                        $itemStatus = $item['status'] === 'complete' ? 'healthy' : ($item['status'] === 'warning' ? 'warning' : 'critical');
                                        $itemMeta = $statusMeta[$itemStatus];
                                    @endphp
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full {{ $itemMeta['dot'] }}"></span>
                                            <span>{{ $item['label'] }}</span>
                                        </div>
                                        <span
                                            class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $itemMeta['pill'] }}">
                                            {{ $itemMeta['label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">System Configuration Quick Access</h2>
                                <p class="text-xs text-slate-500">Launch core administration actions</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Export</button>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <button
                                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-700">User
                                account creation</button>
                            <button
                                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-700">System
                                settings adjustment</button>
                            <button
                                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-700">Database
                                maintenance</button>
                            <button
                                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-700">Data
                                export and backup</button>
                            <button
                                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-700">System
                                logs review</button>
                            <button
                                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-700">Integration
                                management</button>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Alert Center</h2>
                            <p class="text-xs text-slate-500">Critical notifications, maintenance schedules, and warnings
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <select class="rounded-md border-slate-200 text-xs text-slate-700">
                                <option>All alerts</option>
                                <option>Critical</option>
                                <option>Warnings</option>
                                <option>Maintenance</option>
                            </select>
                            <button
                                class="rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Export</button>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3 text-sm">
                        @foreach ($alerts as $alert)
                            @php
                                $alertStatus = $alert['status'] ?? 'warning';
                                $alertMeta = $statusMeta[$alertStatus] ?? $statusMeta['warning'];
                            @endphp
                            <div
                                class="flex flex-wrap items-start justify-between gap-4 rounded-xl border border-slate-100 p-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full {{ $alertMeta['dot'] }}"></span>
                                        <p class="font-semibold text-slate-900">{{ $alert['title'] }}</p>
                                    </div>
                                    <p class="text-xs text-slate-500">{{ $alert['detail'] }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-500">{{ $alert['time'] }}</span>
                                    <span class="rounded-full border px-2 py-0.5 text-xs font-medium {{ $alertMeta['pill'] }}">
                                        {{ $alertMeta['label'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($dashboardPreferences['visible_sections']['main_content'] ?? true)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($sections as $section)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">{{ $section['title'] }}</h2>
                            @if (!empty($section['action']))
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
                                            @if (!empty($item['badges']))
                                                @foreach ($item['badges'] as $badge)
                                                    <span
                                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $badge['class'] }}">
                                                        {{ $badge['label'] }}
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $item['meta'] }}</p>
                                    </div>
                                    @if (!empty($item['href']))
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
        @endif
    </div>
</div>