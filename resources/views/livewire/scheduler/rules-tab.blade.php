{{-- Rules Tab --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Scheduling Rules --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-gray-900">Auto-Assignment Rules</h2>
                    <p class="text-sm text-gray-500">Rules are applied in priority order</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach ($schedulingRules as $rule)
                <div class="p-4">
                    <div class="flex items-start gap-4">
                        {{-- Priority Badge --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                            {{ $rule['priority'] }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-medium text-gray-900">{{ $rule['name'] }}</h3>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        wire:click="toggleRule('{{ $rule['id'] }}', {{ $rule['enabled'] ? 'false' : 'true' }})"
                                        class="sr-only peer"
                                        {{ $rule['enabled'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                            <p class="text-sm text-gray-500">{{ $rule['description'] }}</p>

                            <div class="flex items-center gap-4 mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ ucfirst($rule['type']) }}
                                </span>

                                {{-- Priority Adjustment --}}
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-gray-500">Priority:</span>
                                    <button wire:click="updateRulePriority('{{ $rule['id'] }}', {{ max(10, $rule['priority'] - 10) }})" 
                                        class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-50"
                                        {{ $rule['priority'] <= 10 ? 'disabled' : '' }}>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <span class="text-xs font-medium text-gray-700 w-6 text-center">{{ $rule['priority'] }}</span>
                                    <button wire:click="updateRulePriority('{{ $rule['id'] }}', {{ min(100, $rule['priority'] + 10) }})" 
                                        class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-50"
                                        {{ $rule['priority'] >= 100 ? 'disabled' : '' }}>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Rule Documentation & How It Works --}}
    <div class="space-y-6">
        {{-- How Auto-Assignment Works --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">How Auto-Assignment Works</h2>
            </div>
            <div class="p-4">
                <ol class="space-y-4">
                    <li class="flex gap-3">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">1</div>
                        <div>
                            <p class="font-medium text-gray-900">Rules Evaluated</p>
                            <p class="text-sm text-gray-500">Rules are checked in priority order (highest first)</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">2</div>
                        <div>
                            <p class="font-medium text-gray-900">First Match Applied</p>
                            <p class="text-sm text-gray-500">When a rule matches, that technician is selected</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">3</div>
                        <div>
                            <p class="font-medium text-gray-900">Conflicts Checked</p>
                            <p class="text-sm text-gray-500">System validates for overlaps and capacity</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">4</div>
                        <div>
                            <p class="font-medium text-gray-900">Fallback to Scoring</p>
                            <p class="text-sm text-gray-500">If no rules match, recommendation engine ranks all technicians</p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>

        {{-- Rule Types Explained --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Rule Types</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ([
                    ['type' => 'preference', 'title' => 'Customer Preference', 'desc' => 'Respects customer-specified technician preferences or historical assignments'],
                    ['type' => 'requirement', 'title' => 'Certification Required', 'desc' => 'Matches equipment types to technicians with required certifications'],
                    ['type' => 'territory', 'title' => 'Territory Assignment', 'desc' => 'Routes jobs to technicians assigned to specific geographic areas'],
                    ['type' => 'skill', 'title' => 'Skill-Based Routing', 'desc' => 'Matches job skill requirements to technician capabilities'],
                    ['type' => 'distribution', 'title' => 'Round Robin', 'desc' => 'Distributes jobs evenly across available technicians'],
                    ['type' => 'balance', 'title' => 'Workload Balance', 'desc' => 'Prefers technicians with lighter current workloads'],
                ] as $ruleType)
                    <div class="p-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $ruleType['type'] }}
                            </span>
                            <span class="font-medium text-gray-900 text-sm">{{ $ruleType['title'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $ruleType['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Manual Override Info --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-medium text-amber-800">Manual Override</h3>
                    <p class="text-sm text-amber-700 mt-1">
                        You can always manually assign a technician from the Assignments tab. 
                        Manual assignments are logged for audit purposes and bypass all automated rules.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
