{{-- Quick Actions Menu --}}
@if ($showQuickActions)
    <div class="fixed inset-0 z-50" x-data x-transition>
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="toggleQuickActions"></div>

        {{-- Menu Panel --}}
        <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl shadow-2xl safe-area-bottom pb-4 animate-slide-up">
            <div class="w-12 h-1.5 bg-slate-300 rounded-full mx-auto mt-3 mb-4"></div>

            <h3 class="font-bold text-slate-900 text-lg px-6 mb-4">Quick Actions</h3>

            <div class="space-y-1 px-4">
                {{-- Emergency Support --}}
                <button wire:click="emergencyContact"
                    class="w-full touch-target flex items-center gap-4 p-4 hover:bg-red-50 active:bg-red-100 rounded-xl transition text-left">
                    <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900">Emergency Support</div>
                        <div class="text-sm text-slate-500">Get immediate help</div>
                    </div>
                </button>

                {{-- Request Parts Delivery --}}
                <button wire:click="requestPartsDelivery"
                    class="w-full touch-target flex items-center gap-4 p-4 hover:bg-slate-50 active:bg-slate-100 rounded-xl transition text-left">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900">Request Parts Delivery</div>
                        <div class="text-sm text-slate-500">Get parts delivered to site</div>
                    </div>
                </button>

                {{-- Check Inventory --}}
                <button wire:click="checkInventory"
                    class="w-full touch-target flex items-center gap-4 p-4 hover:bg-slate-50 active:bg-slate-100 rounded-xl transition text-left">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900">Check Inventory</div>
                        <div class="text-sm text-slate-500">View available parts</div>
                    </div>
                </button>

                {{-- View Today's Schedule --}}
                <button wire:click="viewTodaySchedule"
                    class="w-full touch-target flex items-center gap-4 p-4 hover:bg-slate-50 active:bg-slate-100 rounded-xl transition text-left">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900">Today's Schedule</div>
                        <div class="text-sm text-slate-500">View all jobs for today</div>
                    </div>
                </button>

                {{-- Report Problem --}}
                <button wire:click="$set('showEmergencyModal', true); $set('showQuickActions', false)"
                    class="w-full touch-target flex items-center gap-4 p-4 hover:bg-slate-50 active:bg-slate-100 rounded-xl transition text-left">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900">Report Problem</div>
                        <div class="text-sm text-slate-500">Flag an issue with this job</div>
                    </div>
                </button>
            </div>

            {{-- Close Button --}}
            <div class="px-4 mt-4">
                <button wire:click="toggleQuickActions"
                    class="w-full touch-target py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                    Close
                </button>
            </div>
        </div>
    </div>
@endif

<style>
    @keyframes slide-up {
        from {
            transform: translateY(100%);
        }

        to {
            transform: translateY(0);
        }
    }

    .animate-slide-up {
        animation: slide-up 0.25s ease-out;
    }
</style>