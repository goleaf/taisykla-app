<div 
    class="min-h-screen bg-gray-100 pb-24"
    x-data="{
        elapsedSeconds: @entangle('elapsedSeconds'),
        jobStartTime: @js($jobStartTime?->timestamp),
        timerInterval: null,
        
        init() {
            if (this.jobStartTime) {
                this.startTimer();
            }
            this.requestLocation();
        },
        
        startTimer() {
            this.updateElapsed();
            this.timerInterval = setInterval(() => this.updateElapsed(), 1000);
        },
        
        updateElapsed() {
            if (this.jobStartTime) {
                this.elapsedSeconds = Math.floor(Date.now() / 1000) - this.jobStartTime;
            }
        },
        
        formatTime(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            if (hrs > 0) {
                return `${hrs}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },
        
        requestLocation() {
            if ('geolocation' in navigator) {
                navigator.geolocation.watchPosition(
                    (pos) => {
                        $wire.dispatch('location-updated', { 
                            lat: pos.coords.latitude, 
                            lng: pos.coords.longitude 
                        });
                    },
                    (err) => console.log('Location error:', err),
                    { enableHighAccuracy: true, maximumAge: 30000 }
                );
            }
        }
    }"
    wire:poll.30s
>
    {{-- Header --}}
    <header class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white sticky top-0 z-40 safe-area-inset">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Field Dashboard</h1>
                    <p class="text-indigo-200 text-sm">{{ now()->format('l, M d') }}</p>
                </div>
                
                {{-- Unread Messages Badge --}}
                <div class="flex items-center gap-3">
                    <a href="#messages" class="relative p-2 rounded-full bg-white/10 hover:bg-white/20 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        @if ($unreadCount > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-xs flex items-center justify-center font-bold">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                    
                    {{-- Emergency Button --}}
                    <button 
                        wire:click="triggerEmergency"
                        class="p-2 rounded-full bg-red-500/80 hover:bg-red-500 transition"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Alerts Panel --}}
    @if ($alerts->isNotEmpty())
        <div class="px-4 py-2 space-y-2">
            @foreach ($alerts as $alert)
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $alert['type'] === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if ($alert['icon'] === 'exclamation')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                    <span class="text-sm font-medium">{{ $alert['message'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Status Controls --}}
    <section class="px-4 py-4">
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Your Status</h3>
            <div class="grid grid-cols-3 gap-2">
                @php
                    $statuses = [
                        'available' => ['label' => 'Available', 'color' => 'green', 'icon' => 'M5 13l4 4L19 7'],
                        'traveling' => ['label' => 'Traveling', 'color' => 'blue', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                        'on_site' => ['label' => 'On Site', 'color' => 'indigo', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
                        'working' => ['label' => 'Working', 'color' => 'purple', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                        'break' => ['label' => 'On Break', 'color' => 'amber', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'off_duty' => ['label' => 'Off Duty', 'color' => 'gray', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                    ];
                @endphp
                
                @foreach ($statuses as $key => $status)
                    <button
                        wire:click="updateStatus('{{ $key }}')"
                        class="flex flex-col items-center justify-center p-3 rounded-xl transition-all min-h-[72px]
                            {{ $currentStatus === $key 
                                ? "bg-{$status['color']}-500 text-white shadow-lg scale-105" 
                                : "bg-{$status['color']}-50 text-{$status['color']}-700 hover:bg-{$status['color']}-100" }}"
                    >
                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $status['icon'] }}"/>
                        </svg>
                        <span class="text-xs font-medium">{{ $status['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Active Job Timer --}}
    @if ($activeJobId)
        @php $activeJob = $workQueue->firstWhere('id', $activeJobId); @endphp
        @if ($activeJob)
            <section class="px-4 pb-4">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-2xl shadow-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-indigo-100 text-sm font-medium">Active Job Timer</span>
                        <span class="px-2 py-0.5 bg-white/20 rounded text-xs">#{{ $activeJob->id }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-bold font-mono" x-text="formatTime(elapsedSeconds)">0:00</p>
                            @if ($activeJob->estimated_minutes)
                                <p class="text-indigo-200 text-sm mt-1">
                                    Est. {{ $activeJob->estimated_minutes }} min
                                    <span 
                                        x-show="elapsedSeconds > {{ $activeJob->estimated_minutes * 60 }}"
                                        class="text-amber-300 font-medium"
                                    >
                                        (Over time!)
                                    </span>
                                </p>
                            @endif
                        </div>
                        
                        <button
                            wire:click="checkOut({{ $activeJob->id }})"
                            class="px-6 py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-indigo-50 transition"
                        >
                            Complete Job
                        </button>
                    </div>
                    
                    {{-- Progress bar --}}
                    @if ($activeJob->estimated_minutes)
                        <div class="mt-4 h-2 bg-white/20 rounded-full overflow-hidden">
                            <div 
                                class="h-full transition-all duration-1000"
                                :class="elapsedSeconds > {{ $activeJob->estimated_minutes * 60 }} ? 'bg-amber-400' : 'bg-white'"
                                :style="'width: ' + Math.min(100, (elapsedSeconds / {{ $activeJob->estimated_minutes * 60 }}) * 100) + '%'"
                            ></div>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endif

    {{-- Time Summary --}}
    <section class="px-4 pb-4">
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Today's Summary</h3>
            <div class="grid grid-cols-4 gap-2 text-center">
                <div>
                    <p class="text-2xl font-bold text-indigo-600">{{ floor($timeSummary['work'] / 60) }}h</p>
                    <p class="text-xs text-gray-500">Work</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-blue-600">{{ floor($timeSummary['travel'] / 60) }}h</p>
                    <p class="text-xs text-gray-500">Travel</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-600">{{ $timeSummary['break'] }}m</p>
                    <p class="text-xs text-gray-500">Break</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600">{{ floor($timeSummary['billable'] / 60) }}h</p>
                    <p class="text-xs text-gray-500">Billable</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Interactive Route Map --}}
    <section class="px-4 pb-4">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Route Map</h3>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span>{{ $routeSummary['total_stops'] }} stops</span>
                    <span>•</span>
                    <span>{{ round($routeSummary['total_distance_km'], 1) }} km</span>
                </div>
            </div>
            
            <div class="h-64">
                <x-route-map 
                    :stops="$mapStops" 
                    :current-location="$currentLocation"
                    height="256px"
                    :interactive="true"
                />
            </div>
            
            @if ($conflicts && count($conflicts) > 0)
                <div class="p-3 bg-amber-50 border-t border-amber-100">
                    <div class="flex items-center gap-2 text-amber-700 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ count($conflicts) }} scheduling conflict(s) detected</span>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Today's Work Queue --}}
    <section class="px-4 pb-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-900">Today's Jobs</h3>
            <span class="text-sm text-gray-500">{{ $workQueue->count() }} assignments</span>
        </div>
        
        <div class="space-y-3">
            @forelse ($workQueue as $job)
                @php
                    $priorityColors = [
                        'urgent' => 'border-l-red-500 bg-red-50',
                        'high' => 'border-l-orange-400 bg-orange-50',
                        'standard' => 'border-l-blue-400 bg-white',
                        'routine' => 'border-l-green-400 bg-green-50',
                    ];
                    $cardStyle = $priorityColors[$job->priority] ?? 'border-l-gray-300 bg-white';
                    $isExpanded = $expandedJobId === $job->id;
                    $isActive = $activeJobId === $job->id;
                @endphp
                
                <div class="border-l-4 rounded-2xl shadow-sm {{ $cardStyle }} {{ $isActive ? 'ring-2 ring-indigo-500' : '' }}">
                    {{-- Collapsed Card Header --}}
                    <div 
                        class="p-4 cursor-pointer"
                        wire:click="toggleJobExpand({{ $job->id }})"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                {{-- Priority & Time --}}
                                <div class="flex items-center gap-2 mb-1">
                                    @if ($job->priority === 'urgent')
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                        </span>
                                    @endif
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                        {{ $job->priority === 'urgent' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $job->priority === 'high' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $job->priority === 'standard' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $job->priority === 'routine' ? 'bg-green-100 text-green-700' : '' }}
                                    ">
                                        {{ ucfirst($job->priority) }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $job->scheduled_start_at?->format('g:i A') }}
                                        @if ($job->time_window)
                                            ({{ $job->time_window }})
                                        @endif
                                    </span>
                                </div>
                                
                                {{-- Customer & Location --}}
                                <h4 class="font-semibold text-gray-900 truncate">
                                    {{ $job->organization?->name ?? 'Unknown Customer' }}
                                </h4>
                                <p class="text-sm text-gray-500 truncate">
                                    {{ $job->location_address ?? 'No location' }}
                                </p>
                                
                                {{-- Service Type & Duration --}}
                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                    @if ($job->category)
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            {{ $job->category->name ?? 'General' }}
                                        </span>
                                    @endif
                                    @if ($job->estimated_minutes)
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $job->estimated_minutes }} min
                                        </span>
                                    @endif
                                    @if ($job->equipment)
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                            </svg>
                                            {{ Str::limit($job->equipment->name, 15) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Expand Arrow --}}
                            <svg 
                                class="w-5 h-5 text-gray-400 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    
                    {{-- Expanded Details --}}
                    @if ($isExpanded)
                        <div class="border-t border-gray-100 bg-white/50 p-4 space-y-4">
                            {{-- Problem Description --}}
                            <div>
                                <h5 class="text-xs font-semibold text-gray-500 uppercase mb-1">Problem</h5>
                                <p class="text-sm text-gray-700">{{ $job->description ?? 'No description provided' }}</p>
                            </div>
                            
                            {{-- Customer Contact --}}
                            <div>
                                <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">Customer Contact</h5>
                                <div class="flex gap-2">
                                    @if ($job->organization?->primary_contact_phone)
                                        <a 
                                            href="tel:{{ $job->organization->primary_contact_phone }}"
                                            class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-green-100 text-green-700 rounded-xl font-medium text-sm"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            Call
                                        </a>
                                    @endif
                                    @if ($job->organization?->primary_contact_email)
                                        <a 
                                            href="mailto:{{ $job->organization->primary_contact_email }}"
                                            class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-blue-100 text-blue-700 rounded-xl font-medium text-sm"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            Email
                                        </a>
                                    @endif
                                </div>
                                @if ($job->organization?->primary_contact_name)
                                    <p class="text-xs text-gray-500 mt-2">Contact: {{ $job->organization->primary_contact_name }}</p>
                                @endif
                            </div>
                            
                            {{-- Access Instructions --}}
                            @if ($job->access_instructions || $job->parking_instructions)
                                <div class="bg-amber-50 rounded-xl p-3">
                                    <h5 class="text-xs font-semibold text-amber-700 uppercase mb-1">Access Info</h5>
                                    @if ($job->access_instructions)
                                        <p class="text-sm text-amber-800">🔑 {{ $job->access_instructions }}</p>
                                    @endif
                                    @if ($job->parking_instructions)
                                        <p class="text-sm text-amber-800 mt-1">🚗 {{ $job->parking_instructions }}</p>
                                    @endif
                                </div>
                            @endif
                            
                            {{-- Equipment Details --}}
                            @if ($job->equipment)
                                <div>
                                    <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">Equipment</h5>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="font-medium text-gray-900">{{ $job->equipment->name }}</p>
                                        <div class="grid grid-cols-2 gap-2 mt-2 text-xs text-gray-600">
                                            @if ($job->equipment->manufacturer)
                                                <p>Make: {{ $job->equipment->manufacturer }}</p>
                                            @endif
                                            @if ($job->equipment->model)
                                                <p>Model: {{ $job->equipment->model }}</p>
                                            @endif
                                            @if ($job->equipment->serial_number)
                                                <p>S/N: {{ $job->equipment->serial_number }}</p>
                                            @endif
                                            @if ($job->equipment->last_service_at)
                                                <p>Last Service: {{ $job->equipment->last_service_at->format('M d, Y') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Suggested Parts --}}
                            @if ($job->parts && $job->parts->count() > 0)
                                <div>
                                    <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">Suggested Parts</h5>
                                    <div class="space-y-1">
                                        @foreach ($job->parts as $part)
                                            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                                                <span class="text-sm text-gray-700">{{ $part->name }}</span>
                                                <span class="text-xs text-gray-500">×{{ $part->pivot->quantity ?? 1 }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Action Buttons --}}
                            <div class="grid grid-cols-2 gap-2 pt-2">
                                @if ($job->location_latitude && $job->location_longitude)
                                    <a 
                                        href="https://www.google.com/maps/dir/?api=1&destination={{ $job->location_latitude }},{{ $job->location_longitude }}"
                                        target="_blank"
                                        class="flex items-center justify-center gap-2 py-3 bg-blue-600 text-white rounded-xl font-medium"
                                        wire:click="startTravel({{ $job->id }})"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        Navigate
                                    </a>
                                @endif
                                
                                @if (!$isActive)
                                    <button
                                        wire:click="checkIn({{ $job->id }})"
                                        class="flex items-center justify-center gap-2 py-3 bg-green-600 text-white rounded-xl font-medium"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Check In
                                    </button>
                                @else
                                    <button
                                        wire:click="checkOut({{ $job->id }})"
                                        class="flex items-center justify-center gap-2 py-3 bg-indigo-600 text-white rounded-xl font-medium"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Complete
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No jobs scheduled for today</p>
                    <p class="text-sm text-gray-400 mt-1">Check back later or contact dispatch</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Communication Center --}}
    <section id="messages" class="px-4 pb-4">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Messages</h3>
                @if ($unreadCount > 0)
                    <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                        {{ $unreadCount }} new
                    </span>
                @endif
            </div>
            
            <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                @forelse ($messages->take(5) as $message)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($message->user->name ?? 'D', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 text-sm">{{ $message->user->name ?? 'Dispatch' }}</span>
                                    <span class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-0.5">{{ Str::limit($message->body, 100) }}</p>
                                
                                @if ($message->thread?->workOrder)
                                    <span class="inline-flex items-center gap-1 mt-1 text-xs text-indigo-600">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        Job #{{ $message->thread->workOrder->id }}
                                    </span>
                                @endif
                            </div>
                            <button 
                                wire:click="$set('replyToMessageId', {{ $message->id }})"
                                class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        <p>No messages</p>
                    </div>
                @endforelse
            </div>
            
            {{-- Quick Reply --}}
            @if ($replyToMessageId)
                <div class="p-3 bg-gray-50 border-t border-gray-100">
                    <div class="flex gap-2">
                        <input 
                            type="text"
                            wire:model="replyMessage"
                            wire:keydown.enter="sendQuickReply"
                            placeholder="Type a quick reply..."
                            class="flex-1 rounded-xl border-gray-300 text-sm"
                        >
                        <button
                            wire:click="sendQuickReply"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium text-sm"
                        >
                            Send
                        </button>
                        <button
                            wire:click="$set('replyToMessageId', null)"
                            class="p-2 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Parts & Inventory Widget --}}
    <section class="px-4 pb-4">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Quick Parts</h3>
            </div>
            
            <div class="p-4 grid grid-cols-2 gap-2">
                @forelse ($commonParts as $part)
                    @php
                        $stock = $part->inventory_items_sum_quantity ?? 0;
                        $isLow = $stock <= ($part->reorder_level ?? 5);
                    @endphp
                    <button
                        wire:click="reservePart({{ $part->id }})"
                        @disabled(!$activeJobId)
                        class="flex items-center gap-3 p-3 rounded-xl text-left transition
                            {{ $activeJobId ? 'hover:bg-indigo-50 active:bg-indigo-100' : 'opacity-50 cursor-not-allowed' }}
                            {{ $isLow ? 'bg-amber-50' : 'bg-gray-50' }}"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $part->name }}</p>
                            <p class="text-xs {{ $isLow ? 'text-amber-600' : 'text-gray-500' }}">
                                {{ $stock }} in stock
                                @if ($isLow)
                                    ⚠️
                                @endif
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                @empty
                    <div class="col-span-2 p-4 text-center text-gray-400">
                        <p class="text-sm">No parts available</p>
                    </div>
                @endforelse
            </div>
            
            @if (!$activeJobId)
                <div class="px-4 pb-4">
                    <p class="text-xs text-gray-400 text-center">Check in to a job to reserve parts</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Emergency Modal --}}
    @if ($showEmergencyModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/80">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="bg-red-500 text-white p-4">
                    <h3 class="text-lg font-bold">Emergency Alert</h3>
                    <p class="text-red-100 text-sm">This will immediately notify dispatch</p>
                </div>
                
                <div class="p-4">
                    <textarea
                        wire:model="emergencyNote"
                        rows="3"
                        placeholder="Describe the emergency (optional)..."
                        class="w-full rounded-xl border-gray-300 text-sm"
                    ></textarea>
                </div>
                
                <div class="p-4 bg-gray-50 flex gap-2">
                    <button
                        wire:click="$set('showEmergencyModal', false)"
                        class="flex-1 py-3 border border-gray-300 rounded-xl font-medium text-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="sendEmergencyAlert"
                        class="flex-1 py-3 bg-red-600 text-white rounded-xl font-bold"
                    >
                        Send Alert
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if (session('success'))
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            class="fixed bottom-20 left-4 right-4 z-50"
        >
            <div class="bg-green-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif
    
    @if (session('error'))
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            class="fixed bottom-20 left-4 right-4 z-50"
        >
            <div class="bg-red-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif
</div>
