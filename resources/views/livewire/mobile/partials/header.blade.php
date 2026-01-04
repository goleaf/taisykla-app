{{-- Mobile Header --}}
<header class="sticky top-0 z-40 glass border-b border-slate-200/50 safe-area-top">
    <div class="flex items-center justify-between px-4 py-3">
        {{-- Back Button / Logo --}}
        <div class="flex items-center gap-3">
            @if ($activeScreen !== 'jobs')
                <button wire:click="goBack" class="touch-target flex items-center justify-center -ml-2 p-2 rounded-xl hover:bg-slate-100 active:bg-slate-200 transition">
                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            @else
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            @endif

            {{-- Page Title --}}
            <h1 class="font-semibold text-slate-900 text-lg">
                @switch($activeScreen)
                    @case('jobs') My Jobs @break
                    @case('detail') Job Details @break
                    @case('photos') Capture Photos @break
                    @case('notes') Work Notes @break
                    @case('parts') Parts Used @break
                    @case('time') Time Tracking @break
                    @case('signoff') Customer Sign-off @break
                @endswitch
            </h1>
        </div>

        {{-- Right Actions --}}
        <div class="flex items-center gap-1">
            {{-- Job Counter Badge --}}
            @if ($activeScreen === 'jobs')
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold">
                    <span>{{ $workQueue->count() }}</span>
                    <span class="text-indigo-500">jobs</span>
                </div>
            @endif

            {{-- Offline Queue Badge --}}
            @if (count($offlineQueue) > 0)
                <div class="flex items-center gap-1 px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ count($offlineQueue) }}
                </div>
            @endif

            {{-- Timer Display --}}
            @if ($timerRunning && $activeScreen !== 'time')
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold timer-display">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span x-data="{ elapsed: {{ $elapsedSeconds }} }" x-init="setInterval(() => elapsed++, 1000)" x-text="Math.floor(elapsed/3600).toString().padStart(2,'0') + ':' + Math.floor((elapsed%3600)/60).toString().padStart(2,'0') + ':' + (elapsed%60).toString().padStart(2,'0')"></span>
                </div>
            @endif
        </div>
    </div>
</header>
