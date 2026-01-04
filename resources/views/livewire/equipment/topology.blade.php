<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('equipment.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800" wire:navigate>← Back to Equipment</a>
                <h1 class="text-2xl font-semibold text-gray-900">Network Topology</h1>
                <p class="text-sm text-gray-500">Visualize equipment relationships and dependencies</p>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="setViewMode('network')" @class([
                    'px-3 py-1.5 text-sm rounded-lg transition-colors',
                    'bg-indigo-600 text-white' => $viewMode === 'network',
                    'bg-gray-100 text-gray-700 hover:bg-gray-200' => $viewMode !== 'network',
                ])>
                    Network Map
                </button>
                <button wire:click="setViewMode('hierarchy')" @class([
                    'px-3 py-1.5 text-sm rounded-lg transition-colors',
                    'bg-indigo-600 text-white' => $viewMode === 'hierarchy',
                    'bg-gray-100 text-gray-700 hover:bg-gray-200' => $viewMode !== 'hierarchy',
                ])>
                    Hierarchy
                </button>
                <button wire:click="setViewMode('subnet')" @class([
                    'px-3 py-1.5 text-sm rounded-lg transition-colors',
                    'bg-indigo-600 text-white' => $viewMode === 'subnet',
                    'bg-gray-100 text-gray-700 hover:bg-gray-200' => $viewMode !== 'subnet',
                ])>
                    By Subnet
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                {{-- Network Map View --}}
                @if($viewMode === 'network')
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Network Map</h2>
                            <div class="flex items-center gap-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                    <span class="text-gray-500">Healthy (80+)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                                    <span class="text-gray-500">Good (60-79)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                    <span class="text-gray-500">Fair (40-59)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                    <span class="text-gray-500">Poor (<40)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Network Visualization --}}
                        <div class="relative bg-gray-50 rounded-lg p-8 min-h-[500px] border border-gray-200">
                            @if(count($topology['nodes']) === 0)
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <p>No equipment with network information found.</p>
                                        <p class="text-sm">Add IP addresses to equipment to see them here.</p>
                                    </div>
                                </div>
                            @else
                                {{-- Simple Grid-based Network View --}}
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($topology['nodes'] as $node)
                                        <button
                                            wire:click="selectEquipment({{ $node['id'] }})"
                                            @class([
                                                'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
                                                'border-indigo-500 bg-indigo-50' => $selectedEquipmentId === $node['id'],
                                                'border-gray-200 bg-white' => $selectedEquipmentId !== $node['id'],
                                            ])
                                        >
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $node['color'] }}"></span>
                                                <span class="text-xs text-gray-500">{{ $node['type'] }}</span>
                                            </div>
                                            <p class="font-medium text-gray-900 text-sm truncate">{{ $node['label'] }}</p>
                                            <p class="text-xs text-gray-500 font-mono mt-1">{{ $node['ip_address'] }}</p>
                                            @if($node['health_score'])
                                                <p class="text-xs text-gray-400 mt-1">Health: {{ $node['health_score'] }}%</p>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Stats --}}
                        <div class="grid grid-cols-4 gap-4 mt-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-gray-900">{{ $topology['stats']['total_nodes'] }}</p>
                                <p class="text-xs text-gray-500">Total Nodes</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-gray-900">{{ $topology['stats']['total_edges'] }}</p>
                                <p class="text-xs text-gray-500">Connections</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-600">{{ $topology['stats']['by_status']['operational'] ?? 0 }}</p>
                                <p class="text-xs text-gray-500">Operational</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-red-600">{{ ($topology['stats']['by_status']['needs_attention'] ?? 0) + ($topology['stats']['by_status']['in_repair'] ?? 0) }}</p>
                                <p class="text-xs text-gray-500">Issues</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Hierarchy View --}}
                @if($viewMode === 'hierarchy')
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Equipment Hierarchy</h2>
                        <div class="space-y-2">
                            @php
                                $rootNodes = collect($topology['nodes'])->filter(function($node) use ($topology) {
                                    return !collect($topology['edges'])->where('target', $node['id'])->where('type', 'hierarchy')->count();
                                });
                            @endphp
                            @forelse($rootNodes as $node)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <button
                                        wire:click="selectEquipment({{ $node['id'] }})"
                                        class="flex items-center gap-3 w-full text-left hover:bg-gray-50 rounded p-2 -m-2"
                                    >
                                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $node['color'] }}"></span>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $node['label'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $node['type'] }} • {{ $node['ip_address'] ?? 'No IP' }}</p>
                                        </div>
                                    </button>
                                    @php
                                        $children = collect($topology['edges'])
                                            ->where('source', $node['id'])
                                            ->where('type', 'hierarchy')
                                            ->pluck('target');
                                        $childNodes = collect($topology['nodes'])->whereIn('id', $children);
                                    @endphp
                                    @if($childNodes->isNotEmpty())
                                        <div class="ml-6 mt-2 border-l-2 border-gray-200 pl-4 space-y-2">
                                            @foreach($childNodes as $child)
                                                <button
                                                    wire:click="selectEquipment({{ $child['id'] }})"
                                                    class="flex items-center gap-3 w-full text-left hover:bg-gray-50 rounded p-2"
                                                >
                                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $child['color'] }}"></span>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $child['label'] }}</p>
                                                        <p class="text-xs text-gray-500">{{ $child['type'] }}</p>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">No hierarchy relationships defined.</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- Subnet View --}}
                @if($viewMode === 'subnet')
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Equipment by Subnet</h2>
                            <select wire:model.live="subnetFilter" class="text-sm border-gray-300 rounded-lg">
                                <option value="">Select subnet...</option>
                                @foreach($subnets as $subnet => $count)
                                    <option value="{{ $subnet }}">{{ $subnet }} ({{ $count }} devices)</option>
                                @endforeach
                            </select>
                        </div>

                        @if($subnetEquipment)
                            <div class="space-y-2">
                                @foreach($subnetEquipment as $item)
                                    <button
                                        wire:click="selectEquipment({{ $item->id }})"
                                        @class([
                                            'w-full text-left p-3 rounded-lg border transition-colors',
                                            'border-indigo-500 bg-indigo-50' => $selectedEquipmentId === $item->id,
                                            'border-gray-200 hover:border-gray-300' => $selectedEquipmentId !== $item->id,
                                        ])
                                    >
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->type }} • {{ $item->manufacturer?->name ?? $item->manufacturer }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-mono text-sm text-gray-700">{{ $item->ip_address }}</p>
                                                <p class="text-xs text-gray-400">{{ $item->mac_address }}</p>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <p>Select a subnet to view equipment.</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Sidebar: Impact Analysis --}}
            <div class="space-y-6">
                @if($selectedEquipment)
                    {{-- Selected Equipment Info --}}
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Selected Equipment</h2>
                            <button wire:click="clearSelection" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <p class="font-medium text-gray-900">{{ $selectedEquipment->name }}</p>
                            <p class="text-sm text-gray-500">{{ $selectedEquipment->type }}</p>
                            @if($selectedEquipment->ip_address)
                                <p class="text-sm font-mono text-gray-600">{{ $selectedEquipment->ip_address }}</p>
                            @endif
                            <a href="{{ route('equipment.show', $selectedEquipment) }}" class="text-sm text-indigo-600 hover:text-indigo-800" wire:navigate>
                                View Details →
                            </a>
                        </div>
                    </div>

                    {{-- Impact Analysis --}}
                    @if($impactAnalysis)
                        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Impact Analysis</h2>
                            <div @class([
                                'p-3 rounded-lg mb-4',
                                'bg-red-50 text-red-700' => $impactAnalysis['impact_severity'] === 'critical',
                                'bg-orange-50 text-orange-700' => $impactAnalysis['impact_severity'] === 'high',
                                'bg-yellow-50 text-yellow-700' => $impactAnalysis['impact_severity'] === 'medium',
                                'bg-blue-50 text-blue-700' => $impactAnalysis['impact_severity'] === 'low',
                                'bg-gray-50 text-gray-700' => $impactAnalysis['impact_severity'] === 'minimal',
                            ])>
                                <p class="font-medium">{{ ucfirst($impactAnalysis['impact_severity']) }} Impact</p>
                                <p class="text-sm">{{ $impactAnalysis['total_affected'] }} dependent equipment</p>
                            </div>

                            @if(count($impactAnalysis['directly_affected']) > 0)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Directly Affected ({{ count($impactAnalysis['directly_affected']) }})</p>
                                    <div class="space-y-1">
                                        @foreach($impactAnalysis['directly_affected'] as $item)
                                            <div class="text-sm text-gray-600">• {{ $item['name'] }} ({{ $item['type'] }})</div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(count($impactAnalysis['indirectly_affected']) > 0)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Indirectly Affected ({{ count($impactAnalysis['indirectly_affected']) }})</p>
                                    <div class="space-y-1">
                                        @foreach($impactAnalysis['indirectly_affected']->take(5) as $item)
                                            <div class="text-sm text-gray-500">• {{ $item['name'] }}</div>
                                        @endforeach
                                        @if(count($impactAnalysis['indirectly_affected']) > 5)
                                            <div class="text-sm text-gray-400">+ {{ count($impactAnalysis['indirectly_affected']) - 5 }} more</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!empty($impactAnalysis['recommendations']))
                                <div class="border-t border-gray-200 pt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Recommendations</p>
                                    <div class="space-y-2">
                                        @foreach($impactAnalysis['recommendations'] as $rec)
                                            <div @class([
                                                'p-2 rounded text-xs',
                                                'bg-red-50 text-red-700' => $rec['type'] === 'critical',
                                                'bg-yellow-50 text-yellow-700' => $rec['type'] === 'warning',
                                                'bg-blue-50 text-blue-700' => $rec['type'] === 'info',
                                            ])>
                                                {{ $rec['message'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Dependency Chain --}}
                    @if($dependencyChain && count($dependencyChain) > 0)
                        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Depends On</h2>
                            <div class="space-y-2">
                                @foreach($dependencyChain as $dep)
                                    <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                                        <div @class([
                                            'w-2 h-2 rounded-full',
                                            'bg-green-500' => $dep['status'] === 'operational',
                                            'bg-yellow-500' => $dep['status'] === 'needs_attention',
                                            'bg-red-500' => in_array($dep['status'], ['in_repair', 'retired']),
                                        ])></div>
                                        <div class="text-sm">
                                            <p class="font-medium text-gray-900">{{ $dep['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $dep['type'] }}</p>
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="flex justify-center">
                                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                            </svg>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-gray-50 rounded-lg p-6 text-center text-gray-500">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                        </svg>
                        <p class="text-sm">Click on equipment to view impact analysis</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
