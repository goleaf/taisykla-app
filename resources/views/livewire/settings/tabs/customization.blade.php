<div class="space-y-6">

    {{-- Custom Fields --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Custom Fields</h2>
                <p class="text-sm text-gray-500">Extend data models with extra fields.</p>
            </div>
            <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                wire:click="$toggle('showCustomFieldForm')">Add Field</button>
        </div>

        @if($showCustomFieldForm)
            <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <form wire:submit.prevent="createCustomField" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Entity</label>
                        <select wire:model="customFieldForm.entity_type" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach($customFieldEntityOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Type</label>
                        <select wire:model="customFieldForm.type" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach($customFieldTypeOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Label</label>
                        <input wire:model="customFieldForm.label" class="mt-1 w-full rounded-md border-gray-300"
                            placeholder="e.g. Serial Number" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Key (Internal)</label>
                        <input wire:model="customFieldForm.key" class="mt-1 w-full rounded-md border-gray-300"
                            placeholder="e.g. serial_number" />
                    </div>
                    <div class="col-span-2 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showCustomFieldForm', false)"
                            class="px-3 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
                        <button class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50" wire:loading.attr="disabled" wire:target="createCustomField">
                            <span wire:loading.remove wire:target="createCustomField">Save</span>
                            <span wire:loading wire:target="createCustomField">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Entity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customFields as $field)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $field->label }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $customFieldEntityOptions[$field->entity_type] ?? $field->entity_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $customFieldTypeOptions[$field->type] ?? $field->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono text-xs">
                                {{ $field->key }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No custom fields defined.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Custom Statuses --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Custom Statuses</h2>
                <p class="text-sm text-gray-500">Define specific workflow states.</p>
            </div>
            <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                wire:click="$toggle('showCustomStatusForm')">Add Status</button>
        </div>

        @if($showCustomStatusForm)
            <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <form wire:submit.prevent="createCustomStatus" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Context</label>
                        <select wire:model="customStatusForm.context" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach($statusContextOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Lifecycle State</label>
                        <select wire:model="customStatusForm.state" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select State</option>
                            @if(($customStatusForm['context'] ?? '') === 'work_order')
                                @foreach($workOrderStateOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            @else
                                @foreach($equipmentStateOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Label</label>
                        <input wire:model="customStatusForm.label" class="mt-1 w-full rounded-md border-gray-300"
                            placeholder="e.g. Waiting for Parts" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Key (Internal)</label>
                        <input wire:model="customStatusForm.key" class="mt-1 w-full rounded-md border-gray-300"
                            placeholder="e.g. waiting_parts" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Color</label>
                        <div class="flex gap-2 mt-1">
                            <input type="color" wire:model.live="customStatusForm.color"
                                class="h-9 w-12 rounded border border-gray-300 p-1" />
                            <input wire:model="customStatusForm.color" class="w-full rounded-md border-gray-300" />
                        </div>
                    </div>
                    <div class="col-span-2 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showCustomStatusForm', false)"
                            class="px-3 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
                        <button class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50" wire:loading.attr="disabled" wire:target="createCustomStatus">
                            <span wire:loading.remove wire:target="createCustomStatus">Save</span>
                            <span wire:loading wire:target="createCustomStatus">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="space-y-2 text-sm">
            @forelse($customStatuses as $status)
                <div class="flex items-center justify-between p-3 border border-gray-100 rounded-md bg-white">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $status->color }}"></span>
                        <span class="font-medium text-gray-900">{{ $status->label }}</span>
                        <span class="text-xs text-gray-400">({{ $status->key }})</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span
                            class="text-xs text-gray-500 uppercase tracking-wide">{{ $statusContextOptions[$status->context] ?? $status->context }}</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $status->state }}</span>
                    </div>
                </div>
            @empty
                <div class="text-gray-500 text-center py-4">No custom statuses defined.</div>
            @endforelse
        </div>
    </div>

    {{-- Label Overrides --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Label Overrides</h2>
                <p class="text-sm text-gray-500">Customize system terminology.</p>
            </div>
            <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                wire:click="$toggle('showLabelForm')">Add Override</button>
        </div>

        @if($showLabelForm)
            <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <form wire:submit.prevent="createLabelOverride" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Group</label>
                        <select wire:model="labelForm.group" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach($labelGroupOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Key</label>
                        <input wire:model="labelForm.key" class="mt-1 w-full rounded-md border-gray-300"
                            placeholder="e.g. work_order" />
                    </div>
                    <div class="col-span-2">
                        <label class="text-xs text-gray-500 font-medium uppercase">Display Value</label>
                        <input wire:model="labelForm.value" class="mt-1 w-full rounded-md border-gray-300"
                            placeholder="e.g. Service Request" />
                    </div>
                    <div class="col-span-2 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showLabelForm', false)"
                            class="px-3 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
                        <button class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50" wire:loading.attr="disabled" wire:target="createLabelOverride">
                            <span wire:loading.remove wire:target="createLabelOverride">Save</span>
                            <span wire:loading wire:target="createLabelOverride">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="space-y-2 text-sm">
            @forelse($labelOverrides as $override)
                <div class="flex items-center justify-between p-3 border border-gray-100 rounded-md bg-white">
                    <div>
                        <span class="font-medium text-gray-900">{{ $override->value }}</span>
                        <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $override->group }}.{{ $override->key }}
                        </div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $override->locale }}</span>
                </div>
            @empty
                <div class="text-gray-500 text-center py-4">No label overrides defined.</div>
            @endforelse
        </div>
    </div>

</div>