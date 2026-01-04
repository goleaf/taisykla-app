{{-- Job List Screen --}}
<div class="px-4 py-4 space-y-4" x-data="{ 
    refreshing: false,
    startY: 0,
    pullDistance: 0,
    handleTouchStart(e) { this.startY = e.touches[0].clientY; },
    handleTouchMove(e) {
        if (window.scrollY === 0) {
            this.pullDistance = Math.max(0, Math.min(100, e.touches[0].clientY - this.startY));
        }
    },
    handleTouchEnd() {
        if (this.pullDistance > 60) {
            this.refreshing = true;
            $wire.refreshJobs().then(() => { this.refreshing = false; });
        }
        this.pullDistance = 0;
    }
}" @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">

    {{-- Pull to Refresh Indicator --}}
    <div class="flex justify-center -mt-2 mb-2 transition-all duration-200"
        :style="{ height: pullDistance + 'px', opacity: pullDistance / 60 }">
        <svg class="w-6 h-6 text-indigo-600" :class="{ 'animate-spin': refreshing }" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
    </div>

    {{-- Sort Options --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 -mx-1 px-1">
        <span class="text-xs text-slate-500 font-medium whitespace-nowrap">Sort by:</span>
        @foreach (['time' => 'Time', 'route' => 'Route', 'priority' => 'Priority'] as $key => $label)
            <button wire:click="setSortBy('{{ $key }}')"
                class="px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all touch-target {{ $sortBy === $key ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Job Cards --}}
    <div class="space-y-3">
        @forelse ($workQueue as $job)
            @php
                $priorityColors = [
                    'urgent' => 'border-l-red-500 bg-gradient-to-r from-red-50 to-white',
                    'high' => 'border-l-orange-400 bg-gradient-to-r from-orange-50 to-white',
                    'standard' => 'border-l-blue-400 bg-gradient-to-r from-blue-50 to-white',
                    'low' => 'border-l-slate-300 bg-white',
                ];
                $cardClass = $priorityColors[$job->priority] ?? $priorityColors['standard'];
            @endphp

            <div x-data="{ swiped: false, startX: 0 }"
                class="swipe-container rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden {{ $cardClass }} {{ $swiped ? 'swiped' : '' }}"
                @touchstart="startX = $event.touches[0].clientX; swiped = false"
                @touchend="if ($event.changedTouches[0].clientX - startX < -50) swiped = true; else swiped = false">

                {{-- Swipe Actions --}}
                <div class="swipe-actions h-full">
                    <a href="tel:{{ $job->organization?->primary_contact_phone }}"
                        class="w-16 h-full flex flex-col items-center justify-center bg-blue-500 text-white gap-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="text-[10px] font-medium">Call</span>
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $job->location_latitude }},{{ $job->location_longitude }}"
                        target="_blank"
                        class="w-16 h-full flex flex-col items-center justify-center bg-green-500 text-white gap-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span class="text-[10px] font-medium">Navigate</span>
                    </a>
                    <button wire:click="viewJob({{ $job->id }})"
                        class="w-16 h-full flex flex-col items-center justify-center bg-indigo-600 text-white gap-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-[10px] font-medium">Start</span>
                    </button>
                </div>

                {{-- Card Content --}}
                <button wire:click="viewJob({{ $job->id }})"
                    class="w-full text-left p-4 active:bg-slate-50 transition border-l-4 {{ str_replace('bg-gradient-to-r from-', 'border-l-', explode(' ', $cardClass)[0]) }}">
                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-mono text-slate-400">#{{ $job->id }}</span>
                                @if ($job->priority === 'urgent')
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-semibold text-slate-900 text-base leading-tight line-clamp-2">
                                {{ $job->subject }}</h3>
                        </div>
                        <span
                            class="flex-shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ $job->status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                        </span>
                    </div>

                    {{-- Customer & Location --}}
                    <div class="space-y-1.5 mb-3">
                        @if ($job->organization)
                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="truncate font-medium">{{ $job->organization->name }}</span>
                            </div>
                        @endif
                        @if ($job->location_address)
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <span class="truncate">{{ $job->location_address }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Schedule & Time --}}
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-3 text-xs text-slate-500">
                            @if ($job->scheduled_start_at)
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $job->scheduled_start_at->format('g:i A') }}
                                </div>
                            @endif
                            @if ($job->estimated_minutes)
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    ~{{ $job->estimated_minutes }}m
                                </div>
                            @endif
                        </div>
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </button>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-1">No jobs for today</h3>
                <p class="text-slate-500 text-sm">Pull down to refresh</p>
            </div>
        @endforelse
    </div>
</div>