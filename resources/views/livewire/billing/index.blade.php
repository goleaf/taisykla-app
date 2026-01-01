<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Billing</h1>
                <p class="text-sm text-gray-500">Invoices, quotes, and payment tracking.</p>
            </div>
            @if ($user->hasAnyRole(['admin', 'dispatch']))
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="$toggle('showInvoiceCreate')">New Invoice</button>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($showInvoiceCreate && $user->hasAnyRole(['admin', 'dispatch']))
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Invoice</h2>
                <form wire:submit.prevent="createInvoice" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs text-gray-500">Organization</label>
                        <select wire:model="newInvoice.organization_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select organization</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Subtotal</label>
                        <input type="number" step="0.01" wire:model="newInvoice.subtotal" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Tax</label>
                        <input type="number" step="0.01" wire:model="newInvoice.tax" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Total</label>
                        <input type="number" step="0.01" wire:model="newInvoice.total" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Due Date</label>
                        <input type="date" wire:model="newInvoice.due_date" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Status</label>
                        <select wire:model="newInvoice.status" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="text-xs text-gray-500">Notes</label>
                        <textarea wire:model="newInvoice.notes" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                    </div>
                    <div class="md:col-span-3">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 p-4">Invoices</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">#{{ $invoice->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $invoice->organization?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($invoice->status) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">${{ number_format($invoice->total, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-indigo-600">
                                    @if ($invoice->status !== 'paid')
                                        <button wire:click="markPaid({{ $invoice->id }})">Mark Paid</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $invoices->links() }}</div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 p-4">Quotes</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($quotes as $quote)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">#{{ $quote->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $quote->organization?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst($quote->status) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">${{ number_format($quote->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $quotes->links() }}</div>
            </div>
        </div>
    </div>
</div>
