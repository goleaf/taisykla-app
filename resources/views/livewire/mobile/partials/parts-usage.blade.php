{{-- Parts Usage Screen --}}
<div class="px-4 py-4 space-y-4">
    {{-- Barcode Scanner --}}
    <div x-data="{
        scanning: false,
        stream: null,
        
        async startScan() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                this.$refs.scanVideo.srcObject = this.stream;
                this.scanning = true;
                // In production, integrate with a barcode library like QuaggaJS
            } catch (err) {
                alert('Camera access required for scanning');
            }
        },
        
        stopScan() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
            }
            this.scanning = false;
        },
        
        simulateScan() {
            // For demo - in production this would be detected from camera
            const barcode = prompt('Enter barcode/SKU:');
            if (barcode) $wire.handleBarcodeScan(barcode);
            this.stopScan();
        }
    }" class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-4 text-white">
        <div x-show="!scanning">
            <h3 class="font-semibold mb-3">Scan Part Barcode</h3>
            <button @click="startScan()"
                class="w-full touch-target flex items-center justify-center gap-3 px-6 py-4 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl font-semibold transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                Scan Barcode
            </button>
        </div>

        <div x-show="scanning" class="space-y-3">
            <div class="relative rounded-xl overflow-hidden bg-black aspect-video">
                <video x-ref="scanVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-64 h-32 border-2 border-white/50 rounded-lg"></div>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="simulateScan()"
                    class="flex-1 touch-target py-3 bg-white/20 rounded-xl font-semibold">Manual Entry</button>
                <button @click="stopScan()"
                    class="touch-target px-6 py-3 bg-red-500/30 rounded-xl font-semibold">Cancel</button>
            </div>
        </div>
    </div>

    {{-- Search Parts --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="search" wire:model.live.debounce.300ms="partSearch"
                class="w-full touch-target pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Search parts by name or SKU...">
        </div>

        {{-- Search Results --}}
        @if (strlen($partSearch) >= 2)
            <div class="mt-3 space-y-2 max-h-48 overflow-y-auto">
                @forelse ($searchResults as $part)
                    <button wire:click="addPart({{ $part->id }})"
                        class="w-full touch-target flex items-center justify-between p-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition text-left">
                        <div>
                            <div class="font-medium text-slate-900 text-sm">{{ $part->name }}</div>
                            <div class="text-xs text-slate-500">{{ $part->sku }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-slate-700">${{ number_format($part->unit_cost ?? 0, 2) }}
                            </div>
                            <div
                                class="text-xs {{ ($part->inventory_items_sum_quantity ?? 0) > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $part->inventory_items_sum_quantity ?? 0 }} in stock
                            </div>
                        </div>
                    </button>
                @empty
                    <p class="text-sm text-slate-500 text-center py-4">No parts found</p>
                @endforelse
            </div>
        @endif
    </div>

    {{-- Favorite Parts --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <h3 class="font-semibold text-slate-900 mb-3">Quick Add - Favorites</h3>
        <div class="grid grid-cols-2 gap-2">
            @foreach ($favoriteParts as $part)
                <button wire:click="addFromFavorites({{ $part['id'] }})"
                    class="touch-target p-3 bg-slate-50 hover:bg-slate-100 active:bg-slate-200 rounded-xl text-left transition">
                    <div class="font-medium text-slate-900 text-sm truncate">{{ $part['name'] }}</div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-slate-500">{{ $part['sku'] }}</span>
                        <span
                            class="text-xs {{ $part['available'] > 0 ? 'text-green-600' : 'text-red-500' }}">{{ $part['available'] }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Selected Parts --}}
    @if (count($selectedParts) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Parts Used</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($selectedParts as $index => $part)
                    <div class="p-4 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-slate-900 text-sm">{{ $part['name'] }}</div>
                            <div class="text-xs text-slate-500">{{ $part['sku'] }} • ${{ number_format($part['unit_cost'], 2) }}
                                each</div>
                        </div>

                        {{-- Quantity Selector --}}
                        <div class="flex items-center gap-1 bg-slate-100 rounded-lg">
                            <button wire:click="updatePartQuantity({{ $index }}, {{ $part['quantity'] - 1 }})"
                                class="touch-target w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-200 rounded-l-lg transition"
                                {{ $part['quantity'] <= 1 ? 'disabled' : '' }}>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <span class="w-8 text-center font-semibold text-slate-900">{{ $part['quantity'] }}</span>
                            <button wire:click="updatePartQuantity({{ $index }}, {{ $part['quantity'] + 1 }})"
                                class="touch-target w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-200 rounded-r-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <button wire:click="removePart({{ $index }})"
                            class="touch-target w-10 h-10 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>

            {{-- Running Total --}}
            <div class="p-4 bg-slate-50 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700">Total Parts Cost</span>
                    <span class="text-xl font-bold text-slate-900">${{ number_format($partsTotal, 2) }}</span>
                </div>
            </div>
        </div>
    @endif
</div>