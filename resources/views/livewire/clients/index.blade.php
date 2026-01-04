<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Clients</h1>
                <p class="text-sm text-gray-500">Manage customer accounts and service agreements.</p>
            </div>
            @if ($canManage)
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="$toggle('showCreate')">Add Client</button>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 mb-6 relative overflow-hidden">
            <div wire:loading wire:target="search, statusFilter, typeFilter" class="absolute inset-0 bg-white/50 z-10 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Search</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="mt-1 w-full rounded-md border-gray-300"
                        placeholder="Name, contact, or email"
                    />
                </div>
                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <select wire:model.live="statusFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="all">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Type</label>
                    <select wire:model.live="typeFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="all">All types</option>
                        <option value="business">Business</option>
                        <option value="individual">Individual</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <button type="button" class="text-indigo-600" wire:click="clearFilters">Clear filters</button>
                <span>Found {{ $organizations->total() }} clients</span>
            </div>
        </div>

        @if ($showCreate && $canManage)
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">New Client Organization</h2>
                <form wire:submit.prevent="createOrganization" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500">Name</label>
                        <input wire:model="new.name" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('new.name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Type</label>
                        <select wire:model="new.type" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="business">Business</option>
                            <option value="individual">Individual</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Status</label>
                        <select wire:model="new.status" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Service Agreement</label>
                        <select wire:model="new.service_agreement_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select agreement</option>
                            @foreach ($agreements as $agreement)
                                <option value="{{ $agreement->id }}">{{ $agreement->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Primary Contact</label>
                        <input wire:model="new.primary_contact_name" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Contact Email</label>
                        <input wire:model="new.primary_contact_email" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Contact Phone</label>
                        <input wire:model="new.primary_contact_phone" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Billing Email</label>
                        <input wire:model="new.billing_email" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Billing Address</label>
                        <textarea wire:model="new.billing_address" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Notes</label>
                        <textarea wire:model="new.notes" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-3">
                        <button class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled" wire:target="createOrganization">
                            <span wire:loading wire:target="createOrganization" class="mr-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            Save
                        </button>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agreement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($organizations as $organization)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $organization->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($organization->type) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($organization->status) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $organization->primary_contact_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $organization->serviceAgreement?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">
                {{ $organizations->links() }}
            </div>
        </div>
    </div>
</div>
