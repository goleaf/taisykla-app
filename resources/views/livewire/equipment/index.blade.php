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

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 relative overflow-hidden">
            <div wire:loading wire:target="search, statusFilter, categoryFilter, typeFilter, locationFilter, organizationFilter, sortField, sortDirection" class="absolute inset-0 bg-white/50 z-10 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
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
                <div>
                    <label class="text-xs text-gray-500">Type</label>
                    <select wire:model="typeFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Location</label>
                    <select wire:model="locationFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All locations</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}">{{ $location }}</option>
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
                <div>
                    <label class="text-xs text-gray-500">Sort By</label>
                    <select wire:model="sortField" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="last_service_at">Last serviced</option>
                        <option value="name">Name</option>
                        <option value="type">Type</option>
                        <option value="location_name">Location</option>
                        <option value="status">Status</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Direction</label>
                    <select wire:model="sortDirection" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="desc">Newest</option>
                        <option value="asc">Oldest</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <button type="button" class="text-indigo-600" wire:click="clearFilters">Clear filters</button>
                <span>{{ $equipment->total() }} items</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="{{ $canManage ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 relative overflow-hidden">
                    <div wire:loading wire:target="search, statusFilter, categoryFilter, typeFilter, locationFilter, organizationFilter, sortField, sortDirection, gotoPage, nextPage, previousPage" class="absolute inset-0 bg-white/40 z-10 backdrop-blur-[1px] flex items-center justify-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-10 h-10 text-indigo-600 animate-spin mb-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium text-indigo-600">Updating list...</span>
                        </div>
                    </div>
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
                                            <span>Last serviced: {{ $item->last_service_at?->format('M d, Y') ?? '—' }}</span>
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
                                            <a class="px-3 py-1 text-xs border border-gray-300 rounded-md" href="{{ route('equipment.show', $item) }}" wire:navigate>View</a>
                                            <button class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="editEquipment({{ $item->id }})">Edit</button>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <a class="px-3 py-1 text-xs border border-gray-300 rounded-md" href="{{ route('equipment.show', $item) }}" wire:navigate>View</a>
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
                                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveEquipment">
                                            {{ $editingId ? 'Update Equipment' : 'Save Equipment' }}
                                        </span>
                                        <span wire:loading wire:target="saveEquipment">
                                            Processing...
                                        </span>
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
