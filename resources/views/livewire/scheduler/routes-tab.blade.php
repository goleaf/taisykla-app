{{-- Routes Tab --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Technician List --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Technicians</h2>
                <p class="text-sm text-gray-500">Select to optimize route</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($this->technicians as $technician)
                    @php
                        $metric = collect($capacityMetrics)->firstWhere('technician_id', $technician->id);
                    @endphp
                    <button wire:click="selectTechnicianForOptimization({{ $technician->id }})" 
                        class="w-full p-4 text-left hover:bg-gray-50 transition {{ $optimizeTechnicianId === $technician->id ? 'bg-indigo-50' : '' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-semibold">
                                    {{ substr($technician->name, 0, 2) }}
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 text-sm">{{ $technician->name }}</h3>
                                    <p class="text-xs text-gray-500">
                                        {{ $metric['daily']['job_count'] ?? 0 }} jobs today
                                    </p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Route Optimization Preview --}}
    <div class="lg:col-span-2">
        @if ($showOptimizationPreview && !empty($optimizedRoute))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold text-gray-900">Route Optimization</h2>
                            @if (!empty($routeSummary))
                                <div class="flex items-center gap-4 mt-1">
                                    <span class="text-sm text-gray-600">
                                        {{ $routeSummary['total_stops'] }} stops
                                    </span>
                                    <span class="text-sm text-gray-600">
                                        {{ $routeSummary['total_distance_km'] ?? 0 }} km
                                    </span>
                                    @if (($routeSummary['time_saved'] ?? 0) > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            🕐 {{ $routeSummary['time_saved'] }} min saved
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="acceptOptimization" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Accept Optimization
                            </button>
                            <button wire:click="$set('showOptimizationPreview', false)" class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 divide-x divide-gray-200">
                    {{-- Current Route --}}
                    <div>
                        <div class="p-3 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-sm font-medium text-gray-700">Current Order</h3>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-[400px] overflow-y-auto">
                            @foreach ($currentRoute as $index => $stop)
                                <div class="p-3 flex items-start gap-3">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-200 text-gray-600 text-xs font-bold flex items-center justify-center">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $stop['subject'] ?? 'Job' }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $stop['address'] ?? 'No address' }}</p>
                                        <p class="text-xs text-gray-400">{{ $stop['start'] }} - {{ $stop['end'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Optimized Route --}}
                    <div>
                        <div class="p-3 bg-green-50 border-b border-gray-200">
                            <h3 class="text-sm font-medium text-green-700">Optimized Order</h3>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-[400px] overflow-y-auto">
                            @foreach ($optimizedRoute as $stop)
                                <div class="p-3 flex items-start gap-3">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-500 text-white text-xs font-bold flex items-center justify-center">
                                        {{ $stop['sequence'] }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $stop['subject'] ?? 'Job' }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $stop['address'] ?? 'No address' }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-green-600">Est. {{ $stop['estimated_arrival'] }}</span>
                                            @if ($stop['travel_minutes'] > 0)
                                                <span class="text-xs text-gray-400">
                                                    {{ $stop['travel_minutes'] }} min drive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Route Summary --}}
                @if (!empty($routeSummary))
                    <div class="p-4 bg-gray-50 border-t border-gray-200">
                        <div class="grid grid-cols-4 gap-4 text-center">
                            <div>
                                <p class="text-xs text-gray-500">Total Stops</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $routeSummary['total_stops'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Distance</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $routeSummary['total_distance_km'] ?? 0 }} km</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Travel Time</p>
                                <p class="text-lg font-semibold text-gray-900">{{ floor(($routeSummary['total_travel_minutes'] ?? 0) / 60) }}h {{ ($routeSummary['total_travel_minutes'] ?? 0) % 60 }}m</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Efficiency</p>
                                <p class="text-lg font-semibold {{ ($routeSummary['efficiency_score'] ?? 0) >= 70 ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ $routeSummary['efficiency_score'] ?? 0 }}%
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            {{-- Placeholder --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Route Optimization</h3>
                <p class="text-gray-500 text-sm mb-4">Select a technician to optimize their daily route</p>
                <p class="text-xs text-gray-400">
                    Uses traffic-aware algorithms to minimize drive time and maximize efficiency
                </p>
            </div>
        @endif
    </div>
</div>
