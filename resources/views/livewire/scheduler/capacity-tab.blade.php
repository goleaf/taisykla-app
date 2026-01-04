{{-- Capacity Tab --}}
<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Technicians</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $hiringRecommendations['active_technicians'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Avg. Utilization</p>
                    <p
                        class="text-2xl font-bold {{ ($hiringRecommendations['avg_utilization'] ?? 0) > 85 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $hiringRecommendations['avg_utilization'] ?? 0 }}%
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Overbooked</p>
                    <p
                        class="text-2xl font-bold {{ ($hiringRecommendations['overbooked_count'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $hiringRecommendations['overbooked_count'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Underutilized</p>
                    <p class="text-2xl font-bold text-amber-600">{{ count($this->underutilizedTechnicians) }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Technician Capacity Cards --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Technician Capacity</h2>
            </div>
            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                @foreach ($capacityMetrics as $metric)
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-semibold">
                                            {{ substr($metric['name'], 0, 2) }}
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ $metric['name'] }}</h3>
                                            <p class="text-xs text-gray-500">
                                                {{ $metric['daily']['job_count'] }} jobs today
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $metric['status'] === 'overbooked' ? 'bg-red-100 text-red-800' :
                    ($metric['status'] === 'near_capacity' ? 'bg-orange-100 text-orange-800' :
                        ($metric['status'] === 'optimal' ? 'bg-green-100 text-green-800' :
                            ($metric['status'] === 'underutilized' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'))) }}">
                                        {{ ucfirst(str_replace('_', ' ', $metric['status'])) }}
                                    </span>
                                </div>

                                {{-- Daily Progress Bar --}}
                                <div class="mb-2">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                        <span>Daily: {{ floor($metric['daily']['scheduled_minutes'] / 60) }}h /
                                            {{ floor($metric['daily']['max_minutes'] / 60) }}h</span>
                                        <span>{{ $metric['daily']['utilization'] }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300
                                                {{ $metric['daily']['utilization'] >= 100 ? 'bg-red-500' :
                    ($metric['daily']['utilization'] >= 85 ? 'bg-orange-500' :
                        ($metric['daily']['utilization'] >= 50 ? 'bg-green-500' : 'bg-amber-500')) }}"
                                            style="width: {{ min(100, $metric['daily']['utilization']) }}%"></div>
                                    </div>
                                </div>

                                {{-- Weekly Progress Bar --}}
                                <div>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                        <span>Weekly: {{ floor($metric['weekly']['scheduled_minutes'] / 60) }}h /
                                            {{ floor($metric['weekly']['max_minutes'] / 60) }}h</span>
                                        <span>{{ $metric['weekly']['utilization'] }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-indigo-500 transition-all duration-300"
                                            style="width: {{ min(100, $metric['weekly']['utilization']) }}%"></div>
                                    </div>
                                </div>

                                {{-- Alerts --}}
                                @if (!empty($metric['alerts']))
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach ($metric['alerts'] as $alert)
                                                    <span
                                                        class="text-xs px-2 py-0.5 rounded 
                                                                    {{ $alert['severity'] === 'critical' ? 'bg-red-50 text-red-700' :
                                            ($alert['severity'] === 'warning' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                                        {{ $alert['message'] }}
                                                    </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Actions --}}
                                <div class="mt-3 flex gap-2">
                                    <button wire:click="selectTechnicianForOptimization({{ $metric['technician_id'] }})"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        Optimize Route
                                    </button>
                                    <button wire:click="compressSchedule({{ $metric['technician_id'] }})"
                                        class="text-xs text-gray-600 hover:text-gray-800 font-medium">
                                        Compress
                                    </button>
                                </div>
                            </div>
                @endforeach
            </div>
        </div>

        {{-- Capacity Forecast & Recommendations --}}
        <div class="space-y-6">
            {{-- Forecast --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900">Capacity Forecast</h2>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @foreach ($forecast as $week)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">{{ $week['week_label'] }}</span>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-900">{{ $week['predicted_jobs'] }}
                                        jobs</span>
                                    <span class="text-xs text-gray-500">(~{{ $week['predicted_hours'] }}h)</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs 
                                            {{ $week['confidence'] === 'high' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ ucfirst($week['confidence']) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Hiring Recommendations --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900">Staffing Insights</h2>
                </div>
                <div class="p-4 space-y-3">
                    @foreach ($hiringRecommendations['recommendations'] ?? [] as $rec)
                                    <div
                                        class="p-3 rounded-lg 
                                            {{ $rec['urgency'] === 'critical' ? 'bg-red-50 border border-red-200' :
                        ($rec['urgency'] === 'high' ? 'bg-orange-50 border border-orange-200' :
                            ($rec['urgency'] === 'medium' ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50 border border-gray-200')) }}">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0">
                                                @if ($rec['urgency'] === 'critical')
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                @elseif ($rec['type'] === 'stable')
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-700">{{ $rec['message'] }}</p>
                                        </div>
                                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>