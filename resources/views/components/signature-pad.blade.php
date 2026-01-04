<div x-data="{
        signatureData: @entangle($attributes->wire('model') ?? 'signatureData'),
        canvas: null,
        ctx: null,
        isDrawing: false,
        lastX: 0,
        lastY: 0,
        isEmpty: true,
        
        init() {
            this.canvas = this.$refs.canvas;
            this.ctx = this.canvas.getContext('2d');
            this.resizeCanvas();
            this.setupContext();
            
            window.addEventListener('resize', () => this.resizeCanvas());
        },
        
        resizeCanvas() {
            const container = this.canvas.parentElement;
            const rect = container.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            
            this.canvas.width = rect.width * dpr;
            this.canvas.height = 200 * dpr;
            this.canvas.style.width = rect.width + 'px';
            this.canvas.style.height = '200px';
            
            this.ctx.scale(dpr, dpr);
            this.setupContext();
        },
        
        setupContext() {
            this.ctx.strokeStyle = '#1e40af';
            this.ctx.lineWidth = 2;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
        },
        
        getCoordinates(e) {
            const rect = this.canvas.getBoundingClientRect();
            if (e.touches && e.touches[0]) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top
                };
            }
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        },
        
        startDrawing(e) {
            e.preventDefault();
            this.isDrawing = true;
            const coords = this.getCoordinates(e);
            this.lastX = coords.x;
            this.lastY = coords.y;
        },
        
        draw(e) {
            if (!this.isDrawing) return;
            e.preventDefault();
            
            const coords = this.getCoordinates(e);
            
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
                this.saveSignature();
            }
        },
        
        saveSignature() {
            if (!this.isEmpty) {
                this.signatureData = this.canvas.toDataURL('image/png');
            }
        },
        
        clear() {
            this.ctx.clearRect(0, 0, this.canvas.width / (window.devicePixelRatio || 1), this.canvas.height / (window.devicePixelRatio || 1));
            this.isEmpty = true;
            this.signatureData = '';
        }
    }" {{ $attributes->whereDoesntStartWith('wire:model') }}>
    {{-- Signature Pad Container --}}
    <div class="relative bg-white rounded-lg border-2 border-dashed border-gray-300 overflow-hidden touch-none"
        :class="{ 'border-blue-400': isDrawing }">

        <canvas x-ref="canvas" class="w-full cursor-crosshair" @mousedown="startDrawing($event)"
            @mousemove="draw($event)" @mouseup="stopDrawing()" @mouseleave="stopDrawing()"
            @touchstart.passive="startDrawing($event)" @touchmove.passive="draw($event)"
            @touchend="stopDrawing()"></canvas>

        {{-- Signature Line --}}
        <div class="absolute bottom-10 left-4 right-4 border-b border-gray-400"></div>
        <div class="absolute bottom-6 left-4 text-xs text-gray-400">Sign above this line</div>

        {{-- Empty State Hint --}}
        <div x-show="isEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <p class="text-gray-400 text-sm">{{ $placeholder ?? 'Draw your signature here' }}</p>
        </div>
    </div>

    {{-- Controls --}}
    <div class="mt-2 flex items-center justify-between">
        <button type="button" @click="clear()"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Clear
        </button>

        <span x-show="!isEmpty" class="inline-flex items-center gap-1 text-xs text-green-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Signature captured
        </span>
    </div>
</div>