<?php

namespace App\Services\Billing;

use App\Models\Organization;
use App\Models\Quote;
use App\Models\QuoteItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(
        private readonly TaxEngine $taxes,
        private readonly BillingCalculator $calculator,
    ) {
    }

    public function createManual(array $data, array $items): Quote
    {
        return DB::transaction(function () use ($data, $items) {
            $organization = isset($data['organization_id']) ? Organization::find($data['organization_id']) : null;
            $issuedAt = $data['issued_at'] instanceof Carbon
                ? $data['issued_at']
                : Carbon::parse($data['issued_at'] ?? now());

            $items = $this->applyTaxRates($organization, $items);
            $calculated = $this->calculator->calculate($items, [
                'adjustment_total' => $data['adjustment_total'] ?? 0,
            ]);

            $quote = Quote::create([
                'quote_number' => $data['quote_number'] ?? null,
                'organization_id' => $data['organization_id'] ?? null,
                'work_order_id' => $data['work_order_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'quote_type' => $data['quote_type'] ?? 'standard',
                'version' => $data['version'] ?? 1,
                'terms' => $data['terms'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'sent_at' => $data['sent_at'] ?? null,
                'approved_at' => $data['approved_at'] ?? null,
                'rejected_at' => $data['rejected_at'] ?? null,
                'rejection_reason' => $data['rejection_reason'] ?? null,
                'signature_name' => $data['signature_name'] ?? null,
                'signature_data' => $data['signature_data'] ?? null,
                'signature_ip' => $data['signature_ip'] ?? null,
                'revision_of_quote_id' => $data['revision_of_quote_id'] ?? null,
                'currency' => $data['currency'] ?? ($organization?->billing_currency ?? config('billing.default_currency', 'USD')),
                'subtotal' => $calculated['totals']['subtotal'],
                'tax' => $calculated['totals']['tax_total'],
                'total' => $calculated['totals']['total'],
                'labor_subtotal' => $calculated['totals']['labor_subtotal'],
                'parts_subtotal' => $calculated['totals']['parts_subtotal'],
                'fees_subtotal' => $calculated['totals']['fees_subtotal'],
                'discount_total' => $calculated['totals']['discount_total'],
                'tax_total' => $calculated['totals']['tax_total'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($calculated['items'] as $item) {
                QuoteItem::create([
                    'quote_id' => $quote->id,
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

            return $quote->refresh();
        });
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
}
