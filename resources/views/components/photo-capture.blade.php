<div x-data="{
        photos: @entangle($attributes->wire('model') ?? 'photos'),
        isCapturing: false,
        stream: null,
        previewUrl: null,
        annotationMode: false,
        annotationCanvas: null,
        annotationCtx: null,
        currentColor: '#ef4444',
        lineWidth: 3,
        
        init() {
            // Initialize with empty array if null
            if (!this.photos) this.photos = [];
        },
        
        async openCamera() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } },
                    audio: false
                });
                this.$refs.video.srcObject = this.stream;
                this.isCapturing = true;
            } catch (err) {
                console.error('Camera access denied:', err);
                alert('Unable to access camera. Please check permissions.');
            }
        },
        
        capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.captureCanvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
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
                    const container = canvas.parentElement;
                    const maxWidth = container.clientWidth;
                    const scale = Math.min(1, maxWidth / img.width);
                    
                    canvas.width = img.width * scale;
                    canvas.height = img.height * scale;
                    
                    this.annotationCtx = canvas.getContext('2d');
                    this.annotationCtx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    this.annotationCtx.strokeStyle = this.currentColor;
                    this.annotationCtx.lineWidth = this.lineWidth;
                    this.annotationCtx.lineCap = 'round';
                };
                img.src = this.previewUrl;
            });
        },
        
        setColor(color) {
            this.currentColor = color;
            if (this.annotationCtx) {
                this.annotationCtx.strokeStyle = color;
            }
        },
        
        getCoordinates(e) {
            const rect = this.$refs.annotationCanvas.getBoundingClientRect();
            const scaleX = this.$refs.annotationCanvas.width / rect.width;
            const scaleY = this.$refs.annotationCanvas.height / rect.height;
            
            if (e.touches && e.touches[0]) {
                return {
                    x: (e.touches[0].clientX - rect.left) * scaleX,
                    y: (e.touches[0].clientY - rect.top) * scaleY
                };
            }
            return {
                x: (e.clientX - rect.left) * scaleX,
                y: (e.clientY - rect.top) * scaleY
            };
        },
        
        startDraw(e) {
            if (!this.annotationMode) return;
            e.preventDefault();
            this.isDrawing = true;
            const coords = this.getCoordinates(e);
            this.lastX = coords.x;
            this.lastY = coords.y;
        },
        
        draw(e) {
            if (!this.isDrawing || !this.annotationMode) return;
            e.preventDefault();
            const coords = this.getCoordinates(e);
            
            this.annotationCtx.beginPath();
            this.annotationCtx.moveTo(this.lastX, this.lastY);
            this.annotationCtx.lineTo(coords.x, coords.y);
            this.annotationCtx.stroke();
            
            this.lastX = coords.x;
            this.lastY = coords.y;
        },
        
        stopDraw() {
            this.isDrawing = false;
        },
        
        savePhoto() {
            const canvas = this.$refs.annotationCanvas;
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            
            this.photos.push({
                id: Date.now(),
                data: dataUrl,
                timestamp: new Date().toISOString()
            });
            
            this.cancelCapture();
        },
        
        cancelCapture() {
            this.stopCamera();
            this.previewUrl = null;
            this.annotationMode = false;
        },
        
        removePhoto(index) {
            this.photos.splice(index, 1);
        },
        
        openFileInput() {
            this.$refs.fileInput.click();
        },
        
        handleFileSelect(e) {
            const files = e.target.files;
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.photos.push({
                        id: Date.now() + Math.random(),
                        data: event.target.result,
                        timestamp: new Date().toISOString()
                    });
                };
                reader.readAsDataURL(file);
            });
            e.target.value = '';
        }
    }" {{ $attributes->whereDoesntStartWith('wire:model') }} class="space-y-4">
    {{-- Camera/File Input Buttons --}}
    <div class="flex flex-wrap gap-2" x-show="!isCapturing && !annotationMode">
        <button type="button" @click="openCamera()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Take Photo
        </button>

        <button type="button" @click="openFileInput()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Upload
        </button>

        <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept="image/*" multiple
            class="hidden">
    </div>

    {{-- Camera Preview --}}
    <div x-show="isCapturing" class="relative rounded-lg overflow-hidden bg-black">
        <video x-ref="video" autoplay playsinline class="w-full max-h-96 object-contain"></video>

        <div class="absolute bottom-4 inset-x-0 flex justify-center gap-4">
            <button type="button" @click="capturePhoto()"
                class="w-16 h-16 bg-white rounded-full shadow-lg flex items-center justify-center hover:scale-105 transition">
                <div class="w-12 h-12 bg-red-500 rounded-full"></div>
            </button>

            <button type="button" @click="cancelCapture()"
                class="w-12 h-12 bg-gray-800/80 rounded-full flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Annotation Mode --}}
    <div x-show="annotationMode" class="space-y-3">
        <div class="relative rounded-lg overflow-hidden bg-gray-100 touch-none">
            <canvas x-ref="annotationCanvas" @mousedown="startDraw($event)" @mousemove="draw($event)"
                @mouseup="stopDraw()" @mouseleave="stopDraw()" @touchstart.passive="startDraw($event)"
                @touchmove.passive="draw($event)" @touchend="stopDraw()" class="w-full cursor-crosshair"></canvas>
        </div>

        {{-- Color Picker --}}
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">Annotate:</span>
            @foreach (['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#000000'] as $color)
                <button type="button" @click="setColor('{{ $color }}')" class="w-6 h-6 rounded-full border-2 transition"
                    :class="currentColor === '{{ $color }}' ? 'border-gray-800 scale-110' : 'border-transparent'"
                    style="background-color: {{ $color }}"></button>
            @endforeach
        </div>

        {{-- Save/Cancel --}}
        <div class="flex gap-2">
            <button type="button" @click="savePhoto()"
                class="flex-1 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition disabled:opacity-50" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="savePhoto">Save Photo</span>
                <span wire:loading wire:target="savePhoto">Saving...</span>
            </button>
            <button type="button" @click="cancelCapture()"
                class="px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">
                Cancel
            </button>
        </div>
    </div>

    {{-- Hidden canvas for capture --}}
    <canvas x-ref="captureCanvas" class="hidden"></canvas>

    {{-- Photo Gallery --}}
    <div x-show="photos && photos.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <template x-for="(photo, index) in photos" :key="photo.id">
            <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100 shadow-sm">
                <img :src="photo.data" class="w-full h-full object-cover" loading="lazy">
                <button type="button" @click="removePhoto(index)"
                    class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="absolute bottom-0 inset-x-0 bg-black/50 text-white text-xs px-2 py-1"
                    x-text="new Date(photo.timestamp).toLocaleTimeString()"></div>
            </div>
        </template>
    </div>
</div>