{{-- Modals --}}

{{-- Emergency Modal --}}
@if ($showEmergencyModal)
    <div class="fixed inset-0 z-[60] flex items-end justify-center p-4" x-data x-transition>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showEmergencyModal', false)"></div>

        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden animate-slide-up">
            <div class="p-6 text-center border-b border-slate-100">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-1">Request Help</h3>
                <p class="text-sm text-slate-500">Dispatch will be notified immediately</p>
            </div>

            <div class="p-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Describe the issue</label>
                <textarea wire:model="emergencyNote" rows="3"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="What do you need help with?"></textarea>
            </div>

            <div class="p-4 pt-0 flex gap-3">
                <button wire:click="$set('showEmergencyModal', false)"
                    class="flex-1 touch-target py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button wire:click="sendEmergencyAlert"
                    class="flex-1 touch-target py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition">
                    Send Alert
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Request Parts Modal --}}
@if ($showRequestPartsModal)
    <div class="fixed inset-0 z-[60] flex items-end justify-center p-4" x-data x-transition>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showRequestPartsModal', false)"></div>

        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden animate-slide-up">
            <div class="p-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Request Parts Delivery</h3>
                        <p class="text-sm text-slate-500">Parts will be delivered to your location</p>
                    </div>
                </div>
            </div>

            <div class="p-4">
                @if ($currentJob)
                    <div class="mb-4 p-3 bg-slate-50 rounded-xl">
                        <div class="text-xs text-slate-500 mb-1">Delivery Location</div>
                        <div class="font-medium text-slate-900 text-sm">{{ $currentJob->location_address }}</div>
                    </div>
                @endif

                <label class="block text-sm font-semibold text-slate-700 mb-2">Parts Needed</label>
                <textarea wire:model="partsRequestNote" rows="3"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="List the parts you need..."></textarea>
            </div>

            <div class="p-4 pt-0 flex gap-3">
                <button wire:click="$set('showRequestPartsModal', false)"
                    class="flex-1 touch-target py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button wire:click="submitPartsRequest"
                    class="flex-1 touch-target py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                    Submit Request
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Photo Gallery Modal --}}
@if ($showPhotoGallery && $currentJob)
    <div class="fixed inset-0 z-[60] bg-black" x-data="{ currentIndex: 0, scale: 1, translateX: 0, translateY: 0 }"
        x-transition>
        {{-- Close Button --}}
        <button wire:click="$set('showPhotoGallery', false)"
            class="absolute top-4 right-4 z-10 w-12 h-12 bg-black/50 rounded-full flex items-center justify-center text-white touch-target safe-area-top">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Image Counter --}}
        <div
            class="absolute top-4 left-4 z-10 px-3 py-1.5 bg-black/50 rounded-full text-white text-sm font-medium safe-area-top">
            <span x-text="currentIndex + 1"></span> / {{ $currentJob->attachments->count() }}
        </div>

        {{-- Images --}}
        <div class="h-full flex items-center justify-center p-4">
            @foreach ($currentJob->attachments as $index => $attachment)
                <img x-show="currentIndex === {{ $index }}" src="{{ $attachment->url }}"
                    class="max-w-full max-h-full object-contain select-none"
                    :style="{ transform: 'scale(' + scale + ') translate(' + translateX + 'px, ' + translateY + 'px)' }"
                    @dblclick="scale = scale === 1 ? 2.5 : 1; translateX = 0; translateY = 0" alt="Photo {{ $index + 1 }}">
            @endforeach
        </div>

        {{-- Navigation Arrows --}}
        @if ($currentJob->attachments->count() > 1)
            <button @click="currentIndex = Math.max(0, currentIndex - 1)"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/50 rounded-full flex items-center justify-center text-white touch-target">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button @click="currentIndex = Math.min({{ $currentJob->attachments->count() - 1 }}, currentIndex + 1)"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/50 rounded-full flex items-center justify-center text-white touch-target">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @endif

        {{-- Thumbnails --}}
        @if ($currentJob->attachments->count() > 1)
            <div class="absolute bottom-8 inset-x-0 flex justify-center gap-2 px-4 safe-area-bottom">
                @foreach ($currentJob->attachments as $index => $attachment)
                    <button @click="currentIndex = {{ $index }}"
                        class="w-12 h-12 rounded-lg overflow-hidden border-2 transition touch-target"
                        :class="currentIndex === {{ $index }} ? 'border-white' : 'border-transparent opacity-60'">
                        <img src="{{ $attachment->url }}" class="w-full h-full object-cover" alt="">
                    </button>
                @endforeach
            </div>
        @endif
    </div>
@endif