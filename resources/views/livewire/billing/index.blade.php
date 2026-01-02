<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Billing</h1>
                <p class="text-sm text-gray-500">Invoices, quotes, and payment tracking.</p>
            </div>
            @if ($canManage)
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="startInvoiceCreate">New Invoice</button>
            @endif
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Outstanding</p>
                <p class="text-2xl font-semibold text-gray-900">${{ number_format($invoiceSummary['outstanding_total'] ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500">{{ $invoiceSummary['overdue_count'] ?? 0 }} overdue</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Paid to Date</p>
                <p class="text-2xl font-semibold text-gray-900">${{ number_format($invoiceSummary['paid_total'] ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500">{{ $invoiceSummary['sent_count'] ?? 0 }} sent</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Draft Invoices</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $invoiceSummary['draft_count'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">Ready to send</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Quotes Approved</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $quoteSummary['approved_count'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">${{ number_format($quoteSummary['total_value'] ?? 0, 2) }} total value</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label class="text-xs text-gray-500">Search</label>
                    <input wire:model.debounce.300ms="search" class="mt-1 w-full rounded-md border-gray-300" placeholder="Invoice #, org, notes" />
                </div>
                @if ($canManage)
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
                    <label class="text-xs text-gray-500">Invoice Status</label>
                    <select wire:model="invoiceStatusFilter" class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($invoiceStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Quote Status</label>
                    <select wire:model="quoteStatusFilter" class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($quoteStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <button type="button" class="text-indigo-600" wire:click="clearFilters">Clear filters</button>
                <span>{{ $invoices->total() }} invoices · {{ $quotes->total() }} quotes</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="{{ $canManage ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                    <div class="flex items-center justify-between p-4">
                        <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
                        <span class="text-xs text-gray-500">Updated {{ now()->format('M d, Y') }}</span>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse ($invoices as $invoice)
                            @php
                                $isOverdue = $invoice->due_date && $invoice->status !== 'paid' && $invoice->due_date->isPast();
                            @endphp
                            <div class="p-5" wire:key="invoice-{{ $invoice->id }}">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-semibold text-gray-900">Invoice #{{ $invoice->id }}</h3>
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs',
                                                'bg-gray-100 text-gray-700' => $invoice->status === 'draft',
                                                'bg-blue-100 text-blue-700' => $invoice->status === 'sent',
                                                'bg-green-100 text-green-700' => $invoice->status === 'paid',
                                            ])>
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                            @if ($isOverdue)
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs bg-red-100 text-red-700">Overdue</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <span>Org: {{ $invoice->organization?->name ?? '—' }}</span>
                                            <span>Due: {{ $invoice->due_date?->format('M d, Y') ?? 'No due date' }}</span>
                                            <span>Sent: {{ $invoice->sent_at?->format('M d, Y') ?? '—' }}</span>
                                            <span>Paid: {{ $invoice->paid_at?->format('M d, Y') ?? '—' }}</span>
                                        </div>
                                        @if ($invoice->notes)
                                            <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($invoice->notes, 140) }}</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="text-lg font-semibold text-gray-900">${{ number_format($invoice->total, 2) }}</div>
                                        <div class="text-xs text-gray-500">Subtotal ${{ number_format($invoice->subtotal, 2) }} · Tax ${{ number_format($invoice->tax, 2) }}</div>
                                        @if ($canManage)
                                            <div class="flex items-center gap-2 text-xs">
                                                @if ($invoice->status === 'draft')
                                                    <button class="px-2 py-1 border border-gray-300 rounded-md text-gray-600" wire:click="markSent({{ $invoice->id }})">Mark Sent</button>
                                                @endif
                                                @if ($invoice->status !== 'paid')
                                                    <button class="px-2 py-1 border border-gray-300 rounded-md text-gray-600" wire:click="markPaid({{ $invoice->id }})">Mark Paid</button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">No invoices match the current filters.</div>
                        @endforelse
                    </div>
                    <div class="p-4">{{ $invoices->links() }}</div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                    <div class="flex items-center justify-between p-4">
                        <h2 class="text-lg font-semibold text-gray-900">Quotes</h2>
                        <span class="text-xs text-gray-500">{{ $quoteSummary['total_count'] ?? 0 }} total</span>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse ($quotes as $quote)
                            <div class="p-5" wire:key="quote-{{ $quote->id }}">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-semibold text-gray-900">Quote #{{ $quote->id }}</h3>
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs',
                                                'bg-gray-100 text-gray-700' => $quote->status === 'draft',
                                                'bg-blue-100 text-blue-700' => $quote->status === 'sent',
                                                'bg-green-100 text-green-700' => $quote->status === 'approved',
                                                'bg-red-100 text-red-700' => $quote->status === 'rejected',
                                            ])>
                                                {{ ucfirst($quote->status) }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <span>Org: {{ $quote->organization?->name ?? '—' }}</span>
                                            <span>Valid until: {{ $quote->valid_until?->format('M d, Y') ?? '—' }}</span>
                                            <span>Sent: {{ $quote->sent_at?->format('M d, Y') ?? '—' }}</span>
                                            <span>Approved: {{ $quote->approved_at?->format('M d, Y') ?? '—' }}</span>
                                        </div>
                                        @if ($quote->notes)
                                            <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($quote->notes, 140) }}</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="text-lg font-semibold text-gray-900">${{ number_format($quote->total, 2) }}</div>
                                        <div class="text-xs text-gray-500">Subtotal ${{ number_format($quote->subtotal, 2) }} · Tax ${{ number_format($quote->tax, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">No quotes match the current filters.</div>
                        @endforelse
                    </div>
                    <div class="p-4">{{ $quotes->links() }}</div>
                </div>
            </div>

            @if ($canManage)
                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">New Invoice</h2>
                            @if ($showInvoiceForm)
                                <button class="text-sm text-gray-500" type="button" wire:click="cancelInvoiceForm">Close</button>
                            @endif
                        </div>

                        @if ($showInvoiceForm)
                            <form wire:submit.prevent="createInvoice" class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-500">Organization</label>
                                    <select wire:model="invoiceForm.organization_id" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select organization</option>
                                        @foreach ($organizations as $organization)
                                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('invoiceForm.organization_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Work Order ID</label>
                                    <input type="number" wire:model="invoiceForm.work_order_id" class="mt-1 w-full rounded-md border-gray-300" placeholder="Optional" />
                                    @error('invoiceForm.work_order_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Subtotal</label>
                                        <input type="number" step="0.01" wire:model="invoiceForm.subtotal" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('invoiceForm.subtotal') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Tax</label>
                                        <input type="number" step="0.01" wire:model="invoiceForm.tax" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('invoiceForm.tax') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Total</label>
                                    <input type="number" step="0.01" wire:model="invoiceForm.total" class="mt-1 w-full rounded-md border-gray-300 bg-gray-50" readonly />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Due Date</label>
                                    <input type="date" wire:model="invoiceForm.due_date" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('invoiceForm.due_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Status</label>
                                    <select wire:model="invoiceForm.status" class="mt-1 w-full rounded-md border-gray-300">
                                        @foreach ($invoiceStatusOptions as $value => $label)
                                            @if ($value !== 'all')
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('invoiceForm.status') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Notes</label>
                                    <textarea wire:model="invoiceForm.notes" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                                    @error('invoiceForm.notes') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex items-center gap-2">
                                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save Invoice</button>
                                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="cancelInvoiceForm">Cancel</button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm text-gray-500">Select "New Invoice" to create a billing record.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
