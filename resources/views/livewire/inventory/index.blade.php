<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Parts & Inventory</h1>
                <p class="text-sm text-gray-500">Track parts, stock levels, and locations.</p>
            </div>
            @if ($canManage)
                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="$toggle('showNewPart')">Add Part</button>
                    <button class="px-4 py-2 border border-gray-300 rounded-md" wire:click="$toggle('showStock')">Add Stock</button>
                </div>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($showNewPart && $canManage)
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">New Part</h2>
                <form wire:submit.prevent="createPart" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500">Name</label>
                        <input wire:model="newPart.name" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('newPart.name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">SKU</label>
                        <input wire:model="newPart.sku" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Unit Cost</label>
                        <input type="number" step="0.01" wire:model="newPart.unit_cost" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Unit Price</label>
                        <input type="number" step="0.01" wire:model="newPart.unit_price" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Vendor</label>
                        <input wire:model="newPart.vendor" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Reorder Level</label>
                        <input type="number" wire:model="newPart.reorder_level" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Description</label>
                        <textarea wire:model="newPart.description" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-3">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="resetNewPart">Reset</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($showStock && $canManage)
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Stock</h2>
                <form wire:submit.prevent="addStock" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs text-gray-500">Part</label>
                        <select wire:model="newStock.part_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select part</option>
                            @foreach ($parts as $part)
                                <option value="{{ $part->id }}">{{ $part->name }}</option>
                            @endforeach
                        </select>
                        @error('newStock.part_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Location</label>
                        <select wire:model="newStock.location_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select location</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                        @error('newStock.location_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Quantity</label>
                        <input type="number" wire:model="newStock.quantity" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('newStock.quantity') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3 flex items-center gap-3">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Update Stock</button>
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="resetNewStock">Reset</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 mb-6">
            <div class="flex items-center justify-between p-4">
                <h2 class="text-lg font-semibold text-gray-900">Low Stock Alerts</h2>
                <span class="text-xs text-gray-500">Below reorder level</span>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Part</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">On Hand</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($lowStockParts as $part)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $part->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $part->on_hand }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $part->reorder_level }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-gray-500">No low-stock alerts.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 p-4">Parts</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($parts as $part)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $part->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $part->sku ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">${{ number_format($part->unit_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $parts->links() }}</div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 p-4">Inventory</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Part</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($inventory as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->part?->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->location?->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $inventory->links() }}</div>
            </div>
        </div>
    </div>
</div>
