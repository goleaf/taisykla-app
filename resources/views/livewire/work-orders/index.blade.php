<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Work Orders</h1>
                <p class="text-sm text-gray-500">Track service requests across the lifecycle.</p>
            </div>
            @if ($canCreate)
            <a href="{{ route('work-orders.create') }}" wire:navigate
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow-lg shadow-indigo-200 hover:from-indigo-700 hover:to-purple-700 transition-all"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Work Order
            </a>
        @endif
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Total</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['total'] ?? 0 }}</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Active</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['active'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">{{ $summary['on_hold'] ?? 0 }} on hold</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Completed</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['completed'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">{{ $summary['closed'] ?? 0 }} closed</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Urgent</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['urgent'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">{{ $summary['canceled'] ?? 0 }} canceled</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-2">
                    <label class="text-xs text-gray-500">Search</label>
                    <input
                        type="text"
                        wire:model.debounce.300ms="search"
                        class="mt-1 w-full rounded-md border-gray-300"
                        placeholder="Subject, description, org, or equipment"
                    />
                </div>
                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <select wire:model="statusFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="all">All statuses</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Priority</label>
                    <select wire:model="priorityFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="all">All priorities</option>
                        @foreach ($priorityOptions as $priority)
                            <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Category</label>
                    <select wire:model="categoryFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if (! $isClient)
                    <div>
                        <label class="text-xs text-gray-500">Organization</label>
                        <select wire:model="organizationFilter" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">All organizations</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($canAssign)
                    <div>
                        <label class="text-xs text-gray-500">Technician</label>
                        <select wire:model="technicianFilter" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">All technicians</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <div class="flex items-center gap-3">
                    <button type="button" class="text-indigo-600" wire:click="clearFilters">Clear filters</button>
                    <span>{{ $workOrders->total() }} work orders</span>
                </div>
                
                {{-- View Switcher --}}
                <div class="flex items-center gap-1 rounded-lg bg-gray-100 p-1">
                    @foreach ($viewOptions as $viewOption)
                        <button
                            type="button"
                            wire:click="$set('view', '{{ $viewOption }}')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition {{ $view === $viewOption ? 'bg-white shadow text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}"
                        >
                            @if ($viewOption === 'list')
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            @elseif ($viewOption === 'board')
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                            @elseif ($viewOption === 'calendar')
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @elseif ($viewOption === 'map')
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @endif
                            {{ ucfirst($viewOption) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Kanban Board View --}}
        @if ($view === 'board' && $boardColumns)
            <div class="overflow-x-auto pb-4">
                <div class="flex gap-4 min-w-max">
                    @foreach ($boardColumns as $status => $column)
                        @php
                            $columnColors = [
                                'submitted' => 'border-t-gray-400',
                                'assigned' => 'border-t-blue-400',
                                'in_progress' => 'border-t-indigo-500',
                                'on_hold' => 'border-t-yellow-400',
                                'awaiting_approval' => 'border-t-orange-400',
                                'completed' => 'border-t-green-500',
                                'closed' => 'border-t-emerald-600',
                            ];
                            $borderColor = $columnColors[$status] ?? 'border-t-gray-300';
                        @endphp
                        <div class="w-72 flex-shrink-0 bg-gray-50 rounded-lg border-t-4 {{ $borderColor }}">
                            <div class="p-3 border-b border-gray-200 bg-white rounded-t-lg">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-700">{{ $column['label'] }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                        {{ $column['items']->count() }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-2 space-y-2 max-h-[70vh] overflow-y-auto">
                                @forelse ($column['items'] as $workOrder)
                                    @php
                                        $priorityColors = [
                                            'urgent' => 'border-l-red-500 bg-red-50',
                                            'high' => 'border-l-orange-400 bg-orange-50',
                                            'standard' => 'border-l-blue-400 bg-white',
                                        ];
                                        $cardStyle = $priorityColors[$workOrder->priority] ?? 'border-l-gray-300 bg-white';
                                    @endphp
                                    <div 
                                        class="p-3 rounded-lg shadow-sm border border-gray-200 border-l-4 {{ $cardStyle }} cursor-pointer hover:shadow-md transition-shadow"
                                        wire:key="board-card-{{ $workOrder->id }}"
                                    >
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-gray-500">#{{ $workOrder->id }}</p>
                                                <p class="font-medium text-gray-900 text-sm truncate">{{ $workOrder->subject }}</p>
                                            </div>
                                            @if ($workOrder->priority === 'urgent')
                                                <span class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1 truncate">{{ $workOrder->organization?->name ?? 'No organization' }}</p>
                                        <div class="mt-2 flex items-center justify-between text-xs">
                                            @if ($workOrder->assignedTo)
                                                <span class="inline-flex items-center gap-1 text-gray-500">
                                                    <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[10px] font-medium">
                                                        {{ strtoupper(substr($workOrder->assignedTo->name, 0, 1)) }}
                                                    </span>
                                                    {{ Str::words($workOrder->assignedTo->name, 1, '') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">Unassigned</span>
                                            @endif
                                            <a href="{{ route('work-orders.show', $workOrder) }}" class="text-indigo-600 hover:underline" wire:navigate>View</a>
                                        </div>
                                        @if ($workOrder->scheduled_start_at)
                                            <p class="mt-2 text-xs text-gray-400">
                                                📅 {{ $workOrder->scheduled_start_at->format('M d, H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-xs text-gray-400">
                                        No work orders
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif ($view === 'map' && $mapOrders)
            {{-- Map View --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                <x-route-map
                    :stops="collect($mapOrders)->map(fn($order) => [
                        'sequence' => $loop->iteration ?? 1,
                        'label' => $order['subject'] ?? 'Work Order',
                        'address' => $order['location_address'] ?? null,
                        'lat' => $order['lat'] ?? null,
                        'lng' => $order['lng'] ?? null,
                        'priority' => $order['priority'] ?? 'standard',
                    ])->toArray()"
                    height="500px"
                />
                <p class="mt-3 text-xs text-gray-500 text-center">{{ count($mapOrders) }} work orders with location data</p>
            </div>
        @elseif ($view === 'calendar' && $calendarGroups)
            {{-- Calendar View (simplified week view) --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                <div class="text-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Schedule Overview</h3>
                    <p class="text-sm text-gray-500">Work orders grouped by scheduled date</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                    @foreach ($calendarGroups as $group)
                        @php
                            $date = $group['date'] ?? 'Unscheduled';
                            $orders = $group['items'] ?? collect();
                            $capacity = $group['capacity'] ?? 'empty';
                            $isValidDate = $date !== 'Unscheduled' && strtotime($date);
                            $dayName = $isValidDate ? \Carbon\Carbon::parse($date)->format('D') : '';
                            $dayNum = $isValidDate ? \Carbon\Carbon::parse($date)->format('j') : '';
                            $monthName = $isValidDate ? \Carbon\Carbon::parse($date)->format('M') : '';
                            $isToday = $isValidDate && \Carbon\Carbon::parse($date)->isToday();
                            $capacityColors = [
                                'over' => 'border-red-400 bg-red-50',
                                'tight' => 'border-yellow-400 bg-yellow-50',
                                'open' => 'border-green-400 bg-green-50',
                                'empty' => 'border-gray-200 bg-white',
                            ];
                            $borderStyle = $isToday ? 'border-indigo-500 bg-indigo-50' : ($capacityColors[$capacity] ?? 'border-gray-200');
                        @endphp
                        <div class="border-2 rounded-lg p-3 {{ $borderStyle }}">
                            <div class="text-center mb-2">
                                @if ($isValidDate)
                                    <p class="text-xs text-gray-500">{{ $dayName }}, {{ $monthName }}</p>
                                    <p class="text-xl font-bold {{ $isToday ? 'text-indigo-600' : 'text-gray-900' }}">{{ $dayNum }}</p>
                                @else
                                    <p class="text-sm font-medium text-gray-600">{{ $date }}</p>
                                @endif
                                <p class="text-xs {{ $capacity === 'over' ? 'text-red-600' : 'text-gray-400' }}">{{ $orders->count() }} {{ Str::plural('order', $orders->count()) }}</p>
                            </div>
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                @forelse ($orders as $order)
                                    @php
                                        $priorityStyle = match ($order->priority) {
                                            'urgent' => 'bg-red-100 text-red-700 border-l-2 border-red-500',
                                            'high' => 'bg-orange-100 text-orange-700 border-l-2 border-orange-400',
                                            default => 'bg-blue-50 text-blue-700 border-l-2 border-blue-400',
                                        };
                                    @endphp
                                    <a href="{{ route('work-orders.show', $order) }}" 
                                       class="block p-1.5 rounded text-xs truncate {{ $priorityStyle }}"
                                       wire:navigate
                                       title="{{ $order->subject }}"
                                    >
                                        {{ $order->scheduled_start_at?->format('H:i') ?? '—' }} {{ Str::limit($order->subject, 18) }}
                                    </a>
                                @empty
                                    <p class="text-xs text-gray-400 text-center py-2">No orders</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
        {{-- List View (existing) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="{{ $canCreate ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                    <div class="divide-y divide-gray-200">
                        @forelse ($workOrders as $workOrder)
                            @php
                                $statusLabel = ucfirst(str_replace('_', ' ', $workOrder->status));
                                $priorityLabel = ucfirst($workOrder->priority);
                                $statusBadge = match ($workOrder->status) {
                                    'submitted' => 'bg-gray-100 text-gray-700',
                                    'assigned' => 'bg-blue-100 text-blue-700',
                                    'in_progress' => 'bg-indigo-100 text-indigo-700',
                                    'on_hold' => 'bg-yellow-100 text-yellow-700',
                                    'completed' => 'bg-green-100 text-green-700',
                                    'closed' => 'bg-green-100 text-green-700',
                                    'canceled' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                                $priorityBadge = match ($workOrder->priority) {
                                    'urgent' => 'bg-red-100 text-red-700',
                                    'high' => 'bg-orange-100 text-orange-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                                $sla = $slaSummaries[$workOrder->id] ?? null;
                                $slaColor = 'text-gray-500';
                                if ($sla) {
                                    $slaColor = match ($sla['status']) {
                                        'on_track' => 'text-green-600',
                                        'at_risk' => 'text-yellow-600',
                                        'breached' => 'text-red-600',
                                        default => 'text-gray-500',
                                    };
                                }
                            @endphp
                            <div class="p-5" wire:key="work-order-{{ $workOrder->id }}">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-semibold text-gray-900">#{{ $workOrder->id }} {{ $workOrder->subject }}</h3>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $statusBadge }}">
                                                {{ $statusLabel }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $priorityBadge }}">
                                                {{ $priorityLabel }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <span>Org: {{ $workOrder->organization?->name ?? '—' }}</span>
                                            <span>Equipment: {{ $workOrder->equipment?->name ?? '—' }}</span>
                                            <span>Category: {{ $workOrder->category?->name ?? '—' }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <span>Requested: {{ $workOrder->requested_at?->format('M d, H:i') ?? '—' }}</span>
                                            <span>Scheduled: {{ $workOrder->scheduled_start_at?->format('M d, H:i') ?? '—' }}</span>
                                            <span>Assigned: {{ $workOrder->assignedTo?->name ?? 'Unassigned' }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            {{ \Illuminate\Support\Str::limit($workOrder->description ?? 'No description provided.', 120) }}
                                        </p>
                                    </div>
                                    <div class="min-w-[210px] space-y-3 text-sm">
                                        <div>
                                            <p class="text-xs text-gray-500">Status</p>
                                            @if ($canUpdateStatus)
                                                <select class="mt-1 w-full rounded-md border-gray-300 text-sm" wire:change="updateStatus({{ $workOrder->id }}, $event.target.value)">
                                                    @foreach ($statusOptions as $status)
                                                        <option value="{{ $status }}" @selected($workOrder->status === $status)>
                                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="mt-1 text-gray-700">{{ $statusLabel }}</div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Assigned</p>
                                            @if ($canAssign)
                                                <select class="mt-1 w-full rounded-md border-gray-300 text-sm" wire:change="assignTo({{ $workOrder->id }}, $event.target.value)">
                                                    <option value="">Unassigned</option>
                                                    @foreach ($technicians as $technician)
                                                        <option value="{{ $technician->id }}" @selected($workOrder->assigned_to_user_id === $technician->id)>
                                                            {{ $technician->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="mt-1 text-gray-700">{{ $workOrder->assignedTo?->name ?? 'Unassigned' }}</div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">SLA</p>
                                            @if ($sla)
                                                <div class="font-medium {{ $slaColor }}">{{ ucfirst(str_replace('_', ' ', $sla['status'])) }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $sla['response_minutes'] ?? '—' }} / {{ $sla['target_minutes'] ?? '—' }} min
                                                </div>
                                            @else
                                                <div class="text-gray-500">—</div>
                                            @endif
                                        </div>
                                        <a href="{{ route('work-orders.show', $workOrder) }}" class="inline-flex items-center text-indigo-600" wire:navigate>
                                            View details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">No work orders match the current filters.</div>
                        @endforelse
                    </div>
                    <div class="p-4">
                        {{ $workOrders->links() }}
                    </div>
                </div>
            </div>

            @if ($canCreate)
                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">New Work Order</h2>
                            @if ($showForm)
                                <button class="text-sm text-gray-500" type="button" wire:click="cancelForm">Close</button>
                            @endif
                        </div>

                        @if ($showForm)
                            <form wire:submit.prevent="saveWorkOrder" class="space-y-3">
                                @if (! $isClient)
                                    <div>
                                        <label class="text-xs text-gray-500">Organization</label>
                                        <select wire:model="form.organization_id" class="mt-1 w-full rounded-md border-gray-300">
                                            <option value="">Select organization</option>
                                            @foreach ($organizations as $organization)
                                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.organization_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div>
                                    <label class="text-xs text-gray-500">Equipment</label>
                                    <select wire:model="form.equipment_id" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select equipment</option>
                                        @foreach ($equipment as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.equipment_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Category</label>
                                    <select wire:model="form.category_id" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.category_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Priority</label>
                                    <select wire:model="form.priority" class="mt-1 w-full rounded-md border-gray-300">
                                        @foreach ($priorityOptions as $priority)
                                            <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.priority') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Subject</label>
                                    <input type="text" wire:model="form.subject" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('form.subject') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Description</label>
                                    <textarea wire:model="form.description" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                                    @error('form.description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Scheduled Start</label>
                                        <input type="datetime-local" wire:model="form.scheduled_start_at" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.scheduled_start_at') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Scheduled End</label>
                                        <input type="datetime-local" wire:model="form.scheduled_end_at" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.scheduled_end_at') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Time Window</label>
                                    <input type="text" wire:model="form.time_window" class="mt-1 w-full rounded-md border-gray-300" placeholder="Morning, Afternoon" />
                                    @error('form.time_window') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                @if ($canAssign)
                                    <div>
                                        <label class="text-xs text-gray-500">Assign Technician</label>
                                        <select wire:model="form.assigned_to_user_id" class="mt-1 w-full rounded-md border-gray-300">
                                            <option value="">Unassigned</option>
                                            @foreach ($technicians as $technician)
                                                <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.assigned_to_user_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div class="flex items-center gap-3">
                                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create</button>
                                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="cancelForm">Cancel</button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm text-gray-500">Select "New Work Order" to create a service request.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        @endif
    </div>
</div>
