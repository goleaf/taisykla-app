<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Quote;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showInvoiceCreate = false;
    public array $newInvoice = [];

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetNewInvoice();
    }

    public function resetNewInvoice(): void
    {
        $this->newInvoice = [
            'organization_id' => null,
            'work_order_id' => null,
            'status' => 'draft',
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'due_date' => null,
            'notes' => '',
        ];
    }

    public function createInvoice(): void
    {
        $this->validate([
            'newInvoice.organization_id' => ['nullable', 'exists:organizations,id'],
            'newInvoice.work_order_id' => ['nullable', 'exists:work_orders,id'],
            'newInvoice.status' => ['required', 'string', 'max:50'],
            'newInvoice.subtotal' => ['required', 'numeric', 'min:0'],
            'newInvoice.tax' => ['required', 'numeric', 'min:0'],
            'newInvoice.total' => ['required', 'numeric', 'min:0'],
            'newInvoice.due_date' => ['nullable', 'date'],
            'newInvoice.notes' => ['nullable', 'string'],
        ]);

        Invoice::create($this->newInvoice);
        session()->flash('status', 'Invoice created.');
        $this->resetNewInvoice();
        $this->showInvoiceCreate = false;
    }

    public function markPaid(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function render()
    {
        $user = auth()->user();

        $invoiceQuery = Invoice::query()->with('organization');
        $quoteQuery = Quote::query()->with('organization');

        if ($user->hasRole('client')) {
            $invoiceQuery->where('organization_id', $user->organization_id);
            $quoteQuery->where('organization_id', $user->organization_id);
        }

        $invoices = $invoiceQuery->latest()->paginate(10, pageName: 'invoices');
        $quotes = $quoteQuery->latest()->paginate(10, pageName: 'quotes');
        $organizations = Organization::orderBy('name')->get();

        return view('livewire.billing.index', [
            'invoices' => $invoices,
            'quotes' => $quotes,
            'organizations' => $organizations,
            'user' => $user,
        ]);
    }
}
