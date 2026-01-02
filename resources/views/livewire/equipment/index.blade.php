<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Equipment</h1>
                <p class="text-sm text-gray-500">Manage devices, warranties, and maintenance history.</p>
            </div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="$toggle('showCreate')">Add Equipment</button>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($showCreate)
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">New Equipment</h2>
                <form wire:submit.prevent="createEquipment" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if (! $user->hasRole('client'))
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
                        <label class="text-xs text-gray-500">Category</label>
                        <select wire:model="new.equipment_category_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Name</label>
                        <input wire:model="new.name" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('new.name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Type</label>
                        <input wire:model="new.type" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('new.type') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Manufacturer</label>
                        <input wire:model="new.manufacturer" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Model</label>
                        <input wire:model="new.model" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Serial Number</label>
                        <input wire:model="new.serial_number" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Status</label>
                        <select wire:model="new.status" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="operational">Operational</option>
                            <option value="needs_attention">Needs Attention</option>
                            <option value="in_repair">In Repair</option>
                            <option value="retired">Retired</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Location Name</label>
                        <input wire:model="new.location_name" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Location Address</label>
                        <input wire:model="new.location_address" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-2 flex items-center gap-3">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="resetNew">Reset</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($equipment as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->type }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->organization?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->location_name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">
                {{ $equipment->links() }}
            </div>
        </div>
    </div>
</div>
