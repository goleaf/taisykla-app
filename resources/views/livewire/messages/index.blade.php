<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Messages</h1>
                <p class="text-sm text-gray-500">Coordinate with technicians, dispatch, and clients.</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span>{{ $threads->count() }} threads</span>
                <span>{{ $unreadCount }} unread</span>
                @if ($canSend)
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md" wire:click="startComposer">New Thread</button>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-4">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                    <label class="text-xs text-gray-500">Search</label>
                    <input wire:model.debounce.300ms="threadSearch" class="mt-1 w-full rounded-md border-gray-300" placeholder="Subject, person, or work order" />
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                    <div class="flex items-center justify-between p-4">
                        <h2 class="text-lg font-semibold text-gray-900">Threads</h2>
                        <span class="text-xs text-gray-500">{{ $threads->count() }} total</span>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse ($threads as $thread)
                            @php
                                $lastMessage = $thread->messages->first();
                                $selfParticipant = $thread->participants->firstWhere('user_id', $user?->id);
                                $lastReadAt = $selfParticipant?->last_read_at;
                                $isUnread = $lastMessage && (! $lastReadAt || $lastMessage->created_at?->gt($lastReadAt));
                                $participantNames = $thread->participants
                                    ->filter(fn ($participant) => $participant->user_id !== $user?->id)
                                    ->map(fn ($participant) => $participant->user?->name)
                                    ->filter()
                                    ->values();
                            @endphp
                            <button
                                class="w-full text-left px-4 py-3 transition {{ $activeThread && $activeThread->id === $thread->id ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50' }}"
                                wire:click="selectThread({{ $thread->id }})"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium {{ $isUnread ? 'text-gray-900' : 'text-gray-700' }}">
                                        {{ $thread->subject ?? 'Conversation' }}
                                    </p>
                                    <span class="text-xs text-gray-400">{{ $thread->updated_at?->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                    <span>
                                        {{ $participantNames->isNotEmpty() ? $participantNames->implode(', ') : 'No other participants' }}
                                    </span>
                                    @if ($thread->work_order_id)
                                        <span>· WO #{{ $thread->work_order_id }}</span>
                                    @endif
                                    <span>· {{ $thread->messages_count }} msg</span>
                                    @if ($isUnread)
                                        <span class="inline-flex h-2 w-2 rounded-full bg-indigo-500"></span>
                                    @endif
                                </div>
                                @if ($lastMessage)
                                    <p class="mt-2 text-xs text-gray-500">
                                        {{ \Illuminate\Support\Str::limit($lastMessage->body, 90) }}
                                    </p>
                                @endif
                            </button>
                        @empty
                            <div class="p-4 text-sm text-gray-500">No threads yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Conversation</h2>
                        @if ($activeThread)
                            <span class="text-xs text-gray-500">Thread #{{ $activeThread->id }}</span>
                        @endif
                    </div>

                    @if ($activeThread)
                        <div class="mb-4 space-y-1">
                            <p class="text-sm font-medium text-gray-900">{{ $activeThread->subject ?? 'Conversation' }}</p>
                            <p class="text-xs text-gray-500">
                                Participants:
                                {{ $activeThread->participants->pluck('user.name')->filter()->implode(', ') }}
                            </p>
                            @if ($activeThread->workOrder)
                                <a class="text-xs text-indigo-600" href="{{ route('work-orders.show', $activeThread->workOrder) }}" wire:navigate>
                                    View Work Order #{{ $activeThread->workOrder->id }}
                                </a>
                            @endif
                        </div>

                        <div class="space-y-4 max-h-96 overflow-y-auto border border-gray-100 rounded-lg p-4 bg-gray-50">
                            @forelse ($activeMessages as $message)
                                @php
                                    $isOwn = $user && $message->user_id === $user->id;
                                @endphp
                                <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-xl rounded-lg px-4 py-2 {{ $isOwn ? 'bg-indigo-50 text-indigo-900' : 'bg-white text-gray-800' }}">
                                        <p class="text-xs text-gray-400">
                                            {{ $message->user?->name ?? 'Unknown' }} · {{ $message->created_at?->format('M d, H:i') }}
                                        </p>
                                        <p class="text-sm whitespace-pre-line">{{ $message->body }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No messages yet.</p>
                            @endforelse
                        </div>

                        @if ($canSend)
                            <form wire:submit.prevent="sendReply" class="mt-4 space-y-2">
                                <textarea wire:model="replyBody" class="w-full rounded-md border-gray-300" rows="2" placeholder="Type a reply"></textarea>
                                @error('replyBody') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                <div class="flex items-center justify-end">
                                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Send Reply</button>
                                </div>
                            </form>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">Select a thread to view messages.</p>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">New Thread</h2>
                        @if ($showComposer)
                            <button class="text-sm text-gray-500" type="button" wire:click="cancelComposer">Close</button>
                        @endif
                    </div>

                    @if ($showComposer && $canSend)
                        <form wire:submit.prevent="createThread" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-gray-500">Recipient</label>
                                <select wire:model="composer.recipient_id" class="mt-1 w-full rounded-md border-gray-300">
                                    <option value="">Select user</option>
                                    @foreach ($recipients as $recipient)
                                        <option value="{{ $recipient->id }}">{{ $recipient->name }}</option>
                                    @endforeach
                                </select>
                                @error('composer.recipient_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Work Order (optional)</label>
                                <select wire:model="composer.work_order_id" class="mt-1 w-full rounded-md border-gray-300">
                                    <option value="">None</option>
                                    @foreach ($workOrders as $order)
                                        <option value="{{ $order->id }}">#{{ $order->id }} {{ $order->subject }}</option>
                                    @endforeach
                                </select>
                                @error('composer.work_order_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs text-gray-500">Subject</label>
                                <input wire:model="composer.subject" class="mt-1 w-full rounded-md border-gray-300" />
                                @error('composer.subject') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs text-gray-500">Message</label>
                                <textarea wire:model="composer.message" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                                @error('composer.message') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2 flex items-center gap-2">
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Send</button>
                                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="cancelComposer">Cancel</button>
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-gray-500">Select "New Thread" to start a conversation.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
