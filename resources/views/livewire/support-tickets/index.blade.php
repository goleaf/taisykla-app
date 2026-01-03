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
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $tickets->links() }}</div>
        </div>
    </div>
</div>
