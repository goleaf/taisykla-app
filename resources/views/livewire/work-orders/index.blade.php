<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Work Orders</h1>
                <p class="text-sm text-gray-500">Track service requests across the lifecycle.</p>
            </div>
            <button
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md"
                wire:click="$toggle('showCreate')"
            >
                New Work Order
            </button>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <select wire:model="statusFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Search</label>
                    <input
                        type="text"
                        wire:model.debounce.300ms="search"
                        class="mt-1 w-full rounded-md border-gray-300"
                        placeholder="Search by subject or description"
                    />
                </div>
            </div>
        </div>

        @if ($showCreate)
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Work Order</h2>
                <form wire:submit.prevent="createWorkOrder" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if (! $user->hasRole('client'))
                        <div>
                            <label class="text-xs text-gray-500">Organization</label>
                            <select wire:model="new.organization_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Select organization</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                @endforeach
                            </select>
                            @error('new.organization_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="text-xs text-gray-500">Equipment</label>
                        <select wire:model="new.equipment_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select equipment</option>
                            @foreach ($equipment as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('new.equipment_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Category</label>
                        <select wire:model="new.category_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('new.category_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Priority</label>
                        <select wire:model="new.priority" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($priorityOptions as $priority)
                                <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                        @error('new.priority') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Subject</label>
                        <input type="text" wire:model="new.subject" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('new.subject') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Description</label>
                        <textarea wire:model="new.description" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                        @error('new.description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Scheduled Start</label>
                        <input type="datetime-local" wire:model="new.scheduled_start_at" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('new.scheduled_start_at') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Scheduled End</label>
                        <input type="datetime-local" wire:model="new.scheduled_end_at" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('new.scheduled_end_at') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Time Window</label>
                        <input type="text" wire:model="new.time_window" class="mt-1 w-full rounded-md border-gray-300" placeholder="Morning, Afternoon" />
                        @error('new.time_window') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @if ($user->hasAnyRole(['admin', 'dispatch']))
                        <div class="md:col-span-2">
                            <label class="text-xs text-gray-500">Assign Technician</label>
                            <select wire:model="new.assigned_to_user_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Unassigned</option>
                                @foreach ($technicians as $technician)
                                    <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                @endforeach
                            </select>
                            @error('new.assigned_to_user_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div class="md:col-span-2 flex items-center gap-3">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create</button>
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="resetNew">Reset</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($workOrders as $workOrder)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">#{{ $workOrder->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $workOrder->subject }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $workOrder->organization?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if ($user->hasAnyRole(['admin', 'dispatch', 'technician']))
                                    <select class="rounded-md border-gray-300 text-sm" wire:change="updateStatus({{ $workOrder->id }}, $event.target.value)">
                                        @foreach ($statusOptions as $status)
                                            <option value="{{ $status }}" @selected($workOrder->status === $status)>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($workOrder->priority) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if ($user->hasAnyRole(['admin', 'dispatch']))
                                    <select class="rounded-md border-gray-300 text-sm" wire:change="assignTo({{ $workOrder->id }}, $event.target.value)">
                                        <option value="">Unassigned</option>
                                        @foreach ($technicians as $technician)
                                            <option value="{{ $technician->id }}" @selected($workOrder->assigned_to_user_id === $technician->id)>
                                                {{ $technician->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    {{ $workOrder->assignedTo?->name ?? 'Unassigned' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $workOrder->scheduled_start_at?->format('M d, H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-indigo-600">
                                <a href="{{ route('work-orders.show', $workOrder) }}" wire:navigate>View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">
                {{ $workOrders->links() }}
            </div>
        </div>
    </div>
</div>
