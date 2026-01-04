{{-- Work Notes Screen --}}
<div class="px-4 py-4 space-y-4">
    {{-- Note Input --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
        {{-- Category Selector --}}
        <div class="p-4 border-b border-slate-100">
            <label class="block text-sm font-semibold text-slate-700 mb-3">Note Category</label>
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1">
                @foreach (['diagnosis' => '🔍 Diagnosis', 'repair' => '🔧 Repair', 'testing' => '✅ Testing', 'other' => '📝 Other'] as $key => $label)
                    <button wire:click="setNoteCategory('{{ $key }}')"
                        class="flex-shrink-0 touch-target px-4 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition
                            {{ $noteCategory === $key ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Text Input with Voice --}}
        <div class="p-4" x-data="{
            isRecording: false,
            recognition: null,
            
            init() {
                if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    this.recognition = new SpeechRecognition();
                    this.recognition.continuous = true;
                    this.recognition.interimResults = true;
                    
                    this.recognition.onresult = (event) => {
                        let transcript = '';
                        for (let i = event.resultIndex; i < event.results.length; i++) {
                            transcript += event.results[i][0].transcript;
                        }
                        $wire.set('noteContent', $wire.get('noteContent') + transcript);
                    };
                    
                    this.recognition.onend = () => { this.isRecording = false; };
                }
            },
            
            toggleRecording() {
                if (this.isRecording) {
                    this.recognition?.stop();
                } else {
                    this.recognition?.start();
                }
                this.isRecording = !this.isRecording;
            }
        }">
            <div class="relative">
                <textarea wire:model="noteContent" rows="4"
                    class="w-full px-4 py-3 pr-12 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400"
                    placeholder="Enter your work notes here..."></textarea>

                {{-- Voice Input Button --}}
                <button @click="toggleRecording()"
                    class="absolute right-3 bottom-3 w-10 h-10 rounded-full flex items-center justify-center transition touch-target"
                    :class="isRecording ? 'bg-red-500 text-white animate-pulse' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </button>
            </div>

            <button wire:click="saveNote"
                class="w-full mt-3 touch-target py-3.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl transition shadow-sm">
                Save Note
            </button>
        </div>
    </div>

    {{-- Quick Templates --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-4">
        <h3 class="font-semibold text-slate-900 mb-3">Quick Templates</h3>
        <div class="space-y-2">
            @foreach ($noteTemplates as $index => $template)
                <button wire:click="applyTemplate({{ $index }})"
                    class="w-full text-left touch-target px-4 py-3 bg-slate-50 hover:bg-slate-100 active:bg-slate-200 rounded-xl text-sm text-slate-700 transition flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ $template['name'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Previous Notes --}}
    @if (count($workNotes) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Previous Notes ({{ count($workNotes) }})</h3>
            </div>
            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @foreach ($workNotes as $note)
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $note['category'] === 'diagnosis' ? 'bg-purple-100 text-purple-700' :
                    ($note['category'] === 'repair' ? 'bg-blue-100 text-blue-700' :
                        ($note['category'] === 'testing' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700')) }}">
                                {{ ucfirst($note['category']) }}
                            </span>
                            <span class="text-xs text-slate-400">
                                {{ \Carbon\Carbon::parse($note['timestamp'])->format('g:i A') }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $note['content'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>