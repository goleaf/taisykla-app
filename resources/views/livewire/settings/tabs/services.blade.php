<div class="space-y-6">

    {{-- Priority Levels (New) --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Priority Levels & SLAs</h2>
                <p class="text-sm text-gray-500">Define query response targets and urgency levels.</p>
            </div>
            <button class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50 text-sm"
                wire:click="$toggle('showPriorityCreate')">Add Priority</button>
        </div>

        @if($showPriorityCreate)
            <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <form wire:submit.prevent="createPriorityLevel" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Name</label>
                        <input wire:model="newPriority.name" class="mt-1 w-full rounded-md border-gray-300"
                            placeholder="e.g. Critical" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Color</label>
                        <div class="flex gap-2 mt-1">
                            <input type="color" wire:model.live="newPriority.color"
                                class="h-9 w-12 rounded border border-gray-300 p-1" />
                            <input wire:model="newPriority.color" class="w-full rounded-md border-gray-300" />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Response Time (min)</label>
                        <input type="number" wire:model="newPriority.response_time_minutes"
                            class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Resolution Time (min)</label>
                        <input type="number" wire:model="newPriority.resolution_time_minutes"
                            class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="col-span-2">
                        <label class="text-xs text-gray-500 font-medium uppercase">Description</label>
                        <input wire:model="newPriority.description" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="col-span-2 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showPriorityCreate', false)"
                            class="px-3 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="space-y-2 text-sm">
            @forelse($priorityLevels as $priority)
                <div class="flex items-center justify-between p-3 border border-gray-100 rounded-md bg-white">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $priority->color }}"></span>
                        <span class="font-medium text-gray-900">{{ $priority->name }}</span>
                        @if($priority->response_time_minutes)
                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $priority->response_time_minutes }}m response
                            </span>
                        @endif
                    </div>
                    <span class="text-gray-400 text-xs">{{ $priority->description }}</span>
                </div>
            @empty
                <div class="text-gray-500 text-center py-4">No priority levels defined.</div>
            @endforelse
        </div>
    </div>

    {{-- Service Agreements --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Service Agreements</h2>
                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                    wire:click="$toggle('showAgreementCreate')">Add</button>
            </div>
            @if ($showAgreementCreate)
                <form wire:submit.prevent="createAgreement"
                    class="grid grid-cols-1 gap-3 mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                    <input wire:model="newAgreement.name" class="rounded-md border-gray-300" placeholder="Name" />
                    <input wire:model="newAgreement.agreement_type" class="rounded-md border-gray-300"
                        placeholder="Type (e.g. Standard)" />
                    <input type="number" step="0.01" wire:model="newAgreement.monthly_fee"
                        class="rounded-md border-gray-300" placeholder="Monthly Fee" />
                    <button class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                </form>
            @endif
            <div class="space-y-2 text-sm">
                @foreach ($agreements as $agreement)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <span>{{ $agreement->name }}</span>
                        <span class="text-gray-500 font-medium">${{ number_format($agreement->monthly_fee, 2) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">{{ $agreements->links() }}</div>
        </div>

        {{-- Work Order Categories --}}
        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Work Order Categories</h2>
                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                    wire:click="$toggle('showCategoryCreate')">Add</button>
            </div>
            @if ($showCategoryCreate)
                <form wire:submit.prevent="createCategory"
                    class="grid grid-cols-1 gap-3 mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                    <input wire:model="newCategory.name" class="rounded-md border-gray-300" placeholder="Name" />
                    <input type="number" wire:model="newCategory.default_estimated_minutes"
                        class="rounded-md border-gray-300" placeholder="Est. minutes" />
                    <button class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                </form>
            @endif
            <div class="space-y-2 text-sm">
                @foreach ($categories as $category)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <span>{{ $category->name }}</span>
                        <span class="text-gray-500 text-xs">{{ $category->default_estimated_minutes ?? '—' }} mins</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">{{ $categories->links() }}</div>
        </div>
    </div>

    {{-- Equipment Categories --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Equipment Categories</h2>
            <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                wire:click="$toggle('showEquipmentCategoryCreate')">Add</button>
        </div>
        @if ($showEquipmentCategoryCreate)
            <form wire:submit.prevent="createEquipmentCategory"
                class="grid grid-cols-1 gap-3 mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                <input wire:model="newEquipmentCategory.name" class="rounded-md border-gray-300" placeholder="Name" />
                <textarea wire:model="newEquipmentCategory.description" class="rounded-md border-gray-300" rows="2"
                    placeholder="Description"></textarea>
                <button class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
            </form>
        @endif
        <div class="space-y-2 text-sm">
            @foreach ($equipmentCategories as $category)
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                    <span>{{ $category->name }}</span>
                    <span class="text-gray-500 text-xs">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>