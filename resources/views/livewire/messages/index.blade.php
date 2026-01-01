<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Messages</h1>
                <p class="text-sm text-gray-500">Coordinate with technicians, dispatch, and clients.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Threads</h2>
                <div class="space-y-2">
                    @forelse ($threads as $thread)
                        <button
                            class="w-full text-left px-3 py-2 rounded-md {{ $activeThread && $activeThread->id === $thread->id ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50' }}"
                            wire:click="selectThread({{ $thread->id }})"
                        >
                            <p class="text-sm font-medium">{{ $thread->subject ?? 'Conversation' }}</p>
                            <p class="text-xs text-gray-500">Updated {{ $thread->updated_at?->diffForHumans() }}</p>
                        </button>
                    @empty
                        <p class="text-sm text-gray-500">No threads yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Message</h2>
                    <form wire:submit.prevent="startThread" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500">Recipient</label>
                            <select wire:model="newThread.recipient_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Select user</option>
                                @foreach ($recipients as $recipient)
                                    <option value="{{ $recipient->id }}">{{ $recipient->name }}</option>
                                @endforeach
                            </select>
                            @error('newThread.recipient_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Work Order (optional)</label>
                            <select wire:model="newThread.work_order_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">None</option>
                                @foreach ($workOrders as $order)
                                    <option value="{{ $order->id }}">#{{ $order->id }} {{ $order->subject }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-gray-500">Subject</label>
                            <input wire:model="newThread.subject" class="mt-1 w-full rounded-md border-gray-300" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-gray-500">Message</label>
                            <textarea wire:model="newThread.message" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                            @error('newThread.message') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Send</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Conversation</h2>
                    @if ($activeThread)
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            @foreach ($activeThread->messages as $message)
                                <div class="border-b border-gray-100 pb-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $message->user?->name }}</p>
                                    <p class="text-sm text-gray-700">{{ $message->body }}</p>
                                    <p class="text-xs text-gray-400">{{ $message->created_at?->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                        <form wire:submit.prevent="sendReply" class="mt-4">
                            <textarea wire:model="replyBody" class="w-full rounded-md border-gray-300" rows="2" placeholder="Type a reply"></textarea>
                            @error('replyBody') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <button class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md">Send Reply</button>
                        </form>
                    @else
                        <p class="text-sm text-gray-500">Select a thread to view messages.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
