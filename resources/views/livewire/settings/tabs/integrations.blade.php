<div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Integrations</h2>
                <p class="text-sm text-gray-500">Connect with external services and APIs.</p>
            </div>
            <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                wire:click="$toggle('showIntegrationCreate')">Add Integration</button>
        </div>

        @if ($showIntegrationCreate)
            <div class="mb-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                <form wire:submit.prevent="createIntegrationSetting" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input wire:model="newIntegration.provider" class="rounded-md border-gray-300"
                        placeholder="Provider (e.g. stripe)" />
                    <input wire:model="newIntegration.name" class="rounded-md border-gray-300" placeholder="Display Name" />
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Configuration (JSON)</label>
                        <input wire:model="newIntegration.config"
                            class="w-full rounded-md border-gray-300 font-mono text-sm" placeholder='{"api_key": "..."}' />
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" wire:model="newIntegration.is_active"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <span>Active</span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('showIntegrationCreate', false)"
                                class="px-3 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
                            <button class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50" wire:loading.attr="disabled" wire:target="createIntegrationSetting">
                                <span wire:loading.remove wire:target="createIntegrationSetting">Save</span>
                                <span wire:loading wire:target="createIntegrationSetting">Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <div class="space-y-3 text-sm">
            @forelse ($integrationSettings as $integration)
                <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-white">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold uppercase text-xs">
                            {{ substr($integration->provider, 0, 2) }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ ucfirst($integration->provider) }}</div>
                            @if($integration->name)
                                <div class="text-xs text-gray-500">{{ $integration->name }}</div>
                            @endif
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $integration->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $integration->is_active ? 'Connected' : 'Disabled' }}
                    </span>
                </div>
            @empty
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <p class="text-gray-500">No integrations configured.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>