<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Support Tickets</h1>
                <p class="text-sm text-gray-500">Escalations, complaints, and quality follow-ups.</p>
            </div>
            @if ($canCreate)
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="$toggle('showCreate')">New Ticket</button>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 mb-6 relative overflow-hidden">
            <div wire:loading wire:target="search, statusFilter, priorityFilter, organizationFilter" class="absolute inset-0 bg-white/50 z-10 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Search</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="mt-1 w-full rounded-md border-gray-300"
                        placeholder="Subject or description"
                    />
                </div>
                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <select wire:model.live="statusFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="all">All statuses</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Priority</label>
                    <select wire:model.live="priorityFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="all">All priorities</option>
                        <option value="standard">Standard</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                @if ($canManage)
                    <div class="md:col-span-4">
                        <label class="text-xs text-gray-500">Organization</label>
                        <select wire:model.live="organizationFilter" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">All organizations</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <button type="button" class="text-indigo-600" wire:click="clearFilters">Clear filters</button>
                <span>Found {{ $tickets->total() }} tickets</span>
            </div>
        </div>

        @if ($showCreate && $canCreate)
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Ticket</h2>
                <form wire:submit.prevent="createTicket" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if (! $user->isCustomer())
                        <div>
                            <label class="text-xs text-gray-500">Organization</label>
                            <select wire:model="new.organization_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Select organization</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="text-xs text-gray-500">Work Order</label>
                        <select wire:model="new.work_order_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">None</option>
                            @foreach ($workOrders as $order)
                                <option value="{{ $order->id }}">#{{ $order->id }} {{ $order->subject }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Priority</label>
                        <select wire:model="new.priority" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="standard">Standard</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    @if ($canAssign)
                        <div>
                            <label class="text-xs text-gray-500">Assign Support</label>
                            <select wire:model="new.assigned_to_user_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Unassigned</option>
                                @foreach ($supportManagers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Subject</label>
                        <input wire:model="new.subject" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Description</label>
                        <textarea wire:model="new.description" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                    </div>
                    @if ($suggestedArticles->isNotEmpty())
                        <div class="md:col-span-2 bg-gray-50 border border-gray-100 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-900">Before you submit, check these articles</h3>
                            <p class="text-xs text-gray-500">Suggested based on your subject and description.</p>
                            <div class="mt-3 space-y-2 text-sm">
                                @foreach ($suggestedArticles as $article)
                                    <div class="flex items-center justify-between">
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" wire:model="suggestedArticleIds" value="{{ $article->id }}" class="rounded border-gray-300" />
                                            <span>{{ $article->title }}</span>
                                        </label>
                                        <a class="text-xs text-indigo-600" href="{{ route('knowledge-base.show', $article) }}" target="_blank" rel="noreferrer">Open</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="md:col-span-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Knowledge Base</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $ticket->subject }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $ticket->organization?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($ticket->priority) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if ($canManage)
                                    <select class="rounded-md border-gray-300 text-sm" wire:change="updateStatus({{ $ticket->id }}, $event.target.value)">
                                        @foreach ($statusOptions as $status)
                                            <option value="{{ $status }}" @selected($ticket->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if ($ticket->knowledgeArticles->isNotEmpty())
                                    <div class="space-y-1 text-xs">
                                        @foreach ($ticket->knowledgeArticles as $article)
                                            <a class="text-indigo-600" href="{{ route('knowledge-base.show', $article) }}" target="_blank" rel="noreferrer">
                                                {{ Str::limit($article->title, 32) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $tickets->links() }}</div>
        </div>
    </div>
</div>
