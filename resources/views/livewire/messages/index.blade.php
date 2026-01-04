<div class="min-h-screen bg-slate-100">
    <style>
        :root {
            --mail-ink: 15, 23, 42;
            --mail-sky: 14, 165, 233;
            --mail-emerald: 16, 185, 129;
            --mail-amber: 245, 158, 11;
            --mail-rose: 244, 63, 94;
        }

        .mail-surface {
            background-image:
                radial-gradient(circle at 12% 15%, rgba(var(--mail-sky), 0.18), transparent 45%),
                radial-gradient(circle at 80% 12%, rgba(var(--mail-emerald), 0.15), transparent 40%),
                linear-gradient(140deg, rgba(248, 250, 252, 0.95), rgba(226, 232, 240, 0.95));
        }

        .mail-glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .mail-shadow {
            box-shadow: 0 24px 45px rgba(15, 23, 42, 0.08);
        }

        .mail-editor {
            min-height: 160px;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Messaging Hub</p>
                <h1 class="text-3xl sm:text-4xl font-['DM_Serif_Display'] text-slate-900">Communication Center</h1>
                <p class="text-sm text-slate-500">Unified inbox for work orders, dispatch, and customer updates.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-2xl bg-white border border-slate-200 px-4 py-3 shadow-sm">
                    <p class="text-xs text-slate-500">Unread</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $unreadCount }}</p>
                </div>
                <div class="rounded-2xl bg-white border border-slate-200 px-4 py-3 shadow-sm">
                    <p class="text-xs text-slate-500">Threads</p>
                    <p class="text-lg font-semibold text-slate-900">{{ $threads->total() }}</p>
                </div>
                @if ($canSend)
                    <button
                        class="min-h-[44px] rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white shadow-lg"
                        wire:click="startComposer"
                    >
                        Compose Message
                    </button>
                @endif
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="lg:hidden sticky top-0 z-20">
            <div class="mail-glass rounded-2xl border border-slate-200 p-2 shadow-md flex items-center justify-between">
                <button class="flex-1 px-3 py-2 text-xs font-semibold text-slate-600" data-pane-toggle="folders">Folders</button>
                <button class="flex-1 px-3 py-2 text-xs font-semibold text-slate-600" data-pane-toggle="list">List</button>
                <button class="flex-1 px-3 py-2 text-xs font-semibold text-slate-600" data-pane-toggle="message">Message</button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)_minmax(0,1.2fr)]">
            <aside data-pane="folders" class="hidden lg:block space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 mail-shadow">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">Folders</h2>
                        <span class="text-xs text-slate-500">{{ $unreadCount }} unread</span>
                    </div>
                    <div class="mt-4 space-y-2 text-sm">
                        @php
                            $folderItems = [
                                ['key' => 'inbox', 'label' => 'Inbox', 'count' => $folderCounts['inbox'] ?? 0],
                                ['key' => 'sent', 'label' => 'Sent', 'count' => $folderCounts['sent'] ?? 0],
                                ['key' => 'drafts', 'label' => 'Drafts', 'count' => $folderCounts['drafts'] ?? 0],
                                ['key' => 'archived', 'label' => 'Archived', 'count' => $folderCounts['archived'] ?? 0],
                                ['key' => 'starred', 'label' => 'Starred', 'count' => $folderCounts['starred'] ?? 0],
                                ['key' => 'work_orders', 'label' => 'Work Order Messages', 'count' => $folderCounts['work_orders'] ?? 0],
                            ];
                        @endphp
                        @foreach ($folderItems as $item)
                            <button
                                class="flex w-full items-center justify-between rounded-xl px-3 py-2 transition {{ $activeFolder === $item['key'] ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                                wire:click="$set('activeFolder', '{{ $item['key'] }}')"
                            >
                                <span>{{ $item['label'] }}</span>
                                <span class="text-xs">{{ $item['count'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 mail-shadow">
                    <h3 class="text-sm font-semibold text-slate-900">Custom Folders</h3>
                    <div class="mt-3 space-y-2">
                        @forelse ($folders as $folder)
                            <button
                                class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm {{ $activeFolder === $folder->slug ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                                wire:click="$set('activeFolder', '{{ $folder->slug }}')"
                            >
                                <span>{{ $folder->name }}</span>
                                <span class="text-xs text-slate-400">{{ $folder->color ?? 'custom' }}</span>
                            </button>
                        @empty
                            <p class="text-xs text-slate-500">No custom folders yet.</p>
                        @endforelse
                    </div>
                    <form wire:submit.prevent="createFolder" class="mt-4 flex items-center gap-2">
                        <input
                            wire:model.defer="newFolderName"
                            class="min-h-[40px] w-full rounded-lg border-slate-200 text-xs"
                            placeholder="New folder name"
                        />
                        <button class="min-h-[40px] rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white">Add</button>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 mail-shadow">
                    <h3 class="text-sm font-semibold text-slate-900">Quick Filters</h3>
                    <div class="mt-3 space-y-2 text-xs text-slate-500">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-slate-300" wire:model="filters.unread" />
                            Unread only
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-slate-300" wire:model="filters.has_attachments" />
                            Has attachments
                        </label>
                    </div>
                </div>
            </aside>

            <section data-pane="list" class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 mail-shadow">
                    <label class="text-xs text-slate-500">Search</label>
                    <input
                        wire:model.debounce.300ms="threadSearch"
                        class="mt-1 w-full rounded-lg border-slate-200 text-sm"
                        placeholder="Search subject, customer, or work order"
                    />
                    <details class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                        <summary class="cursor-pointer font-semibold text-slate-700">Advanced filters</summary>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="text-[11px] uppercase text-slate-500">Sender</label>
                                <input wire:model.debounce.300ms="filters.sender" class="mt-1 w-full rounded-lg border-slate-200 text-xs" placeholder="Name or email" />
                            </div>
                            <div>
                                <label class="text-[11px] uppercase text-slate-500">Work Order</label>
                                <select wire:model="filters.work_order_id" class="mt-1 w-full rounded-lg border-slate-200 text-xs">
                                    <option value="">Any</option>
                                    @foreach ($workOrders as $order)
                                        <option value="{{ $order->id }}">#{{ $order->id }} {{ $order->subject }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] uppercase text-slate-500">Date start</label>
                                <input wire:model="filters.date_start" type="date" class="mt-1 w-full rounded-lg border-slate-200 text-xs" />
                            </div>
                            <div>
                                <label class="text-[11px] uppercase text-slate-500">Date end</label>
                                <input wire:model="filters.date_end" type="date" class="mt-1 w-full rounded-lg border-slate-200 text-xs" />
                            </div>
                            <div>
                                <label class="text-[11px] uppercase text-slate-500">Message type</label>
                                <select wire:model="filters.message_type" class="mt-1 w-full rounded-lg border-slate-200 text-xs">
                                    <option value="">Any</option>
                                    <option value="direct">Direct</option>
                                    <option value="group">Group</option>
                                    <option value="system">System</option>
                                    <option value="work_order">Work order</option>
                                    <option value="broadcast">Broadcast</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] uppercase text-slate-500">Channel</label>
                                <select wire:model="filters.channel" class="mt-1 w-full rounded-lg border-slate-200 text-xs">
                                    <option value="">Any</option>
                                    <option value="in_app">In-app</option>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="push">Push</option>
                                </select>
                            </div>
                        </div>
                    </details>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 mail-shadow">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-2 text-xs text-slate-500">
                                <input type="checkbox" class="rounded border-slate-300" wire:click="toggleSelectAll" {{ $selectAll ? 'checked' : '' }} />
                                Select all
                            </label>
                            @if (! empty($selectedThreads))
                                <span class="text-xs text-slate-500">{{ count($selectedThreads) }} selected</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button class="min-h-[36px] rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600" wire:click="markSelectedRead">Mark read</button>
                            <button class="min-h-[36px] rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600" wire:click="markSelectedUnread">Mark unread</button>
                            <button class="min-h-[36px] rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600" wire:click="archiveSelected">Archive</button>
                            <button class="min-h-[36px] rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600" wire:click="deleteSelected">Delete</button>
                            <button class="min-h-[36px] rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600" wire:click="forwardSelected">Forward</button>
                            <div class="flex items-center gap-2">
                                <input
                                    wire:model.defer="bulkMoveFolder"
                                    class="min-h-[36px] rounded-lg border-slate-200 text-xs"
                                    placeholder="Move to folder"
                                />
                                <button class="min-h-[36px] rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white" wire:click="moveSelected">Move</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white mail-shadow overflow-hidden" wire:poll.15s>
                    <div class="divide-y divide-slate-200">
                        @php
                            $threadGroups = $activeFolder === 'work_orders'
                                ? $threads->getCollection()->groupBy('work_order_id')
                                : collect(['all' => $threads->getCollection()]);
                        @endphp
                        @forelse ($threadGroups as $workOrderId => $groupedThreads)
                            @if ($activeFolder === 'work_orders')
                                <div class="bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-500">
                                    Work Order #{{ $workOrderId ?? 'Unassigned' }}
                                </div>
                            @endif
                            @foreach ($groupedThreads as $thread)
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
                                    $hasAttachments = $lastMessage && ($lastMessage->messageAttachments->isNotEmpty() || $lastMessage->attachments->isNotEmpty());
                                    $senderName = $lastMessage?->sender?->name ?? $lastMessage?->user?->name ?? 'System';
                                @endphp
                                <div
                                    class="group flex gap-3 px-4 py-3 transition {{ $activeThread && $activeThread->id === $thread->id ? 'bg-slate-900 text-white' : 'hover:bg-slate-50' }}"
                                    wire:click="selectThread({{ $thread->id }})"
                                >
                                    <div class="pt-1">
                                        <input
                                            type="checkbox"
                                            class="rounded border-slate-300"
                                            wire:click.stop="toggleThreadSelection({{ $thread->id }})"
                                            {{ in_array($thread->id, $selectedThreads, true) ? 'checked' : '' }}
                                        />
                                    </div>
                                    <button
                                        class="mt-1 h-5 w-5 rounded-full border border-slate-300 flex items-center justify-center text-xs {{ $selfParticipant?->is_starred ? 'bg-amber-400 text-white border-amber-400' : 'text-slate-400' }}"
                                        wire:click.stop="toggleStar({{ $thread->id }})"
                                    >
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.95 4.154.017c.97.004 1.371 1.24.588 1.81l-3.36 2.443 1.25 3.962c.29.92-.755 1.688-1.54 1.116L10 13.347 6.67 16.225c-.784.572-1.83-.196-1.54-1.116l1.25-3.962-3.36-2.443c-.783-.57-.382-1.806.588-1.81l4.154-.017 1.286-3.95z" />
                                        </svg>
                                    </button>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-semibold {{ $isUnread ? 'text-slate-900' : 'text-slate-700' }} {{ $activeThread && $activeThread->id === $thread->id ? 'text-white' : '' }}">
                                                {{ $thread->subject ?? 'Conversation' }}
                                            </p>
                                            <span class="text-xs {{ $activeThread && $activeThread->id === $thread->id ? 'text-white/70' : 'text-slate-400' }}">
                                                {{ $thread->updated_at?->format('M d, H:i') }}
                                            </span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs {{ $activeThread && $activeThread->id === $thread->id ? 'text-white/70' : 'text-slate-500' }}">
                                            <span>{{ $senderName }}</span>
                                            <span>|</span>
                                            <span>{{ $participantNames->isNotEmpty() ? $participantNames->implode(', ') : 'No other participants' }}</span>
                                            @if ($thread->work_order_id)
                                                <span>| WO #{{ $thread->work_order_id }}</span>
                                            @endif
                                            <span>| {{ $thread->messages_count }} msg</span>
                                            @if ($hasAttachments)
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.414a4 4 0 10-5.656-5.656L6 10.1" />
                                                    </svg>
                                                    Attachment
                                                </span>
                                            @endif
                                            @if ($isUnread)
                                                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                            @endif
                                        </div>
                                        @if ($lastMessage)
                                            <p class="mt-2 text-xs {{ $activeThread && $activeThread->id === $thread->id ? 'text-white/70' : 'text-slate-500' }}">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($lastMessage->body), 110) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @empty
                            <div class="p-4 text-sm text-slate-500">No messages found.</div>
                        @endforelse
                    </div>
                    <div class="border-t border-slate-200 px-4 py-3">
                        {{ $threads->links() }}
                    </div>
                </div>
            </section>

            <section data-pane="message" class="hidden lg:block space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 mail-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Conversation</h2>
                            <p class="text-xs text-slate-500">Thread details and conversation history.</p>
                        </div>
                        @if ($activeThread)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">Thread #{{ $activeThread->id }}</span>
                        @endif
                    </div>

                    @if ($activeThread)
                        <div class="mt-4 space-y-2">
                            <p class="text-sm font-semibold text-slate-900">{{ $activeThread->subject ?? 'Conversation' }}</p>
                            <p class="text-xs text-slate-500">
                                Participants:
                                {{ $activeThread->participants->pluck('user.name')->filter()->implode(', ') }}
                            </p>
                            @if ($activeThread->workOrder)
                                <a class="text-xs text-emerald-600" href="{{ route('work-orders.show', $activeThread->workOrder) }}" wire:navigate>
                                    View Work Order #{{ $activeThread->workOrder->id }}
                                </a>
                            @endif
                        </div>

                        @php
                            $messageCount = $activeMessages->count();
                            $collapsedMessages = $messageCount > 6 ? $activeMessages->slice(0, $messageCount - 6) : collect();
                            $visibleMessages = $messageCount > 6 ? $activeMessages->slice(-6) : $activeMessages;
                        @endphp

                        <div class="mt-4 space-y-3">
                            @if ($collapsedMessages->isNotEmpty())
                                <details class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                                    <summary class="cursor-pointer font-semibold text-slate-700">Show {{ $collapsedMessages->count() }} older messages</summary>
                                    <div class="mt-3 space-y-2">
                                        @foreach ($collapsedMessages as $message)
                                            <div class="rounded-lg bg-white p-3">
                                                <p class="text-[11px] text-slate-400">{{ $message->sender?->name ?? $message->user?->name ?? 'System' }} - {{ $message->created_at?->format('M d, H:i') }}</p>
                                                <div class="text-sm text-slate-700">{!! $message->body !!}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif

                            <div class="space-y-4">
                                @forelse ($visibleMessages as $message)
                                    @php
                                        $isOwn = $user && $message->user_id === $user->id;
                                        $attachmentItems = $message->messageAttachments->isNotEmpty()
                                            ? $message->messageAttachments
                                            : $message->attachments;
                                    @endphp
                                    <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                                        <div class="max-w-xl rounded-2xl px-4 py-3 {{ $isOwn ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-800' }}">
                                            <div class="flex items-center justify-between gap-4 text-[11px] {{ $isOwn ? 'text-white/70' : 'text-slate-400' }}">
                                                <span>{{ $message->sender?->name ?? $message->user?->name ?? 'System' }}</span>
                                                <span>{{ $message->created_at?->format('M d, H:i') }}</span>
                                            </div>
                                            <div class="mt-2 text-sm leading-relaxed">{!! $message->body !!}</div>
                                            @if ($attachmentItems->isNotEmpty())
                                                <div class="mt-3 space-y-2">
                                                @foreach ($attachmentItems as $attachment)
                                                    <div class="flex items-center justify-between rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs">
                                                        <span>{{ $attachment->file_name ?? $attachment->label ?? 'Attachment' }}</span>
                                                        <span>{{ $attachment->file_type ?? $attachment->mime_type ?? 'file' }}</span>
                                                    </div>
                                                @endforeach
                                                </div>
                                            @endif
                                            <div class="mt-2 flex items-center gap-2 text-[10px] {{ $isOwn ? 'text-white/60' : 'text-slate-400' }}">
                                                <span class="rounded-full bg-white/10 px-2 py-0.5">{{ $message->channel ?? 'in_app' }}</span>
                                                <span class="rounded-full bg-white/10 px-2 py-0.5">{{ $message->message_type ?? 'direct' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">No messages yet.</p>
                                @endforelse
                            </div>
                        </div>

                        @if ($canSend)
                            <form wire:submit.prevent="sendReply" class="mt-6 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-slate-900">Reply</p>
                                    <button type="button" class="text-xs text-slate-500" data-quote-button>Quote last message</button>
                                </div>
                                <textarea
                                    wire:model="replyBody"
                                    class="w-full rounded-xl border-slate-200 text-sm"
                                    rows="3"
                                    placeholder="Type a reply"
                                    data-reply-input
                                ></textarea>
                                @error('replyBody') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                                <div class="flex items-center justify-end">
                                    <button class="min-h-[44px] rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white">
                                        Send Reply
                                    </button>
                                </div>
                            </form>
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                    Online now: 3
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                    <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                                    Typing indicator active
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                    Read receipts enabled
                                </span>
                            </div>
                        @endif
                    @else
                        <p class="mt-4 text-sm text-slate-500">Select a thread to view messages.</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 mail-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Compose Message</h2>
                            <p class="text-xs text-slate-500">Send direct, group, or broadcast updates.</p>
                        </div>
                        @if ($showComposer)
                            <button class="text-xs text-slate-500" type="button" wire:click="cancelComposer">Close</button>
                        @endif
                    </div>

                    @if ($showComposer && $canSend)
                        <form wire:submit.prevent="sendMessage" class="mt-4 space-y-4" onsubmit="return confirm('Send this message now?')">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-[11px] uppercase text-slate-500">To</label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            list="recipient-options"
                                            wire:model.defer="recipientSearch"
                                            class="w-full rounded-lg border-slate-200 text-sm"
                                            placeholder="Start typing a name or email"
                                        />
                                        <button type="button" class="min-h-[40px] rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white" wire:click="addRecipient('to')">Add</button>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($composer['recipient_ids'] as $recipientId)
                                            @php $recipient = $recipients->firstWhere('id', $recipientId); @endphp
                                            @if ($recipient)
                                                <button type="button" class="rounded-full bg-slate-900 px-3 py-1 text-xs text-white" wire:click="removeRecipient('recipient', {{ $recipient->id }})">
                                                    {{ $recipient->name }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                    @error('composer.recipient_ids') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] uppercase text-slate-500">CC / BCC</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <input
                                                list="recipient-options"
                                                wire:model.defer="ccSearch"
                                                class="w-full rounded-lg border-slate-200 text-sm"
                                                placeholder="Add CC"
                                            />
                                            <button type="button" class="min-h-[40px] rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600" wire:click="addRecipient('cc')">Add</button>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($composer['cc_ids'] as $recipientId)
                                                @php $recipient = $recipients->firstWhere('id', $recipientId); @endphp
                                                @if ($recipient)
                                                    <button type="button" class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700" wire:click="removeRecipient('cc', {{ $recipient->id }})">
                                                        {{ $recipient->name }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input
                                                list="recipient-options"
                                                wire:model.defer="bccSearch"
                                                class="w-full rounded-lg border-slate-200 text-sm"
                                                placeholder="Add BCC"
                                            />
                                            <button type="button" class="min-h-[40px] rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600" wire:click="addRecipient('bcc')">Add</button>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($composer['bcc_ids'] as $recipientId)
                                                @php $recipient = $recipients->firstWhere('id', $recipientId); @endphp
                                                @if ($recipient)
                                                    <button type="button" class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700" wire:click="removeRecipient('bcc', {{ $recipient->id }})">
                                                        {{ $recipient->name }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <datalist id="recipient-options">
                                @foreach ($recipients as $recipient)
                                    <option value="{{ $recipient->email }}">{{ $recipient->name }}</option>
                                @endforeach
                            </datalist>

                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="text-[11px] uppercase text-slate-500">Subject</label>
                                    <input wire:model="composer.subject" class="mt-1 w-full rounded-lg border-slate-200 text-sm" placeholder="Subject line" />
                                </div>
                                <div>
                                    <label class="text-[11px] uppercase text-slate-500">Link to Work Order</label>
                                    <select wire:model="composer.work_order_id" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
                                        <option value="">None</option>
                                        @foreach ($workOrders as $order)
                                            <option value="{{ $order->id }}">#{{ $order->id }} {{ $order->subject }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[11px] uppercase text-slate-500">Message Type</label>
                                    <select wire:model="composer.message_type" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
                                        <option value="direct">Direct</option>
                                        <option value="group">Group</option>
                                        <option value="system">System</option>
                                        <option value="work_order">Work order note</option>
                                        <option value="broadcast">Broadcast</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[11px] uppercase text-slate-500">Channel</label>
                                    <select wire:model="composer.channel" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
                                        <option value="in_app">In-app</option>
                                        <option value="email">Email</option>
                                        <option value="sms">SMS</option>
                                        <option value="push">Push</option>
                                    </select>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4" data-rich-editor>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1" data-editor-command="bold">Bold</button>
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1" data-editor-command="italic">Italic</button>
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1" data-editor-command="underline">Underline</button>
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1" data-editor-command="insertUnorderedList">Bullets</button>
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1" data-editor-command="insertOrderedList">Numbered</button>
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1" data-editor-command="createLink">Link</button>
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1" data-editor-command="foreColor" data-editor-value="#0284c7">Blue</button>
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-2 py-1" data-editor-command="hiliteColor" data-editor-value="#fde047">Highlight</button>
                                </div>
                                <div class="mt-3">
                                    <div class="mail-editor rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-700" contenteditable="true" data-editor-content wire:ignore></div>
                                    <textarea class="hidden" wire:model.defer="composer.message" data-editor-target></textarea>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                    <span>Rich text enabled</span>
                                    <span data-sms-count>0 chars - $0.00</span>
                                </div>
                            </div>
                            @error('composer.message') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror

                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="text-[11px] uppercase text-slate-500">Template</label>
                                    <div class="flex items-center gap-2">
                                        <select class="w-full rounded-lg border-slate-200 text-sm" wire:model="selectedTemplateId">
                                            <option value="">Choose template</option>
                                            @foreach ($templates as $template)
                                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="min-h-[40px] rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600" wire:click="saveTemplateFromComposer">Save</button>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">Merge fields auto-fill from work order and technician data.</p>
                                </div>
                                <div>
                                    <label class="text-[11px] uppercase text-slate-500">Attachments</label>
                                    <div class="relative rounded-xl border border-dashed border-slate-300 bg-white p-4 text-xs text-slate-500" data-dropzone>
                                        <input type="file" multiple class="absolute inset-0 opacity-0 cursor-pointer" wire:model="composerAttachments" />
                                        <p>Drag files here or tap to upload. Max 10 MB each.</p>
                                    </div>
                                    <div class="mt-2 space-y-2">
                                        @foreach ($composerAttachments as $attachment)
                                            @php
                                                $mimeType = $attachment->getMimeType();
                                                $isImage = $mimeType && str_starts_with($mimeType, 'image/');
                                            @endphp
                                            <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                                <div class="flex items-center gap-3">
                                                    @if ($isImage)
                                                        <img src="{{ $attachment->temporaryUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover" loading="lazy" />
                                                    @else
                                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-500">FILE</div>
                                                    @endif
                                                    <div>
                                                        <p class="font-medium text-slate-700">{{ $attachment->getClientOriginalName() }}</p>
                                                        <p class="text-[10px] text-slate-400">{{ $mimeType ?? 'unknown' }}</p>
                                                    </div>
                                                </div>
                                                <span>{{ number_format($attachment->getSize() / 1024, 1) }} KB</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2" wire:loading wire:target="composerAttachments">
                                        <div class="h-1 rounded-full bg-slate-200">
                                            <div class="h-1 w-1/2 rounded-full bg-emerald-400 animate-pulse"></div>
                                        </div>
                                        <p class="mt-1 text-[11px] text-slate-500">Uploading attachments...</p>
                                    </div>
                                    @error('composerAttachments.*') <p class="text-xs text-rose-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="checkbox" class="rounded border-slate-300" wire:model="composer.broadcast_all" />
                                    Broadcast to all users
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="checkbox" class="rounded border-slate-300" />
                                    Send copy to me
                                </label>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <button class="min-h-[44px] rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white">Send</button>
                                <button type="button" class="min-h-[44px] rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600" wire:click="saveDraft">Save draft</button>
                            </div>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-slate-500">Select "Compose Message" to start a new conversation.</p>
                    @endif
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 mail-shadow">
                        <h2 class="text-lg font-semibold text-slate-900">Analytics Snapshot</h2>
                        <p class="text-xs text-slate-500">Message volume, response time, and delivery rates.</p>
                        <div class="mt-4 space-y-3">
                            <div>
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>Message volume</span>
                                    <span>{{ $threads->total() }} total</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-200">
                                    <div class="h-2 w-3/4 rounded-full bg-sky-500"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>Avg response time</span>
                                    <span>18m</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-200">
                                    <div class="h-2 w-2/3 rounded-full bg-emerald-500"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>Email open rate</span>
                                    <span>62%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-200">
                                    <div class="h-2 w-2/3 rounded-full bg-amber-400"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>SMS delivery rate</span>
                                    <span>98%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-200">
                                    <div class="h-2 w-[95%] rounded-full bg-emerald-500"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 mail-shadow">
                        <h2 class="text-lg font-semibold text-slate-900">Notification Preferences</h2>
                        <p class="text-xs text-slate-500">Control delivery channels, quiet hours, and VIP senders.</p>
                        <form class="mt-4 space-y-4" wire:submit.prevent="saveNotificationPreferences">
                            @foreach ($notificationPreferences as $channel => $prefs)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-slate-900">{{ strtoupper($channel) }}</span>
                                        <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                            <input type="checkbox" class="rounded border-slate-300" wire:model="notificationPreferences.{{ $channel }}.is_enabled" />
                                            Enabled
                                        </label>
                                    </div>
                                <div class="mt-3 grid gap-2 text-xs text-slate-500">
                                    <div>
                                        <label class="text-[11px] uppercase text-slate-500">Frequency</label>
                                        <select class="mt-1 w-full rounded-lg border-slate-200 text-xs" wire:model="notificationPreferences.{{ $channel }}.frequency">
                                            <option value="immediate">Immediate</option>
                                            <option value="hourly">Hourly digest</option>
                                            <option value="daily">Daily digest</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[11px] uppercase text-slate-500">Message types</label>
                                        <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-slate-600">
                                            @foreach (['direct', 'group', 'system', 'work_order', 'broadcast', 'urgent'] as $type)
                                                <label class="inline-flex items-center gap-1">
                                                    <input type="checkbox" class="rounded border-slate-300" wire:model="notificationPreferences.{{ $channel }}.message_types" value="{{ $type }}" />
                                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[11px] uppercase text-slate-500">Quiet start</label>
                                            <input type="time" class="mt-1 w-full rounded-lg border-slate-200 text-xs" wire:model="notificationPreferences.{{ $channel }}.quiet_hours_start" />
                                        </div>
                                        <div>
                                            <label class="text-[11px] uppercase text-slate-500">Quiet end</label>
                                            <input type="time" class="mt-1 w-full rounded-lg border-slate-200 text-xs" wire:model="notificationPreferences.{{ $channel }}.quiet_hours_end" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[11px] uppercase text-slate-500">VIP senders</label>
                                        <input
                                            type="text"
                                            class="mt-1 w-full rounded-lg border-slate-200 text-xs"
                                            wire:model="notificationPreferences.{{ $channel }}.vip_senders"
                                            placeholder="name@company.com, dispatch@example.com"
                                        />
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <button class="min-h-[40px] rounded-lg bg-slate-900 px-4 text-xs font-semibold text-white">Save preferences</button>
                        </form>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 mail-shadow">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Automated Messaging</h2>
                            <p class="text-xs text-slate-500">Trigger-based messages for work orders, billing, and surveys.</p>
                        </div>
                        <span class="text-xs text-slate-500">{{ $automations->count() }} automations</span>
                    </div>
                    <div class="mt-4 space-y-2">
                        @forelse ($automations as $automation)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $automation->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $automation->trigger }}</p>
                                </div>
                                <button
                                    class="min-h-[32px] rounded-full px-3 text-xs font-semibold {{ $automation->is_enabled ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}"
                                    wire:click="toggleAutomation({{ $automation->id }})"
                                >
                                    {{ $automation->is_enabled ? 'Enabled' : 'Disabled' }}
                                </button>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">No automation rules configured yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 mail-shadow">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Integrations & Real-time</h2>
                            <p class="text-xs text-slate-500">Email, SMS, push, and WebSocket connection health.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Live</span>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
                            <p class="text-slate-500">SMTP</p>
                            <p class="text-sm font-semibold text-slate-900">Connected</p>
                            <p class="text-[11px] text-slate-500">Branded templates active</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
                            <p class="text-slate-500">SMS Gateway</p>
                            <p class="text-sm font-semibold text-slate-900">Twilio Ready</p>
                            <p class="text-[11px] text-slate-500">Opt-in verified</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
                            <p class="text-slate-500">Push</p>
                            <p class="text-sm font-semibold text-slate-900">Enabled</p>
                            <p class="text-[11px] text-slate-500">Desktop + mobile</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
                            <p class="text-slate-500">WebSocket</p>
                            <p class="text-sm font-semibold text-slate-900">Reconnected</p>
                            <p class="text-[11px] text-slate-500">Polling fallback active</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        (() => {
            const smsSegmentLength = {{ config('messaging.sms.segment_length') }};
            const smsSegmentCost = {{ config('messaging.sms.segment_cost') }};
            let paneInitialized = false;

            const updateSmsCount = (text) => {
                const smsCount = document.querySelector('[data-sms-count]');
                if (! smsCount) {
                    return;
                }
                const length = text.length;
                const segments = length === 0 ? 0 : Math.ceil(length / smsSegmentLength);
                const cost = (segments * smsSegmentCost).toFixed(2);
                smsCount.textContent = `${length} chars - $${cost}`;
            };

            const showPane = (name) => {
                document.querySelectorAll('[data-pane]').forEach((pane) => {
                    if (window.innerWidth >= 1024) {
                        pane.classList.remove('hidden');
                        return;
                    }
                    pane.classList.toggle('hidden', pane.dataset.pane !== name);
                });
                document.querySelectorAll('[data-pane-toggle]').forEach((toggle) => {
                    toggle.classList.toggle('text-slate-900', toggle.dataset.paneToggle === name);
                });
            };

            const initPaneToggles = () => {
                document.querySelectorAll('[data-pane-toggle]').forEach((toggle) => {
                    if (toggle.dataset.bound) {
                        return;
                    }
                    toggle.dataset.bound = 'true';
                    toggle.addEventListener('click', () => showPane(toggle.dataset.paneToggle));
                });
                if (! paneInitialized) {
                    showPane('list');
                    paneInitialized = true;
                }
            };

            const initEditors = () => {
                document.querySelectorAll('[data-rich-editor]').forEach((wrapper) => {
                    if (wrapper.dataset.initialized) {
                        return;
                    }
                    const editor = wrapper.querySelector('[data-editor-content]');
                    const target = wrapper.querySelector('[data-editor-target]');
                    if (! editor || ! target) {
                        return;
                    }
                    wrapper.dataset.initialized = 'true';

                    if (target.value && editor.innerHTML.trim() === '') {
                        editor.innerHTML = target.value;
                    }

                    const sync = () => {
                        target.value = editor.innerHTML;
                        target.dispatchEvent(new Event('input', { bubbles: true }));
                        updateSmsCount(editor.textContent || '');
                    };

                    wrapper.querySelectorAll('[data-editor-command]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const command = button.dataset.editorCommand;
                            const value = button.dataset.editorValue || null;
                            if (command === 'createLink') {
                                const url = window.prompt('Link URL');
                                if (! url) {
                                    return;
                                }
                                document.execCommand(command, false, url);
                            } else {
                                document.execCommand(command, false, value);
                            }
                            sync();
                        });
                    });

                    editor.addEventListener('input', sync);
                    updateSmsCount(editor.textContent || '');

                    setInterval(() => {
                        if (document.activeElement === editor) {
                            return;
                        }
                        if (target.value !== editor.innerHTML) {
                            editor.innerHTML = target.value;
                        }
                    }, 500);
                });
            };

            const initDropzones = () => {
                document.querySelectorAll('[data-dropzone]').forEach((zone) => {
                    if (zone.dataset.bound) {
                        return;
                    }
                    const input = zone.querySelector('input[type="file"]');
                    if (! input) {
                        return;
                    }
                    zone.dataset.bound = 'true';

                    zone.addEventListener('dragover', (event) => {
                        event.preventDefault();
                        zone.classList.add('border-slate-500');
                    });
                    zone.addEventListener('dragleave', () => zone.classList.remove('border-slate-500'));
                    zone.addEventListener('drop', (event) => {
                        event.preventDefault();
                        zone.classList.remove('border-slate-500');
                        if (! event.dataTransfer?.files?.length) {
                            return;
                        }
                        input.files = event.dataTransfer.files;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                });
            };

            const initQuote = () => {
                const quoteButton = document.querySelector('[data-quote-button]');
                if (! quoteButton || quoteButton.dataset.bound) {
                    return;
                }
                quoteButton.dataset.bound = 'true';
                quoteButton.addEventListener('click', () => {
                    const messages = document.querySelectorAll('[data-message-id]');
                    const latestMessage = messages.length ? messages[messages.length - 1] : null;
                    const replyInput = document.querySelector('[data-reply-input]');
                    if (! latestMessage || ! replyInput) {
                        return;
                    }
                    const text = latestMessage.textContent?.trim() || '';
                    replyInput.value = `> ${text}\n\n` + replyInput.value;
                    replyInput.dispatchEvent(new Event('input', { bubbles: true }));
                });
            };

            const initAll = () => {
                initPaneToggles();
                initEditors();
                initDropzones();
                initQuote();
            };

            initAll();
            window.addEventListener('resize', () => showPane('list'));
            setInterval(initAll, 1000);
        })();
    </script>
</div>
