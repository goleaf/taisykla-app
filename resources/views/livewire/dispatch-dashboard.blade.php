<div class="min-h-screen bg-gray-100" wire:poll.10s>
    {{-- Header --}}
    <header class="bg-gradient-to-r from-slate-800 to-slate-900 text-white sticky top-0 z-40">
        <div class="px-4 lg:px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Dispatch Center</h1>
                    <p class="text-slate-400 text-sm">Real-time operations dashboard</p>
                </div>
                
                <div class="flex items-center gap-4">
                    {{-- Live indicator --}}
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-green-500/20 rounded-full">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-green-400 text-xs font-medium">LIVE</span>
                    </div>
                    
                    {{-- Current time --}}
                    <div class="text-slate-400 text-sm font-mono">
                        {{ now()->format('H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            class="fixed top-20 right-4 z-50 bg-green-600 text-white px-4 py-3 rounded-xl shadow-lg"
        >
            {{ session('success') }}
        </div>
    @endif

    <div class="p-4 lg:p-6 space-y-6">
        {{-- KPI Metrics Bar --}}
        <section class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
            {{-- Jobs Completed --}}
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Completed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $kpis['completed_today'] }}</p>
                        <div class="flex items-center gap-1 mt-1">
                            <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500" style="width: {{ min(100, $kpis['completion_percent']) }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400">/ {{ $kpis['target_today'] }}</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            {{-- In Progress --}}
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">In Progress</p>
                <p class="text-2xl font-bold text-indigo-600">{{ $kpis['in_progress'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Active jobs</p>
            </div>
            
            {{-- Pending --}}
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ $kpis['pending_count'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Unassigned</p>
            </div>
            
            {{-- Avg Response --}}
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Avg Response</p>
                <p class="text-2xl font-bold text-gray-900">{{ $kpis['avg_response_minutes'] }}<span class="text-sm font-normal text-gray-400">m</span></p>
                <p class="text-xs text-gray-400 mt-1">To assignment</p>
            </div>
            
            {{-- Avg Completion --}}
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Avg Job Time</p>
                <p class="text-2xl font-bold text-gray-900">{{ $kpis['avg_completion_minutes'] }}<span class="text-sm font-normal text-gray-400">m</span></p>
                <p class="text-xs text-gray-400 mt-1">Completion</p>
            </div>
            
            {{-- Utilization --}}
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Utilization</p>
                <p class="text-2xl font-bold {{ $kpis['utilization_rate'] > 80 ? 'text-green-600' : ($kpis['utilization_rate'] > 50 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $kpis['utilization_rate'] }}%
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $kpis['busy_technicians'] }}/{{ $kpis['total_technicians'] }} techs</p>
            </div>
            
            {{-- vs Historical --}}
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">vs 7-Day Avg</p>
                @php
                    $diff = $kpis['completed_today'] - $kpis['hist_avg_completed'];
                    $diffPercent = $kpis['hist_avg_completed'] > 0 ? round(($diff / $kpis['hist_avg_completed']) * 100) : 0;
                @endphp
                <p class="text-2xl font-bold {{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $diff >= 0 ? '+' : '' }}{{ $diffPercent }}%
                </p>
                <p class="text-xs text-gray-400 mt-1">Avg: {{ $kpis['hist_avg_completed'] }}/day</p>
            </div>
        </section>

        {{-- Main Dashboard Grid --}}
        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Left Column: Unassigned Queue --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold text-gray-900">Unassigned Queue</h2>
                            <p class="text-xs text-gray-500">{{ $unassignedRequests->count() }} requests waiting</p>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            {{-- Filter --}}
                            <select 
                                wire:model.live="requestFilter"
                                class="text-xs rounded-lg border-gray-300 py-1"
                            >
                                <option value="all">All</option>
                                <option value="urgent">Urgent Only</option>
                                <option value="overdue">SLA Overdue</option>
                            </select>
                            
                            {{-- Bulk actions --}}
                            @if (count($selectedRequests) > 0)
                                <button
                                    wire:click="openBulkModal"
                                    class="px-3 py-1 bg-indigo-600 text-white text-xs font-medium rounded-lg"
                                >
                                    Assign ({{ count($selectedRequests) }})
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <div class="max-h-[600px] overflow-y-auto divide-y divide-gray-100">
                        @forelse ($unassignedRequests as $request)
                            @php
                                $priorityColors = [
                                    'urgent' => 'border-l-red-500',
                                    'high' => 'border-l-orange-400',
                                    'standard' => 'border-l-blue-400',
                                    'routine' => 'border-l-green-400',
                                ];
                                $slaColors = [
                                    'breached' => 'bg-red-100 text-red-700',
                                    'critical' => 'bg-amber-100 text-amber-700',
                                    'warning' => 'bg-yellow-100 text-yellow-700',
                                    'ok' => 'bg-green-100 text-green-700',
                                ];
                            @endphp
                            <div class="p-3 border-l-4 {{ $priorityColors[$request->priority] ?? 'border-l-gray-300' }} hover:bg-gray-50 transition">
                                <div class="flex items-start gap-3">
                                    {{-- Checkbox --}}
                                    <input 
                                        type="checkbox"
                                        wire:click="toggleRequestSelection({{ $request->id }})"
                                        @checked(in_array($request->id, $selectedRequests))
                                        class="mt-1 rounded border-gray-300 text-indigo-600"
                                    >
                                    
                                    <div class="flex-1 min-w-0">
                                        {{-- Header row --}}
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-mono text-gray-400">#{{ $request->id }}</span>
                                            <div class="flex items-center gap-1">
                                                @if ($request->sla_status !== 'none')
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium {{ $slaColors[$request->sla_status] ?? '' }}">
                                                        @if ($request->sla_status === 'breached')
                                                            SLA BREACHED
                                                        @else
                                                            {{ abs($request->sla_minutes_remaining) }}m
                                                        @endif
                                                    </span>
                                                @endif
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                                    {{ ucfirst($request->priority) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        {{-- Customer --}}
                                        <p class="font-medium text-gray-900 truncate mt-1">
                                            {{ $request->organization?->name ?? 'Unknown' }}
                                        </p>
                                        
                                        {{-- Details --}}
                                        <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                                            <p class="truncate">{{ $request->subject ?? $request->category?->name ?? 'Service Request' }}</p>
                                            <div class="flex items-center gap-2">
                                                <span>⏱️ {{ floor($request->waiting_minutes / 60) }}h {{ $request->waiting_minutes % 60 }}m waiting</span>
                                                @if ($request->organization?->service_tier)
                                                    <span class="px-1 py-0.5 bg-purple-100 text-purple-700 rounded text-[10px]">
                                                        {{ ucfirst($request->organization->service_tier) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Quick Assign Button --}}
                                        <div class="flex gap-2 mt-2">
                                            <button
                                                wire:click="openAssignmentModal({{ $request->id }})"
                                                class="flex-1 py-1.5 text-xs font-medium bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition"
                                            >
                                                Assign
                                            </button>
                                            <a 
                                                href="{{ route('work-orders.show', $request) }}"
                                                class="px-3 py-1.5 text-xs text-gray-500 hover:bg-gray-100 rounded-lg transition"
                                                wire:navigate
                                            >
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p>All requests assigned!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Middle Column: Technician Board + Timeline --}}
            <div class="lg:col-span-1 space-y-4">
                {{-- Technician Status Board --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Technicians</h2>
                        <div class="flex items-center gap-2">
                            <select 
                                wire:model.live="technicianFilter"
                                class="text-xs rounded-lg border-gray-300 py-1"
                            >
                                <option value="all">All</option>
                                <option value="available">Available</option>
                                <option value="busy">Busy</option>
                            </select>
                            <div class="flex rounded-lg overflow-hidden border border-gray-200">
                                <button 
                                    wire:click="$set('technicianView', 'grid')"
                                    class="p-1.5 {{ $technicianView === 'grid' ? 'bg-gray-100' : '' }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                </button>
                                <button 
                                    wire:click="$set('technicianView', 'list')"
                                    class="p-1.5 {{ $technicianView === 'list' ? 'bg-gray-100' : '' }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 max-h-[400px] overflow-y-auto">
                        @if ($technicianView === 'grid')
                            <div class="grid grid-cols-2 gap-3">
                                @foreach ($technicians as $tech)
                                    @php
                                        $statusBg = [
                                            'green' => 'bg-green-100 border-green-300',
                                            'blue' => 'bg-blue-100 border-blue-300',
                                            'orange' => 'bg-orange-100 border-orange-300',
                                            'red' => 'bg-red-100 border-red-300',
                                            'yellow' => 'bg-yellow-100 border-yellow-300',
                                            'gray' => 'bg-gray-100 border-gray-300',
                                        ];
                                        $statusDot = [
                                            'green' => 'bg-green-500',
                                            'blue' => 'bg-blue-500',
                                            'orange' => 'bg-orange-500',
                                            'red' => 'bg-red-500',
                                            'yellow' => 'bg-yellow-500',
                                            'gray' => 'bg-gray-400',
                                        ];
                                    @endphp
                                    <button
                                        wire:click="selectTechnician({{ $tech->id }})"
                                        class="text-left p-3 rounded-xl border-2 transition {{ $statusBg[$tech->status_color] ?? 'bg-gray-100 border-gray-300' }} {{ $selectedTechnicianId === $tech->id ? 'ring-2 ring-indigo-500' : '' }}"
                                    >
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $statusDot[$tech->status_color] ?? 'bg-gray-400' }}"></span>
                                            <span class="font-medium text-gray-900 text-sm truncate">{{ $tech->name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-600 space-y-1">
                                            <p>{{ ucfirst(str_replace('_', ' ', $tech->availability_status)) }}</p>
                                            <p>📋 {{ $tech->todays_schedule->count() }} jobs today</p>
                                            <div class="flex items-center gap-1">
                                                <span>📊</span>
                                                <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                    <div class="h-full bg-indigo-500" style="width: {{ $tech->utilization }}%"></div>
                                                </div>
                                                <span>{{ $tech->utilization }}%</span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach ($technicians as $tech)
                                    @php
                                        $statusDot = [
                                            'green' => 'bg-green-500',
                                            'blue' => 'bg-blue-500',
                                            'orange' => 'bg-orange-500',
                                            'red' => 'bg-red-500',
                                            'yellow' => 'bg-yellow-500',
                                            'gray' => 'bg-gray-400',
                                        ];
                                    @endphp
                                    <button
                                        wire:click="selectTechnician({{ $tech->id }})"
                                        class="w-full text-left p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition {{ $selectedTechnicianId === $tech->id ? 'ring-2 ring-indigo-500' : '' }}"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="w-3 h-3 rounded-full {{ $statusDot[$tech->status_color] ?? 'bg-gray-400' }}"></span>
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $tech->name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $tech->current_job ? "Working: #{$tech->current_job->id}" : ucfirst(str_replace('_', ' ', $tech->availability_status)) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right text-xs text-gray-500">
                                                <p>{{ $tech->todays_schedule->count() }} jobs</p>
                                                <p>{{ $tech->utilization }}% util</p>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Schedule Timeline --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Today's Schedule</h2>
                    </div>
                    
                    <div class="p-2 overflow-x-auto">
                        <div class="min-w-[600px]">
                            {{-- Hour Headers --}}
                            <div class="flex border-b border-gray-200 pb-1 mb-2">
                                <div class="w-24 flex-shrink-0"></div>
                                @foreach ($timeline['hours'] as $hour)
                                    <div class="flex-1 text-center text-xs text-gray-400">{{ $hour }}:00</div>
                                @endforeach
                            </div>
                            
                            {{-- Technician Rows --}}
                            @foreach ($timeline['technicians'] as $tech)
                                <div class="flex items-center py-1.5 border-b border-gray-100 last:border-0">
                                    <div class="w-24 flex-shrink-0 pr-2">
                                        <p class="text-xs font-medium text-gray-700 truncate">{{ $tech['name'] }}</p>
                                    </div>
                                    <div class="flex-1 relative h-8 bg-gray-50 rounded">
                                        @foreach ($tech['jobs'] as $job)
                                            @php
                                                $startPercent = (($job['start_hour'] - 6) * 60 + $job['start_minute']) / (14 * 60) * 100;
                                                $widthPercent = ($job['duration'] / (14 * 60)) * 100;
                                                $jobColors = [
                                                    'urgent' => 'bg-red-400',
                                                    'high' => 'bg-orange-400',
                                                    'standard' => 'bg-blue-400',
                                                    'routine' => 'bg-green-400',
                                                ];
                                                $statusOpacity = match($job['status']) {
                                                    'completed' => 'opacity-50',
                                                    'in_progress' => 'ring-2 ring-indigo-500',
                                                    default => '',
                                                };
                                            @endphp
                                            <div 
                                                class="absolute top-1 h-6 rounded px-1 text-[10px] text-white font-medium truncate {{ $jobColors[$job['priority']] ?? 'bg-gray-400' }} {{ $statusOpacity }}"
                                                style="left: {{ max(0, min(100 - $widthPercent, $startPercent)) }}%; width: {{ min($widthPercent, 100 - $startPercent) }}%;"
                                                title="{{ $job['title'] }} ({{ $job['duration'] }}min)"
                                            >
                                                {{ $job['title'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Map + Alerts --}}
            <div class="lg:col-span-1 space-y-4">
                {{-- Geographic Heat Map --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Coverage Map</h2>
                    </div>
                    <div class="h-64">
                        <x-route-map 
                            :stops="collect($heatMapData['pending'])->map(fn($p) => [
                                'sequence' => $p['id'],
                                'label' => 'Request #' . $p['id'],
                                'lat' => $p['lat'],
                                'lng' => $p['lng'],
                                'priority' => $p['priority'],
                            ])->toArray()"
                            :current-location="null"
                            height="256px"
                        />
                    </div>
                    <div class="p-3 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>🔴 {{ collect($heatMapData['pending'])->where('priority', 'urgent')->count() }} urgent</span>
                            <span>🟠 {{ collect($heatMapData['pending'])->where('priority', 'high')->count() }} high</span>
                            <span>🔵 {{ collect($heatMapData['pending'])->where('priority', 'standard')->count() }} standard</span>
                            <span>👷 {{ count($heatMapData['technicians']) }} techs active</span>
                        </div>
                    </div>
                </div>

                {{-- Alert & Exception Panel --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-gray-900">Alerts</h2>
                            @if ($alerts->where('severity', 'critical')->count() > 0)
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded-full animate-pulse">
                                    {{ $alerts->where('severity', 'critical')->count() }} critical
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="max-h-[400px] overflow-y-auto divide-y divide-gray-100">
                        @forelse ($alerts as $alert)
                            @php
                                $alertBg = $alert['severity'] === 'critical' 
                                    ? 'bg-red-50 border-l-red-500' 
                                    : 'bg-amber-50 border-l-amber-500';
                                $alertIcon = match($alert['type']) {
                                    'overtime' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'sla_violation', 'sla_warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                                    'missed_checkin' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                                    default => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                };
                            @endphp
                            <div class="p-3 border-l-4 {{ $alertBg }}">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0 {{ $alert['severity'] === 'critical' ? 'text-red-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $alertIcon }}"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 text-sm">{{ $alert['title'] }}</p>
                                        <p class="text-xs text-gray-600 mt-0.5">{{ $alert['message'] }}</p>
                                        <div class="flex gap-2 mt-2">
                                            <button
                                                wire:click="openAssignmentModal({{ $alert['id'] }})"
                                                class="px-2 py-1 text-xs bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition"
                                            >
                                                View
                                            </button>
                                            <button
                                                wire:click="acknowledgeAlert({{ $alert['id'] }}, '{{ $alert['type'] }}')"
                                                class="px-2 py-1 text-xs text-gray-500 hover:bg-gray-100 rounded transition"
                                            >
                                                Dismiss
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm">No alerts</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assignment Modal --}}
    @if ($showAssignmentModal && $selectedRequestId)
        @php $selectedRequest = $unassignedRequests->firstWhere('id', $selectedRequestId); @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/75 transition-opacity" wire:click="closeAssignmentModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-xl font-bold text-gray-900">Assign Work Order</h3>
                        @if ($selectedRequest)
                            <p class="text-gray-500 mt-1">#{{ $selectedRequest->id }} - {{ $selectedRequest->organization?->name }}</p>
                        @endif
                    </div>
                    
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        {{-- Request Summary --}}
                        @if ($selectedRequest)
                            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                                <h4 class="font-medium text-gray-900 mb-2">{{ $selectedRequest->subject ?? 'Service Request' }}</h4>
                                <p class="text-sm text-gray-600">{{ $selectedRequest->description ?? 'No description' }}</p>
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <span class="px-2 py-0.5 bg-{{ $selectedRequest->priority === 'urgent' ? 'red' : ($selectedRequest->priority === 'high' ? 'orange' : 'blue') }}-100 text-{{ $selectedRequest->priority === 'urgent' ? 'red' : ($selectedRequest->priority === 'high' ? 'orange' : 'blue') }}-700 text-xs rounded-full">
                                        {{ ucfirst($selectedRequest->priority) }}
                                    </span>
                                    @if ($selectedRequest->location_address)
                                        <span class="text-xs text-gray-500">📍 {{ $selectedRequest->location_address }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        {{-- AI Recommendations --}}
                        @if ($recommendations && count($recommendations) > 0)
                            <h4 class="font-medium text-gray-900 mb-3">Recommended Technicians</h4>
                            <div class="space-y-2 mb-6">
                                @foreach ($recommendations as $rec)
                                    <button
                                        wire:click="$set('assignToTechnicianId', {{ $rec['technician']->id }})"
                                        class="w-full text-left p-4 rounded-xl border-2 transition {{ $assignToTechnicianId === $rec['technician']->id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                                    {{ strtoupper(substr($rec['technician']->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $rec['technician']->name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ ucfirst($rec['availability_status'] ?? 'unknown') }} • 
                                                        {{ $rec['current_workload'] }} jobs today
                                                        @if ($rec['estimated_travel'])
                                                            • ~{{ $rec['estimated_travel'] }}min away
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold
                                                    {{ $rec['score'] >= 70 ? 'bg-green-100 text-green-700' : ($rec['score'] >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') }}">
                                                    {{ $rec['score'] }}
                                                </div>
                                            </div>
                                        </div>
                                        @if (!empty($rec['reasons']))
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach ($rec['reasons'] as $reason)
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded">{{ $reason }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        
                        {{-- All Technicians --}}
                        <h4 class="font-medium text-gray-900 mb-3">All Technicians</h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($technicians as $tech)
                                <button
                                    wire:click="$set('assignToTechnicianId', {{ $tech->id }})"
                                    class="p-3 rounded-lg border text-left transition {{ $assignToTechnicianId === $tech->id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <p class="font-medium text-gray-900 text-sm">{{ $tech->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $tech->todays_schedule->count() }} jobs • {{ $tech->utilization }}% util</p>
                                </button>
                            @endforeach
                        </div>
                        
                        {{-- Schedule Options --}}
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h4 class="font-medium text-gray-900 mb-3">Schedule For</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Date</label>
                                    <input type="date" wire:model="scheduledDate" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Time</label>
                                    <input type="time" wire:model="scheduledTime" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-gray-50 flex gap-3">
                        <button
                            wire:click="closeAssignmentModal"
                            class="flex-1 py-3 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-100 transition"
                        >
                            Cancel
                        </button>
                        <button
                            wire:click="confirmAssignment"
                            @disabled(!$assignToTechnicianId)
                            class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 disabled:opacity-50 transition"
                        >
                            Assign
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Assignment Modal --}}
    @if ($showBulkModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/75 transition-opacity" wire:click="$set('showBulkModal', false)"></div>
                
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-xl font-bold text-gray-900">Bulk Assignment</h3>
                        <p class="text-gray-500 mt-1">Assign {{ count($selectedRequests) }} work orders</p>
                    </div>
                    
                    <div class="p-6">
                        <h4 class="font-medium text-gray-900 mb-3">Select Technician</h4>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach ($technicians as $tech)
                                <button
                                    wire:click="$set('assignToTechnicianId', {{ $tech->id }})"
                                    class="w-full p-3 rounded-lg border text-left transition {{ $assignToTechnicianId === $tech->id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <p class="font-medium text-gray-900">{{ $tech->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $tech->remaining_capacity }} slots available</p>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="p-6 bg-gray-50 flex gap-3">
                        <button
                            wire:click="$set('showBulkModal', false)"
                            class="flex-1 py-3 border border-gray-300 rounded-xl font-medium text-gray-700"
                        >
                            Cancel
                        </button>
                        <button
                            wire:click="bulkAssign"
                            @disabled(!$assignToTechnicianId)
                            class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-medium disabled:opacity-50"
                        >
                            Assign All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
