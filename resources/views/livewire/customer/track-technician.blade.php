<div 
    class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50"
    wire:poll.5s="loadTechnicianLocation"
>
    <div class="max-w-4xl mx-auto px-4 py-8">
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('work-orders.show', $workOrder) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-4" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Work Order
            </a>
            
            <h1 class="text-2xl font-bold text-gray-900">Track Your Technician</h1>
            <p class="text-gray-500">Work Order #{{ $workOrder->id }} - {{ $workOrder->subject }}</p>
        </div>

        {{-- Status Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-6">
            @if ($isLive)
                {{-- Live Tracking Active --}}
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                        </span>
                        <span class="text-lg font-semibold">Live Tracking</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-green-100 text-sm">Technician</p>
                            <p class="text-xl font-bold">{{ $workOrder->assignedTo?->name ?? 'Pending' }}</p>
                        </div>
                        <div>
                            <p class="text-green-100 text-sm">Estimated Arrival</p>
                            <p class="text-3xl font-bold">{{ $eta ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Not Yet Active --}}
                <div class="bg-gradient-to-r from-gray-500 to-gray-600 text-white p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-lg font-semibold">Tracking Not Active</span>
                    </div>
                    
                    <p class="text-gray-200">
                        @if (!$workOrder->assignedTo)
                            A technician has not been assigned yet. You'll be notified when someone is on the way.
                        @elseif ($workOrder->status === 'completed')
                            This service has been completed.
                        @else
                            Live tracking will be available once the technician begins traveling to your location.
                        @endif
                    </p>
                </div>
            @endif
            
            {{-- Map --}}
            <div class="relative h-80 bg-gray-100">
                @if ($isLive && $technicianLat && $technicianLng)
                    <x-route-map
                        :stops="[[
                            'sequence' => 1,
                            'label' => 'Service Location',
                            'address' => $workOrder->location_address,
                            'lat' => $workOrder->location_latitude,
                            'lng' => $workOrder->location_longitude,
                            'priority' => $workOrder->priority,
                        ]]"
                        :current-location="['lat' => $technicianLat, 'lng' => $technicianLng]"
                        :height="'320px'"
                    />
                @elseif ($workOrder->location_latitude && $workOrder->location_longitude)
                    <x-route-map
                        :stops="[[
                            'sequence' => 1,
                            'label' => 'Service Location',
                            'address' => $workOrder->location_address,
                            'lat' => $workOrder->location_latitude,
                            'lng' => $workOrder->location_longitude,
                            'priority' => $workOrder->priority,
                        ]]"
                        :height="'320px'"
                    />
                @else
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p>No location data available</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Technician Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Technician</h3>
                
                @if ($workOrder->assignedTo)
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xl font-bold">
                            {{ strtoupper(substr($workOrder->assignedTo->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $workOrder->assignedTo->name }}</p>
                            <p class="text-sm text-gray-500">{{ $workOrder->assignedTo->job_title ?? 'Field Technician' }}</p>
                            @if ($technicianStatus)
                                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-xs font-medium 
                                    {{ $technicianStatus === 'available' ? 'bg-green-100 text-green-700' : 
                                       ($technicianStatus === 'busy' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $technicianStatus === 'available' ? 'bg-green-500' : ($technicianStatus === 'busy' ? 'bg-amber-500' : 'bg-gray-400') }}"></span>
                                    {{ ucfirst($technicianStatus) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    @if ($workOrder->assignedTo->phone)
                        <a 
                            href="tel:{{ $workOrder->assignedTo->phone }}"
                            class="mt-4 w-full inline-flex items-center justify-center gap-2 py-3 px-4 bg-indigo-50 text-indigo-700 font-medium rounded-xl hover:bg-indigo-100 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Call Technician
                        </a>
                    @endif
                @else
                    <p class="text-gray-500">A technician will be assigned soon.</p>
                @endif
            </div>

            {{-- Service Details --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Service Details</h3>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        @php
                            $statusColors = [
                                'submitted' => 'bg-gray-100 text-gray-700',
                                'assigned' => 'bg-blue-100 text-blue-700',
                                'in_progress' => 'bg-indigo-100 text-indigo-700',
                                'on_hold' => 'bg-yellow-100 text-yellow-700',
                                'completed' => 'bg-green-100 text-green-700',
                            ];
                        @endphp
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$workOrder->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                        </span>
                    </div>
                    
                    @if ($workOrder->scheduled_start_at)
                        <div>
                            <p class="text-sm text-gray-500">Scheduled</p>
                            <p class="font-medium text-gray-900">{{ $workOrder->scheduled_start_at->format('l, M d \a\t g:i A') }}</p>
                        </div>
                    @endif
                    
                    @if ($workOrder->location_address)
                        <div>
                            <p class="text-sm text-gray-500">Location</p>
                            <p class="font-medium text-gray-900">{{ $workOrder->location_address }}</p>
                        </div>
                    @endif
                    
                    @if ($workOrder->equipment)
                        <div>
                            <p class="text-sm text-gray-500">Equipment</p>
                            <p class="font-medium text-gray-900">{{ $workOrder->equipment->name }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        @if ($workOrder->events && $workOrder->events->count() > 0)
            <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Timeline</h3>
                
                <div class="space-y-4">
                    @foreach ($workOrder->events->take(5) as $event)
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full bg-indigo-500"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $event->type)) }}</p>
                                @if ($event->note)
                                    <p class="text-sm text-gray-500">{{ $event->note }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">{{ $event->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
