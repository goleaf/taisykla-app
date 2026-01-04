{{-- Bottom Navigation --}}
<nav class="fixed bottom-0 inset-x-0 z-50 glass border-t border-slate-200/50 bottom-nav-safe">
    <div class="flex items-stretch justify-around h-16 max-w-md mx-auto">
        {{-- Jobs --}}
        <button wire:click="$set('activeScreen', 'jobs')"
            class="flex-1 flex flex-col items-center justify-center gap-0.5 transition {{ $activeScreen === 'jobs' ? 'text-indigo-600' : 'text-slate-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span class="text-[10px] font-semibold">Jobs</span>
        </button>

        {{-- Photos --}}
        @if ($currentJob)
            <button wire:click="goToPhotos"
                class="flex-1 flex flex-col items-center justify-center gap-0.5 transition {{ $activeScreen === 'photos' ? 'text-indigo-600' : 'text-slate-500' }}">
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    @if (count($photos) > 0)
                        <span
                            class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ count($photos) }}</span>
                    @endif
                </div>
                <span class="text-[10px] font-semibold">Photos</span>
            </button>
        @endif

        {{-- Timer (Center) --}}
        <button wire:click="goToTimeTracking"
            class="flex-1 flex flex-col items-center justify-center gap-0.5 transition {{ $activeScreen === 'time' ? 'text-indigo-600' : 'text-slate-500' }}">
            <div class="relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                @if ($timerRunning)
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></span>
                @endif
            </div>
            <span class="text-[10px] font-semibold">Timer</span>
        </button>

        {{-- Parts --}}
        @if ($currentJob)
            <button wire:click="goToParts"
                class="flex-1 flex flex-col items-center justify-center gap-0.5 transition {{ $activeScreen === 'parts' ? 'text-indigo-600' : 'text-slate-500' }}">
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    @if (count($selectedParts) > 0)
                        <span
                            class="absolute -top-1 -right-1 w-4 h-4 bg-teal-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ count($selectedParts) }}</span>
                    @endif
                </div>
                <span class="text-[10px] font-semibold">Parts</span>
            </button>
        @endif

        {{-- More / Quick Actions --}}
        <button wire:click="toggleQuickActions"
            class="flex-1 flex flex-col items-center justify-center gap-0.5 transition {{ $showQuickActions ? 'text-indigo-600' : 'text-slate-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="text-[10px] font-semibold">More</span>
        </button>
    </div>
</nav>