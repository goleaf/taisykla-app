<?php

namespace App\Services\Billing;

class BillingCalculator
{
    public function calculate(array $items, array $options = []): array
    {
        $totals = [
            'labor_subtotal' => 0.0,
            'parts_subtotal' => 0.0,
            'fees_subtotal' => 0.0,
            'discount_total' => 0.0,
            'tax_total' => 0.0,
            'subtotal' => 0.0,
            'total' => 0.0,
        ];

        $normalizedItems = [];

        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? ($item['unit_cost'] ?? 0));
            $discount = (float) ($item['discount_amount'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $isTaxable = (bool) ($item['is_taxable'] ?? true);

            $lineSubtotal = round($quantity * $unitPrice, 2);
            $discount = min($discount, $lineSubtotal);
            $taxableAmount = max($lineSubtotal - $discount, 0);
            $taxAmount = $isTaxable ? round($taxableAmount * $taxRate, 2) : 0.0;
            $lineTotal = round($taxableAmount + $taxAmount, 2);

            $itemType = $item['item_type'] ?? 'service';

            $normalizedItems[] = array_merge($item, [
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'line_subtotal' => $lineSubtotal,
                'discount_amount' => round($discount, 2),
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
            ]);

            $totals['subtotal'] += $lineSubtotal;
            $totals['discount_total'] += $discount;
            $totals['tax_total'] += $taxAmount;

            if ($itemType === 'labor' || $itemType === 'service') {
                $totals['labor_subtotal'] += $lineSubtotal;
            } elseif ($itemType === 'part') {
                $totals['parts_subtotal'] += $lineSubtotal;
            } else {
                $totals['fees_subtotal'] += $lineSubtotal;
            }
        }

        $adjustment = (float) ($options['adjustment_total'] ?? 0);
        $totals['total'] = round($totals['subtotal'] - $totals['discount_total'] + $totals['tax_total'] + $adjustment, 2);

        return [
            'items' => $normalizedItems,
            'totals' => array_map(fn ($value) => round($value, 2), $totals),
        ];
    }
}
