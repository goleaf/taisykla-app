{{-- Customer Sign-off Screen --}}
<div class="px-4 py-4 space-y-4">
    {{-- Work Summary --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <h3 class="font-semibold text-slate-900 mb-4">Work Summary</h3>

        @if ($currentJob)
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Job #</span>
                    <span class="font-medium text-slate-900">{{ $currentJob->id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Customer</span>
                    <span class="font-medium text-slate-900">{{ $currentJob->organization?->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Work Type</span>
                    <span class="font-medium text-slate-900">{{ $currentJob->category?->name ?? 'General Service' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Photos Taken</span>
                    <span class="font-medium text-slate-900">{{ count($photos) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Parts Used</span>
                    <span class="font-medium text-slate-900">{{ count($selectedParts) }} items</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-slate-500">Total Parts Cost</span>
                    <span class="font-bold text-slate-900">${{ number_format($partsTotal, 2) }}</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Signature Pad --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900">Customer Signature</h3>
            <p class="text-sm text-slate-500">Please sign below to confirm work completion</p>
        </div>

        <div class="p-4" x-data="{
            signatureData: @entangle('signatureData'),
            canvas: null,
            ctx: null,
            isDrawing: false,
            lastX: 0,
            lastY: 0,
            isEmpty: true,
            
            init() {
                this.canvas = this.$refs.signatureCanvas;
                this.ctx = this.canvas.getContext('2d');
                this.resizeCanvas();
                this.setupContext();
                window.addEventListener('resize', () => this.resizeCanvas());
                
                Livewire.on('clear-signature', () => this.clear());
            },
            
            resizeCanvas() {
                const rect = this.canvas.parentElement.getBoundingClientRect();
                const dpr = window.devicePixelRatio || 1;
                this.canvas.width = rect.width * dpr;
                this.canvas.height = 180 * dpr;
                this.canvas.style.width = rect.width + 'px';
                this.canvas.style.height = '180px';
                this.ctx.scale(dpr, dpr);
                this.setupContext();
            },
            
            setupContext() {
                this.ctx.strokeStyle = '#1e40af';
                this.ctx.lineWidth = 3;
                this.ctx.lineCap = 'round';
                this.ctx.lineJoin = 'round';
            },
            
            getCoords(e) {
                const rect = this.canvas.getBoundingClientRect();
                const touch = e.touches?.[0] || e;
                return { x: touch.clientX - rect.left, y: touch.clientY - rect.top };
            },
            
            startDrawing(e) {
                e.preventDefault();
                this.isDrawing = true;
                const coords = this.getCoords(e);
                this.lastX = coords.x;
                this.lastY = coords.y;
            },
            
            draw(e) {
                if (!this.isDrawing) return;
                e.preventDefault();
                const coords = this.getCoords(e);
                this.ctx.beginPath();
                this.ctx.moveTo(this.lastX, this.lastY);
                this.ctx.lineTo(coords.x, coords.y);
                this.ctx.stroke();
                this.lastX = coords.x;
                this.lastY = coords.y;
                this.isEmpty = false;
            },
            
            stopDrawing() {
                if (this.isDrawing) {
                    this.isDrawing = false;
                    if (!this.isEmpty) {
                        this.signatureData = this.canvas.toDataURL('image/png');
                    }
                }
            },
            
            clear() {
                const dpr = window.devicePixelRatio || 1;
                this.ctx.clearRect(0, 0, this.canvas.width / dpr, this.canvas.height / dpr);
                this.isEmpty = true;
                this.signatureData = '';
            }
        }">
            <div class="relative bg-slate-50 rounded-xl border-2 border-dashed border-slate-300 overflow-hidden touch-none"
                :class="{ 'border-blue-400': isDrawing }">
                <canvas x-ref="signatureCanvas" class="w-full cursor-crosshair" @mousedown="startDrawing"
                    @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                    @touchstart.passive="startDrawing" @touchmove.passive="draw" @touchend="stopDrawing"></canvas>

                <div class="absolute bottom-8 left-4 right-4 border-b border-slate-400"></div>
                <div class="absolute bottom-4 left-4 text-xs text-slate-400">Sign above this line</div>

                <div x-show="isEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <p class="text-slate-400 text-sm">Draw signature here</p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <button @click="clear()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition touch-target">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Clear
                </button>
                <span x-show="!isEmpty" class="inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Signature captured
                </span>
            </div>
        </div>
    </div>

    {{-- Customer Name --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <label class="block text-sm font-semibold text-slate-700 mb-2">Customer Name (Print)</label>
        <input type="text" wire:model="customerName"
            class="w-full touch-target px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Enter customer name">
    </div>

    {{-- Satisfaction Rating --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <label class="block text-sm font-semibold text-slate-700 mb-3">How satisfied are you with the service?</label>
        <div class="flex justify-center gap-2">
            @for ($i = 1; $i <= 5; $i++)
                <button wire:click="setRating({{ $i }})"
                    class="touch-target w-14 h-14 rounded-xl text-2xl transition {{ $satisfactionRating >= $i ? 'bg-amber-100' : 'bg-slate-100' }} hover:scale-110">
                    {{ $satisfactionRating >= $i ? '⭐' : '☆' }}
                </button>
            @endfor
        </div>
        <p class="text-center text-sm text-slate-500 mt-2">
            {{ $satisfactionRating >= 4 ? 'Excellent!' : ($satisfactionRating >= 3 ? 'Good' : 'We\'ll do better') }}
        </p>
    </div>

    {{-- Additional Comments --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <label class="block text-sm font-semibold text-slate-700 mb-2">Additional Comments (Optional)</label>
        <textarea wire:model="additionalComments" rows="3"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Any additional feedback..."></textarea>
    </div>

    {{-- Submit Button --}}
    <button wire:click="submitCompletion"
        class="w-full touch-target py-4 bg-gradient-to-r from-emerald-500 to-green-500 hover:from-emerald-600 hover:to-green-600 active:from-emerald-700 active:to-green-700 text-white font-bold text-lg rounded-2xl shadow-lg transition"
        {{ empty($signatureData) ? 'disabled' : '' }}
        :class="{ 'opacity-50 cursor-not-allowed': {{ empty($signatureData) ? 'true' : 'false' }} }">
        <span class="inline-flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Submit Completion
        </span>
    </button>
</div>