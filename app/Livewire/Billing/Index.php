<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Quote;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showInvoiceForm = false;
    public array $invoiceForm = [];
    public string $search = '';
    public string $organizationFilter = '';
    public string $invoiceStatusFilter = 'all';
    public string $quoteStatusFilter = 'all';

    protected $paginationTheme = 'tailwind';

    public array $invoiceStatusOptions = [
        'all' => 'All',
        'draft' => 'Draft',
        'sent' => 'Sent',
        'paid' => 'Paid',
    ];

    public array $quoteStatusOptions = [
        'all' => 'All',
        'draft' => 'Draft',
        'sent' => 'Sent',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::BILLING_VIEW), 403);

        $this->resetInvoiceForm();
    }

    public function resetInvoiceForm(): void
    {
        $this->invoiceForm = [
            'organization_id' => null,
            'work_order_id' => null,
            'status' => 'draft',
            'subtotal' => 0,
            'tax' => 0,
            'due_date' => null,
            'notes' => '',
        ];

        $this->recalculateTotal();
    }

    public function updatedSearch(): void
    {
        $this->resetPagination();
    }

    public function updatedOrganizationFilter(): void
    {
        $this->resetPagination();
    }

    public function updatedInvoiceStatusFilter(): void
    {
        $this->resetPagination();
    }

    public function updatedQuoteStatusFilter(): void
    {
        $this->resetPagination();
    }

    public function updatedInvoiceFormSubtotal(): void
    {
        $this->recalculateTotal();
    }

    public function updatedInvoiceFormTax(): void
    {
        $this->recalculateTotal();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->organizationFilter = '';
        $this->invoiceStatusFilter = 'all';
        $this->quoteStatusFilter = 'all';
        $this->resetPagination();
    }

    public function startInvoiceCreate(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->resetInvoiceForm();
        $this->showInvoiceForm = true;
    }

    public function cancelInvoiceForm(): void
    {
        $this->resetInvoiceForm();
        $this->showInvoiceForm = false;
    }

    protected function rules(): array
    {
        return [
            'invoiceForm.organization_id' => ['nullable', 'exists:organizations,id'],
            'invoiceForm.work_order_id' => ['nullable', 'exists:work_orders,id'],
            'invoiceForm.status' => ['required', Rule::in($this->invoiceStatuses())],
            'invoiceForm.subtotal' => ['required', 'numeric', 'min:0'],
            'invoiceForm.tax' => ['required', 'numeric', 'min:0'],
            'invoiceForm.due_date' => ['nullable', 'date'],
            'invoiceForm.notes' => ['nullable', 'string'],
        ];
    }

    public function createInvoice(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->validate();

        $status = $this->invoiceForm['status'];
        $subtotal = $this->normalizeAmount($this->invoiceForm['subtotal']);
        $tax = $this->normalizeAmount($this->invoiceForm['tax']);
        $total = $this->normalizeAmount($subtotal + $tax);
        $sentAt = in_array($status, ['sent', 'paid'], true) ? now() : null;
        $paidAt = $status === 'paid' ? now() : null;

        Invoice::create([
            'organization_id' => $this->normalizeId($this->invoiceForm['organization_id']),
            'work_order_id' => $this->normalizeId($this->invoiceForm['work_order_id']),
            'status' => $status,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'due_date' => $this->invoiceForm['due_date'] ?: null,
            'sent_at' => $sentAt,
            'paid_at' => $paidAt,
            'notes' => $this->invoiceForm['notes'],
        ]);

        session()->flash('status', 'Invoice created.');
        $this->resetInvoiceForm();
        $this->showInvoiceForm = false;
    }

    public function markPaid(int $invoiceId): void
    {
        $this->updateInvoiceStatus($invoiceId, 'paid');
    }

    public function markSent(int $invoiceId): void
    {
        $this->updateInvoiceStatus($invoiceId, 'sent');
    }

    public function updateInvoiceStatus(int $invoiceId, string $status): void
    {
        if (! $this->canManage) {
            return;
        }

        if (! in_array($status, $this->invoiceStatuses(), true)) {
            return;
        }

        $invoice = Invoice::findOrFail($invoiceId);

        $updates = ['status' => $status];

        if ($status === 'draft') {
            $updates['sent_at'] = null;
            $updates['paid_at'] = null;
        }

        if ($status === 'sent') {
            $updates['sent_at'] = $invoice->sent_at ?? now();
            $updates['paid_at'] = null;
        }

        if ($status === 'paid') {
            $updates['paid_at'] = now();
            if (! $invoice->sent_at) {
                $updates['sent_at'] = now();
            }
        }

        $invoice->update($updates);
    }

    public function render()
    {
        $user = auth()->user();

        $invoiceQuery = Invoice::query()->with('organization');
        $quoteQuery = Quote::query()->with('organization');

        $this->applyBillingVisibility($invoiceQuery, $user);
        $this->applyBillingVisibility($quoteQuery, $user);

        if ($this->canManage && $this->organizationFilter !== '') {
            $invoiceQuery->where('organization_id', $this->organizationFilter);
            $quoteQuery->where('organization_id', $this->organizationFilter);
        }

        if ($this->search !== '') {
            $this->applySearch($invoiceQuery, $this->search);
            $this->applySearch($quoteQuery, $this->search);
        }

        $invoiceSummaryQuery = clone $invoiceQuery;
        $quoteSummaryQuery = clone $quoteQuery;

        if ($this->invoiceStatusFilter !== 'all') {
            $invoiceQuery->where('status', $this->invoiceStatusFilter);
        }

        if ($this->quoteStatusFilter !== 'all') {
            $quoteQuery->where('status', $this->quoteStatusFilter);
        }

        $invoices = $invoiceQuery->orderByDesc('created_at')->paginate(10, pageName: 'invoices');
        $quotes = $quoteQuery->orderByDesc('created_at')->paginate(10, pageName: 'quotes');
        $invoiceSummary = $this->buildInvoiceSummary($invoiceSummaryQuery);
        $quoteSummary = $this->buildQuoteSummary($quoteSummaryQuery);
        $organizations = $this->canManage ? Organization::orderBy('name')->get() : collect();

        return view('livewire.billing.index', [
            'invoices' => $invoices,
            'quotes' => $quotes,
            'organizations' => $organizations,
            'user' => $user,
            'canManage' => $this->canManage,
            'invoiceSummary' => $invoiceSummary,
            'quoteSummary' => $quoteSummary,
        ]);
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canManageBilling();
    }

    private function invoiceStatuses(): array
    {
        return ['draft', 'sent', 'paid'];
    }

    private function resetPagination(): void
    {
        $this->resetPage('invoices');
        $this->resetPage('quotes');
    }

    private function recalculateTotal(): void
    {
        $subtotal = $this->normalizeAmount($this->invoiceForm['subtotal'] ?? 0);
        $tax = $this->normalizeAmount($this->invoiceForm['tax'] ?? 0);
        $this->invoiceForm['total'] = $this->normalizeAmount($subtotal + $tax);
    }

    private function normalizeAmount(mixed $value): float
    {
        if ($value === '' || $value === null) {
            return 0.0;
        }

        return round((float) $value, 2);
    }

    private function normalizeId(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    private function applySearch(Builder $query, string $search): void
    {
        $search = trim($search);
        $searchLike = '%' . $search . '%';

        $query->where(function (Builder $builder) use ($search, $searchLike) {
            $builder->where('status', 'like', $searchLike)
                ->orWhere('notes', 'like', $searchLike)
                ->orWhereHas('organization', function (Builder $orgBuilder) use ($searchLike) {
                    $orgBuilder->where('name', 'like', $searchLike);
                });

            if (is_numeric($search)) {
                $builder->orWhere('id', (int) $search)
                    ->orWhere('work_order_id', (int) $search);
            }
        });
    }

    private function buildInvoiceSummary(Builder $query): array
    {
        $today = now()->toDateString();

        return [
            'outstanding_total' => (clone $query)->where('status', '!=', 'paid')->sum('total'),
            'overdue_count' => (clone $query)
                ->where('status', '!=', 'paid')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $today)
                ->count(),
            'draft_count' => (clone $query)->where('status', 'draft')->count(),
            'sent_count' => (clone $query)->where('status', 'sent')->count(),
            'paid_total' => (clone $query)->where('status', 'paid')->sum('total'),
        ];
    }

    private function buildQuoteSummary(Builder $query): array
    {
        return [
            'total_count' => (clone $query)->count(),
            'draft_count' => (clone $query)->where('status', 'draft')->count(),
            'sent_count' => (clone $query)->where('status', 'sent')->count(),
            'approved_count' => (clone $query)->where('status', 'approved')->count(),
            'total_value' => (clone $query)->sum('total'),
        ];
    }

    private function applyBillingVisibility(Builder $query, $user): void
    {
        if ($user->can(PermissionCatalog::BILLING_VIEW_ALL)) {
            return;
        }

        $hasScope = false;
        $query->where(function (Builder $builder) use ($user, &$hasScope) {
            if ($user->can(PermissionCatalog::BILLING_VIEW_ORG) && $user->organization_id) {
                $builder->orWhere('organization_id', $user->organization_id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::BILLING_VIEW_OWN)) {
                $builder->orWhereHas('workOrder', function (Builder $workOrderBuilder) use ($user) {
                    $workOrderBuilder->where('requested_by_user_id', $user->id);
                });
                $hasScope = true;
            }
        });

        if (! $hasScope) {
            $query->whereRaw('1 = 0');
        }
    }
}
