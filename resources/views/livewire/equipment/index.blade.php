<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Equipment</h1>
                <p class="text-sm text-gray-500">Manage devices, warranties, and maintenance history.</p>
            </div>
            @if ($canManage)
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="startCreate">Add Equipment</button>
            @endif
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Total</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['total'] ?? 0 }}</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Operational</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['operational'] ?? 0 }}</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Needs Attention</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['needs_attention'] ?? 0 }}</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">In Repair</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['in_repair'] ?? 0 }}</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Retired</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $summary['retired'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label class="text-xs text-gray-500">Search</label>
                    <input wire:model.debounce.300ms="search" class="mt-1 w-full rounded-md border-gray-300" placeholder="Name, model, serial, location" />
                </div>
                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <select wire:model="statusFilter" class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Category</label>
                    <select wire:model="categoryFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if (! $isClient)
                    <div>
                        <label class="text-xs text-gray-500">Organization</label>
                        <select wire:model="organizationFilter" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">All organizations</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <button type="button" class="text-indigo-600" wire:click="clearFilters">Clear filters</button>
                <span>{{ $equipment->total() }} items</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="{{ $canManage ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                    <div class="divide-y divide-gray-200">
                        @forelse ($equipment as $item)
                            <div class="p-5" wire:key="equipment-{{ $item->id }}">
                                @php
                                    $statusLabel = ucfirst(str_replace('_', ' ', $item->status));
                                @endphp
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-semibold text-gray-900">{{ $item->name }}</h3>
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs',
                                                'bg-green-100 text-green-700' => $item->status === 'operational',
                                                'bg-yellow-100 text-yellow-700' => $item->status === 'needs_attention',
                                                'bg-orange-100 text-orange-700' => $item->status === 'in_repair',
                                                'bg-gray-100 text-gray-700' => $item->status === 'retired',
                                            ])>
                                                {{ $statusLabel }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <span>Type: {{ $item->type }}</span>
                                            <span>Category: {{ $item->category?->name ?? '—' }}</span>
                                            @if (! $isClient)
                                                <span>Org: {{ $item->organization?->name ?? '—' }}</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <span>Manufacturer: {{ $item->manufacturer ?? '—' }}</span>
                                            <span>Model: {{ $item->model ?? '—' }}</span>
                                            <span>Serial: {{ $item->serial_number ?? '—' }}</span>
                                            <span>Asset: {{ $item->asset_tag ?? '—' }}</span>
                                            @if ($item->purchase_date)
                                                <span>Purchased: {{ $item->purchase_date->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Location: {{ $item->location_name ?? '—' }}
                                            @if ($item->location_address)
                                                · {{ \Illuminate\Support\Str::limit($item->location_address, 60) }}
                                            @endif
                                        </div>
                                        @if ($item->notes)
                                            <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($item->notes, 140) }}</p>
                                        @endif
                                    </div>
                                    @if ($canManage)
                                        <div class="flex items-center gap-2">
                                            <button class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="editEquipment({{ $item->id }})">Edit</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">No equipment matches the current filters.</div>
                        @endforelse
                    </div>
                    <div class="p-4">
                        {{ $equipment->links() }}
                    </div>
                </div>
            </div>

            @if ($canManage)
                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ $editingId ? 'Edit Equipment' : 'New Equipment' }}
                            </h2>
                            @if ($showForm)
                                <button class="text-sm text-gray-500" type="button" wire:click="cancelForm">Close</button>
                            @endif
                        </div>

                        @if ($showForm)
                            <form wire:submit.prevent="saveEquipment" class="space-y-3">
                                @if (! $isClient)
                                    <div>
                                        <label class="text-xs text-gray-500">Organization</label>
                                        <select wire:model="form.organization_id" class="mt-1 w-full rounded-md border-gray-300">
                                            <option value="">Select organization</option>
                                            @foreach ($organizations as $organization)
                                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.organization_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div>
                                    <label class="text-xs text-gray-500">Category</label>
                                    <select wire:model="form.equipment_category_id" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.equipment_category_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Status</label>
                                    <select wire:model="form.status" class="mt-1 w-full rounded-md border-gray-300">
                                        @foreach ($statusOptions as $value => $label)
                                            @if ($value !== 'all')
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('form.status') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Name</label>
                                    <input wire:model="form.name" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('form.name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Type</label>
                                    <input wire:model="form.type" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('form.type') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Manufacturer</label>
                                        <input wire:model="form.manufacturer" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.manufacturer') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Model</label>
                                        <input wire:model="form.model" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.model') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Serial Number</label>
                                        <input wire:model="form.serial_number" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.serial_number') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Asset Tag</label>
                                        <input wire:model="form.asset_tag" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.asset_tag') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Purchase Date</label>
                                    <input type="date" wire:model="form.purchase_date" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('form.purchase_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Location Name</label>
                                        <input wire:model="form.location_name" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.location_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Location Address</label>
                                        <input wire:model="form.location_address" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.location_address') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Notes</label>
                                    <textarea wire:model="form.notes" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                                    @error('form.notes') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex items-center gap-2">
                                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                                        {{ $editingId ? 'Update Equipment' : 'Save Equipment' }}
                                    </button>
                                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="cancelForm">Cancel</button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm text-gray-500">Select "Add Equipment" to register a new device.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
