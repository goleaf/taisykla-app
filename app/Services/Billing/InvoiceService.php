<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Organization;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        private readonly PricingEngine $pricing,
        private readonly TaxEngine $taxes,
        private readonly BillingCalculator $calculator,
    ) {
    }

    public function createFromWorkOrder(WorkOrder $workOrder, array $data = []): Invoice
    {
        $organization = $workOrder->organization;
        if (! $organization) {
            throw new \RuntimeException('Work order must have an organization to generate an invoice.');
        }

        $items = $this->buildItemsFromWorkOrder($workOrder, $organization);

        $payload = array_merge([
            'organization_id' => $organization->id,
            'work_order_id' => $workOrder->id,
            'status' => $data['status'] ?? 'draft',
            'invoice_type' => $data['invoice_type'] ?? 'standard',
            'issued_at' => $data['issued_at'] ?? now(),
            'terms' => $data['terms'] ?? $organization->payment_terms,
            'currency' => $data['currency'] ?? ($organization->billing_currency ?? config('billing.default_currency', 'USD')),
        ], Arr::only($data, ['due_date', 'sent_at', 'paid_at', 'notes', 'invoice_number']));

        return $this->createManual($payload, $items);
    }

    public function createManual(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items) {
            $issuedAt = $data['issued_at'] instanceof Carbon ? $data['issued_at'] : Carbon::parse($data['issued_at'] ?? now());
            $organization = isset($data['organization_id']) ? Organization::find($data['organization_id']) : null;
            $dueDate = $data['due_date'] ?? ($organization ? $this->resolveDueDate($organization, $issuedAt) : null);

            $items = $this->applyTaxRates($organization, $items);
            $calculated = $this->calculator->calculate($items, [
                'adjustment_total' => $data['adjustment_total'] ?? 0,
            ]);

            $invoice = Invoice::create([
                'invoice_number' => $data['invoice_number'] ?? null,
                'organization_id' => $data['organization_id'] ?? null,
                'work_order_id' => $data['work_order_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'invoice_type' => $data['invoice_type'] ?? 'standard',
                'issued_at' => $issuedAt,
                'due_date' => $dueDate,
                'sent_at' => $data['sent_at'] ?? null,
                'paid_at' => $data['paid_at'] ?? null,
                'terms' => $data['terms'] ?? null,
                'currency' => $data['currency'] ?? config('billing.default_currency', 'USD'),
                'subtotal' => $calculated['totals']['subtotal'],
                'tax' => $calculated['totals']['tax_total'],
                'total' => $calculated['totals']['total'],
                'labor_subtotal' => $calculated['totals']['labor_subtotal'],
                'parts_subtotal' => $calculated['totals']['parts_subtotal'],
                'fees_subtotal' => $calculated['totals']['fees_subtotal'],
                'discount_total' => $calculated['totals']['discount_total'],
                'tax_total' => $calculated['totals']['tax_total'],
                'adjustment_total' => $data['adjustment_total'] ?? 0,
                'balance_due' => $calculated['totals']['total'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($calculated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'] ?? 'Line item',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['line_total'],
                    'item_type' => $item['item_type'] ?? 'service',
                    'part_id' => $item['part_id'] ?? null,
                    'service_type' => $item['service_type'] ?? null,
                    'labor_minutes' => $item['labor_minutes'] ?? null,
                    'unit_cost' => $item['unit_cost'] ?? null,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'is_taxable' => $item['is_taxable'] ?? true,
                    'position' => $item['position'] ?? null,
                    'metadata' => $item['metadata'] ?? null,
                ]);
            }

            return $invoice->refresh();
        });
    }

    private function buildItemsFromWorkOrder(WorkOrder $workOrder, Organization $organization): array
    {
        $items = [];

        if ($workOrder->labor_minutes) {
            $rate = $this->pricing->resolveLaborRate($organization, $workOrder->category?->name, $workOrder->scheduled_start_at);
            $hours = round($workOrder->labor_minutes / 60, 2);
            $items[] = [
                'description' => sprintf('Labor (%s hours)', $hours),
                'quantity' => $hours,
                'unit_price' => $rate['rate'],
                'unit_cost' => $rate['rate'],
                'item_type' => 'labor',
                'service_type' => $workOrder->category?->name,
                'labor_minutes' => $workOrder->labor_minutes,
                'is_taxable' => true,
            ];
        }

        $workOrder->loadMissing('parts.part');
        foreach ($workOrder->parts as $partLine) {
            if (! $partLine->part) {
                continue;
            }

            $pricing = $this->pricing->resolvePartPrice($organization, $partLine->part, $partLine->quantity);
            $items[] = [
                'description' => $partLine->part->name,
                'quantity' => $partLine->quantity,
                'unit_price' => $partLine->unit_price > 0 ? $partLine->unit_price : $pricing['unit_price'],
                'unit_cost' => $partLine->unit_cost ?? $partLine->part->unit_cost,
                'item_type' => 'part',
                'part_id' => $partLine->part_id,
                'part_category_id' => $partLine->part->part_category_id ?? null,
                'is_taxable' => true,
            ];
        }

        return $items;
    }

    private function applyTaxRates(?Organization $organization, array $items): array
    {
        if (! $organization) {
            return $items;
        }

        return array_map(function ($item) use ($organization) {
            if (! array_key_exists('tax_rate', $item)) {
                $item['tax_rate'] = $this->taxes->resolveTaxRate($organization, $item);
            }

            return $item;
        }, $items);
    }

    private function resolveDueDate(Organization $organization, Carbon $issuedAt): ?Carbon
    {
        $terms = trim((string) $organization->payment_terms);
        if ($terms === '') {
            return null;
        }

        if (stripos($terms, 'due on receipt') !== false) {
            return $issuedAt->copy();
        }

        if (preg_match('/net\s*(\d+)/i', $terms, $matches)) {
            $days = (int) $matches[1];
            return $issuedAt->copy()->addDays($days);
        }

        return null;
    }
}
