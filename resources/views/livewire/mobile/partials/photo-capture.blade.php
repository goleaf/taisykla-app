{{-- Photo Capture Screen --}}
<div class="px-4 py-4 space-y-4">
    {{-- Photo Label Selector --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <label class="block text-sm font-semibold text-slate-700 mb-3">Photo Type</label>
        <div class="flex gap-2">
            @foreach (['before' => 'Before', 'during' => 'During', 'after' => 'After'] as $key => $label)
                    <button wire:click="setPhotoLabel('{{ $key }}')" class="flex-1 touch-target px-4 py-3 rounded-xl text-sm font-semibold transition
                            {{ $photoLabel === $key
                ? ($key === 'before' ? 'bg-amber-500 text-white' : ($key === 'during' ? 'bg-blue-500 text-white' : 'bg-green-500 text-white'))
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                    </button>
            @endforeach
        </div>
    </div>

    {{-- Camera Component --}}
    <div x-data="{
        photos: @entangle('photos'),
        isCapturing: false,
        stream: null,
        previewUrl: null,
        annotationMode: false,
        annotationCtx: null,
        currentColor: '#ef4444',
        isDrawing: false,
        lastX: 0,
        lastY: 0,
        uploadProgress: 0,
        
        async openCamera() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } },
                    audio: false
                });
                this.$refs.video.srcObject = this.stream;
                this.isCapturing = true;
            } catch (err) {
                alert('Unable to access camera. Please check permissions.');
            }
        },
        
        capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.captureCanvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            this.previewUrl = canvas.toDataURL('image/jpeg', 0.85);
            this.stopCamera();
            this.initAnnotation();
        },
        
        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.isCapturing = false;
        },
        
        initAnnotation() {
            this.annotationMode = true;
            this.$nextTick(() => {
                const img = new Image();
                img.onload = () => {
                    const canvas = this.$refs.annotationCanvas;
                    const maxWidth = canvas.parentElement.clientWidth;
                    const scale = Math.min(1, maxWidth / img.width);
                    canvas.width = img.width * scale;
                    canvas.height = img.height * scale;
                    this.annotationCtx = canvas.getContext('2d');
                    this.annotationCtx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    this.annotationCtx.strokeStyle = this.currentColor;
                    this.annotationCtx.lineWidth = 4;
                    this.annotationCtx.lineCap = 'round';
                };
                img.src = this.previewUrl;
            });
        },
        
        setColor(color) {
            this.currentColor = color;
            if (this.annotationCtx) this.annotationCtx.strokeStyle = color;
        },
        
        getCoords(e) {
            const rect = this.$refs.annotationCanvas.getBoundingClientRect();
            const scaleX = this.$refs.annotationCanvas.width / rect.width;
            const scaleY = this.$refs.annotationCanvas.height / rect.height;
            const touch = e.touches?.[0] || e;
            return { x: (touch.clientX - rect.left) * scaleX, y: (touch.clientY - rect.top) * scaleY };
        },
        
        startDraw(e) {
            if (!this.annotationMode) return;
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
            this.annotationCtx.beginPath();
            this.annotationCtx.moveTo(this.lastX, this.lastY);
            this.annotationCtx.lineTo(coords.x, coords.y);
            this.annotationCtx.stroke();
            this.lastX = coords.x;
            this.lastY = coords.y;
        },
        
        stopDraw() { this.isDrawing = false; },
        
        savePhoto() {
            const dataUrl = this.$refs.annotationCanvas.toDataURL('image/jpeg', 0.85);
            $wire.savePhoto({ id: Date.now(), data: dataUrl, timestamp: new Date().toISOString() });
            this.cancelCapture();
        },
        
        cancelCapture() {
            this.stopCamera();
            this.previewUrl = null;
            this.annotationMode = false;
        },
        
        removePhoto(index) {
            $wire.deletePhoto(index);
        }
    }" class="space-y-4">
        {{-- Capture Buttons --}}
        <div x-show="!isCapturing && !annotationMode" class="flex gap-3">
            <button @click="openCamera()"
                class="flex-1 touch-target flex items-center justify-center gap-3 px-6 py-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-2xl shadow-lg transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Take Photo
            </button>
            <label
                class="touch-target flex items-center justify-center px-6 py-4 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-2xl border border-slate-200 cursor-pointer transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <input type="file" accept="image/*" multiple class="hidden"
                    @change="Array.from($event.target.files).forEach(f => { const r = new FileReader(); r.onload = e => $wire.savePhoto({ id: Date.now() + Math.random(), data: e.target.result, timestamp: new Date().toISOString() }); r.readAsDataURL(f); })">
            </label>
        </div>

        {{-- Camera Preview --}}
        <div x-show="isCapturing" class="relative rounded-2xl overflow-hidden bg-black">
            <video x-ref="video" autoplay playsinline class="w-full max-h-[60vh] object-contain"></video>
            <div class="absolute bottom-6 inset-x-0 flex justify-center gap-6">
                <button @click="capturePhoto()"
                    class="w-20 h-20 bg-white rounded-full shadow-xl flex items-center justify-center hover:scale-105 active:scale-95 transition">
                    <div class="w-16 h-16 bg-red-500 rounded-full"></div>
                </button>
                <button @click="cancelCapture()"
                    class="w-14 h-14 bg-slate-800/80 rounded-full flex items-center justify-center text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Annotation Mode --}}
        <div x-show="annotationMode" class="space-y-3">
            <div class="relative rounded-2xl overflow-hidden bg-slate-100 touch-none">
                <canvas x-ref="annotationCanvas" @mousedown="startDraw" @mousemove="draw" @mouseup="stopDraw"
                    @mouseleave="stopDraw" @touchstart.passive="startDraw" @touchmove.passive="draw"
                    @touchend="stopDraw" class="w-full cursor-crosshair"></canvas>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium">Annotate:</span>
                @foreach (['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#000000'] as $color)
                    <button @click="setColor('{{ $color }}')" class="w-8 h-8 rounded-full border-2 transition touch-target"
                        :class="currentColor === '{{ $color }}' ? 'border-slate-800 scale-110' : 'border-transparent'"
                        style="background-color: {{ $color }}"></button>
                @endforeach
            </div>
            <div class="flex gap-3">
                <button @click="savePhoto()"
                    class="flex-1 touch-target py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition">Save
                    Photo</button>
                <button @click="cancelCapture()"
                    class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl transition">Cancel</button>
            </div>
        </div>

        <canvas x-ref="captureCanvas" class="hidden"></canvas>

        {{-- Photo Gallery --}}
        <div x-show="photos && photos.length > 0" class="space-y-3">
            <h3 class="font-semibold text-slate-900">Captured Photos ({{ count($photos) }})</h3>
            <div class="grid grid-cols-2 gap-3">
                <template x-for="(photo, index) in photos" :key="photo.id">
                    <div class="relative group aspect-square rounded-xl overflow-hidden bg-slate-100 shadow-sm">
                        <img :src="photo.data" class="w-full h-full object-cover" loading="lazy">
                        <button @click="removePhoto(index)"
                            class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition touch-target">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent p-2">
                            <span class="text-white text-xs font-semibold uppercase"
                                x-text="photo.label || '{{ $photoLabel }}'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Upload Progress --}}
        <div x-show="uploadProgress > 0 && uploadProgress < 100"
            class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <div class="flex-1">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700">Uploading...</span>
                        <span class="text-slate-500" x-text="uploadProgress + '%'"></span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-600 rounded-full transition-all"
                            :style="{ width: uploadProgress + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>