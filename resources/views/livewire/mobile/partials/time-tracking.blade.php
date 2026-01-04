{{-- Time Tracking Screen --}}
<div class="px-4 py-4 space-y-4">
    {{-- Main Timer --}}
    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-6 text-white text-center">
        <div class="mb-4" x-data="{ elapsed: {{ $elapsedSeconds }} }" x-init="setInterval(() => { if ({{ $timerRunning ? 'true' : 'false' }}) elapsed++ }, 1000)">
            <div class="text-6xl font-bold timer-display tracking-wider mb-2" x-text="Math.floor(elapsed/3600).toString().padStart(2,'0') + ':' + Math.floor((elapsed%3600)/60).toString().padStart(2,'0') + ':' + (elapsed%60).toString().padStart(2,'0')">
                00:00:00
            </div>
            <div class="text-slate-400 text-sm uppercase tracking-wide">{{ ucfirst($activityType) }}</div>
        </div>

        {{-- Activity Type Buttons --}}
        <div class="flex gap-2 mb-6">
            @foreach (['work' => 'Work', 'diagnosis' => 'Diagnosis', 'travel' => 'Travel'] as $key => $label)
                <button wire:click="setActivityType('{{ $key }}')" 
                    class="flex-1 touch-target py-2.5 rounded-xl text-sm font-semibold transition
                    {{ $activityType === $key ? 'bg-white text-slate-900' : 'bg-white/10 hover:bg-white/20' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Timer Controls --}}
        <div class="flex gap-3 justify-center">
            @if (!$timerRunning)
                <button wire:click="resumeTimer" class="touch-target w-20 h-20 rounded-full bg-emerald-500 hover:bg-emerald-600 flex items-center justify-center shadow-lg transition">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </button>
            @else
                <button wire:click="pauseTimer" class="touch-target w-20 h-20 rounded-full bg-amber-500 hover:bg-amber-600 flex items-center justify-center shadow-lg transition">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                    </svg>
                </button>
            @endif
            <button wire:click="stopTimer" class="touch-target w-16 h-16 rounded-full bg-red-500/20 hover:bg-red-500/30 border-2 border-red-400 flex items-center justify-center transition self-center">
                <svg class="w-8 h-8 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 6h12v12H6z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Break Control --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-900">Break Time</h3>
                <p class="text-sm text-slate-500">Log your break periods</p>
            </div>
            @if ($activityType !== 'break')
                <button wire:click="startBreak" class="touch-target px-5 py-2.5 bg-amber-100 hover:bg-amber-200 text-amber-700 font-semibold rounded-xl transition">
                    Start Break
                </button>
            @else
                <button wire:click="endBreak" class="touch-target px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl transition">
                    End Break
                </button>
            @endif
        </div>
    </div>

    {{-- Daily Summary --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <h3 class="font-semibold text-slate-900 mb-4">Today's Summary</h3>
        <div class="grid grid-cols-2 gap-3">
            @foreach ([
                'work' => ['label' => 'Work', 'color' => 'emerald', 'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z'],
                'travel' => ['label' => 'Travel', 'color' => 'blue', 'icon' => 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2'],
                'break' => ['label' => 'Break', 'color' => 'amber', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                'diagnosis' => ['label' => 'Diagnosis', 'color' => 'purple', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
            ] as $key => $item)
                @php $minutes = $dailySummary[$key] ?? 0; @endphp
                <div class="p-3 bg-{{ $item['color'] }}-50 rounded-xl">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 rounded-lg bg-{{ $item['color'] }}-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-{{ $item['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-{{ $item['color'] }}-700">{{ $item['label'] }}</span>
                    </div>
                    <div class="text-2xl font-bold text-{{ $item['color'] }}-900 timer-display">
                        {{ sprintf('%d:%02d', floor($minutes / 60), $minutes % 60) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Manual Time Entry --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
        <button wire:click="toggleManualEntry" class="w-full p-4 flex items-center justify-between text-left">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900">Manual Time Entry</h4>
                    <p class="text-xs text-slate-500">Add time entries manually</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-slate-400 transition-transform {{ $showManualTimeEntry ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        @if ($showManualTimeEntry)
            <div class="p-4 pt-0 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Start Time</label>
                        <input type="time" wire:model="manualStartTime" class="w-full touch-target px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">End Time</label>
                        <input type="time" wire:model="manualEndTime" class="w-full touch-target px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Activity Type</label>
                    <select wire:model="manualActivity" class="w-full touch-target px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="work">Work</option>
                        <option value="diagnosis">Diagnosis</option>
                        <option value="travel">Travel</option>
                        <option value="break">Break</option>
                    </select>
                </div>
                <button wire:click="saveManualEntry" class="w-full touch-target py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                    Add Entry
                </button>
            </div>
        @endif
    </div>

    {{-- Recent Time Entries --}}
    @if (count($timeEntries) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Today's Entries</h3>
            </div>
            <div class="divide-y divide-slate-100 max-h-64 overflow-y-auto">
                @foreach (array_reverse($timeEntries) as $entry)
                    <div class="p-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $entry['activity'] === 'work' ? 'bg-emerald-100 text-emerald-700' : 
                                   ($entry['activity'] === 'travel' ? 'bg-blue-100 text-blue-700' : 
                                   ($entry['activity'] === 'break' ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700')) }}">
                                {{ ucfirst($entry['activity']) }}
                            </span>
                            <span class="text-xs text-slate-400">
                                {{ \Carbon\Carbon::parse($entry['start_time'])->format('g:i A') }} - 
                                {{ \Carbon\Carbon::parse($entry['end_time'])->format('g:i A') }}
                            </span>
                        </div>
                        <span class="font-semibold text-slate-700 text-sm timer-display">
                            {{ sprintf('%d:%02d', floor($entry['duration_minutes'] / 60), $entry['duration_minutes'] % 60) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
