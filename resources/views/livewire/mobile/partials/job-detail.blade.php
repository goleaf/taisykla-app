{{-- Job Detail Screen --}}
@if ($currentJob)
    <div class="px-4 py-4 space-y-4">
        {{-- Top Section: Customer & Quick Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
            {{-- Customer Info --}}
            <div class="p-4 border-b border-slate-100">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1 min-w-0">
                        <h2 class="font-bold text-lg text-slate-900">{{ $currentJob->organization?->name ?? 'Customer' }}
                        </h2>
                        @if ($currentJob->location_address)
                            <button wire:click="navigateToJob"
                                class="flex items-start gap-2 mt-1 text-sm text-indigo-600 hover:text-indigo-800">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <span class="text-left">{{ $currentJob->location_address }}</span>
                            </button>
                        @endif
                    </div>
                    {{-- Priority Badge --}}
                    <span
                        class="flex-shrink-0 inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold uppercase
                        {{ $currentJob->priority === 'urgent' ? 'bg-red-100 text-red-700' : ($currentJob->priority === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                        @if ($currentJob->priority === 'urgent')
                            <span class="relative flex h-2 w-2"><span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span
                                    class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span></span>
                        @endif
                        {{ $currentJob->priority }}
                    </span>
                </div>

                {{-- Quick Contact Buttons --}}
                <div class="flex gap-2">
                    <button wire:click="callCustomer"
                        class="flex-1 touch-target flex items-center justify-center gap-2 px-4 py-3 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        Call
                    </button>
                    <button wire:click="messageCustomer"
                        class="flex-1 touch-target flex items-center justify-center gap-2 px-4 py-3 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white font-semibold rounded-xl transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Message
                    </button>
                </div>
            </div>

            {{-- Status Dropdown --}}
            <div class="p-4 bg-slate-50">
                <label class="block text-xs font-medium text-slate-500 mb-2">Current Status</label>
                <select wire:model.live="jobStatus" wire:change="updateJobStatus($event.target.value)"
                    class="w-full touch-target px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="assigned">Assigned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="on_hold">On Hold</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>

        {{-- Problem Information --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
            <div class="p-4">
                <h3 class="font-semibold text-slate-900 mb-2">Problem Description</h3>
                <p class="text-slate-600 text-sm leading-relaxed">
                    {{ $currentJob->description ?: 'No description provided.' }}</p>
            </div>

            {{-- Photo Gallery Preview --}}
            @if ($currentJob->attachments->count() > 0)
                <div class="p-4 border-t border-slate-100">
                    <button wire:click="$set('showPhotoGallery', true)" class="w-full">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-slate-700 text-sm">Photos ({{ $currentJob->attachments->count() }})</h4>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                        <div class="flex gap-2 overflow-x-auto">
                            @foreach ($currentJob->attachments->take(4) as $attachment)
                                <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-slate-100 overflow-hidden">
                                    <img src="{{ $attachment->url }}" class="w-full h-full object-cover" alt="" loading="lazy">
                                </div>
                            @endforeach
                        </div>
                    </button>
                </div>
            @endif

            {{-- Equipment Details --}}
            @if ($currentJob->equipment)
                <div class="border-t border-slate-100">
                    <button wire:click="$toggle('showEquipmentDetails')"
                        class="w-full p-4 flex items-center justify-between text-left">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-slate-700">{{ $currentJob->equipment->name }}</h4>
                                <p class="text-xs text-slate-500">{{ $currentJob->equipment->model ?? 'Equipment Details' }}</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transition-transform {{ $showEquipmentDetails ? 'rotate-180' : '' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    @if ($showEquipmentDetails)
                        <div class="px-4 pb-4 space-y-2 text-sm">
                            @if ($currentJob->equipment->serial_number)
                                <div class="flex justify-between"><span class="text-slate-500">Serial #</span><span
                                        class="font-medium text-slate-700">{{ $currentJob->equipment->serial_number }}</span></div>
                            @endif
                            @if ($currentJob->equipment->model)
                                <div class="flex justify-between"><span class="text-slate-500">Model</span><span
                                        class="font-medium text-slate-700">{{ $currentJob->equipment->model }}</span></div>
                            @endif
                            @if ($currentJob->equipment->manufacturer)
                                <div class="flex justify-between"><span class="text-slate-500">Manufacturer</span><span
                                        class="font-medium text-slate-700">{{ $currentJob->equipment->manufacturer }}</span></div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Navigation Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-slate-900">Navigation</h3>
                @if ($currentJob->travel_minutes)
                    <span class="text-sm text-slate-500">~{{ $currentJob->travel_minutes }} min away</span>
                @endif
            </div>
            <div class="flex gap-2">
                <button wire:click="navigateToJob"
                    class="flex-1 touch-target flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold rounded-xl transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Navigate Here
                </button>
                <button wire:click="copyAddress"
                    class="touch-target flex items-center justify-center px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Check-in/Status Controls --}}
        <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl shadow-lg p-4 text-white">
            <h3 class="font-semibold mb-4">Work Controls</h3>

            @if (!$timerRunning)
                <div class="space-y-2">
                    @if ($jobStatus === 'assigned')
                        <button wire:click="arriveAtSite"
                            class="w-full touch-target flex items-center justify-center gap-3 px-6 py-4 bg-white/20 hover:bg-white/30 active:bg-white/40 backdrop-blur-sm rounded-xl font-bold text-lg transition disabled:opacity-50" wire:loading.attr="disabled">
                            <svg wire:loading.remove wire:target="arriveAtSite" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span wire:loading.remove wire:target="arriveAtSite">Arrive at Site</span>
                            <span wire:loading wire:target="arriveAtSite">Processing...</span>
                        </button>
                    @endif
                    @if ($jobStatus === 'in_progress' && !$workStartTime)
                        <button wire:click="startWork"
                            class="w-full touch-target flex items-center justify-center gap-3 px-6 py-4 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 rounded-xl font-bold text-lg transition shadow-lg disabled:opacity-50" wire:loading.attr="disabled">
                            <svg wire:loading.remove wire:target="startWork" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            </svg>
                            <span wire:loading.remove wire:target="startWork">Start Work</span>
                            <span wire:loading wire:target="startWork">Starting...</span>
                        </button>
                    @endif
                </div>
            @else
                {{-- Timer Display --}}
                <div class="text-center mb-4" x-data="{ elapsed: {{ $elapsedSeconds }} }"
                    x-init="setInterval(() => elapsed++, 1000)">
                    <div class="text-4xl font-bold timer-display mb-1"
                        x-text="Math.floor(elapsed/3600).toString().padStart(2,'0') + ':' + Math.floor((elapsed%3600)/60).toString().padStart(2,'0') + ':' + (elapsed%60).toString().padStart(2,'0')">
                    </div>
                    <div class="text-white/70 text-sm">{{ ucfirst($activityType) }} time</div>
                </div>

                <div class="flex gap-2">
                    <button wire:click="pauseTimer"
                        class="flex-1 touch-target flex items-center justify-center gap-2 px-4 py-3 bg-white/20 hover:bg-white/30 rounded-xl font-semibold transition disabled:opacity-50" wire:loading.attr="disabled">
                        <svg wire:loading.remove wire:target="pauseTimer" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span wire:loading.remove wire:target="pauseTimer">Pause</span>
                        <span wire:loading wire:target="pauseTimer">...</span>
                    </button>
                    <button wire:click="goToSignoff"
                        class="flex-1 touch-target flex items-center justify-center gap-2 px-4 py-3 bg-emerald-500 hover:bg-emerald-600 rounded-xl font-semibold transition disabled:opacity-50" wire:loading.attr="disabled">
                        <svg wire:loading.remove wire:target="goToSignoff" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span wire:loading.remove wire:target="goToSignoff">Complete</span>
                        <span wire:loading wire:target="goToSignoff">...</span>
                    </button>
                </div>
            @endif

            <button wire:click="requestHelp"
                class="w-full mt-3 touch-target flex items-center justify-center gap-2 px-4 py-3 bg-red-500/20 hover:bg-red-500/30 border border-red-400/50 rounded-xl font-semibold transition disabled:opacity-50" wire:loading.attr="disabled">
                <svg wire:loading.remove wire:target="requestHelp" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
                </svg>
                <span wire:loading.remove wire:target="requestHelp">Request Help</span>
                <span wire:loading wire:target="requestHelp">Requesting...</span>
            </button>
        </div>

        {{-- Quick Action Cards --}}
        <div class="grid grid-cols-2 gap-3">
            <button wire:click="goToPhotos"
                class="touch-target p-4 bg-white rounded-2xl shadow-sm border border-slate-200/50 text-left hover:bg-slate-50 active:bg-slate-100 transition">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                </div>
                <h4 class="font-semibold text-slate-900">Photos</h4>
                <p class="text-xs text-slate-500">{{ count($photos) }} captured</p>
            </button>

            <button wire:click="goToNotes"
                class="touch-target p-4 bg-white rounded-2xl shadow-sm border border-slate-200/50 text-left hover:bg-slate-50 active:bg-slate-100 transition">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h4 class="font-semibold text-slate-900">Notes</h4>
                <p class="text-xs text-slate-500">{{ count($workNotes) }} entries</p>
            </button>

            <button wire:click="goToParts"
                class="touch-target p-4 bg-white rounded-2xl shadow-sm border border-slate-200/50 text-left hover:bg-slate-50 active:bg-slate-100 transition">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h4 class="font-semibold text-slate-900">Parts Used</h4>
                <p class="text-xs text-slate-500">{{ count($selectedParts) }} items</p>
            </button>

            <button wire:click="goToTimeTracking"
                class="touch-target p-4 bg-white rounded-2xl shadow-sm border border-slate-200/50 text-left hover:bg-slate-50 active:bg-slate-100 transition">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="font-semibold text-slate-900">Time Log</h4>
                <p class="text-xs text-slate-500">Track hours</p>
            </button>
        </div>
    </div>
@endif