<div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Automation Rules</h2>
                <p class="text-sm text-gray-500">Configure triggers and automatic actions.</p>
            </div>
            <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                wire:click="$toggle('showAutomationCreate')">Add Rule</button>
        </div>

        @if ($showAutomationCreate)
            <div class="mb-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                <form wire:submit.prevent="createAutomationRule" class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input wire:model="newAutomation.name" class="rounded-md border-gray-300" placeholder="Rule Name" />
                        <input wire:model="newAutomation.trigger" class="rounded-md border-gray-300"
                            placeholder="Trigger Event (e.g. work_order.created)" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Conditions (JSON)</label>
                        <textarea wire:model="newAutomation.conditions"
                            class="w-full rounded-md border-gray-300 text-sm font-mono bg-white" rows="3"
                            placeholder='{"priority": "high"}'></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Actions (JSON)</label>
                        <textarea wire:model="newAutomation.actions"
                            class="w-full rounded-md border-gray-300 text-sm font-mono bg-white" rows="3"
                            placeholder='{"assign_team": "support"}'></textarea>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" wire:model="newAutomation.is_active"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <span>Active immediately</span>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showAutomationCreate', false)"
                            class="px-3 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save Rule</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="space-y-3 text-sm">
            @forelse ($automationRules as $rule)
                <div
                    class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:border-gray-200 transition-colors">
                    <div>
                        <div class="font-medium text-gray-900">{{ $rule->name }}</div>
                        <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $rule->trigger }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $rule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $rule->is_active ? 'Active' : 'Disabled' }}
                        </span>
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    No automation rules configured.
                </div>
            @endforelse
        </div>
    </div>
</div>