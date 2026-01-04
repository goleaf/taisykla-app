<?php

namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentApplication;
use Illuminate\Support\Facades\DB;

class PaymentAllocationService
{
    public function applyToInvoice(Payment $payment, Invoice $invoice, ?float $amount = null): PaymentApplication
    {
        return DB::transaction(function () use ($payment, $invoice, $amount) {
            $remainingPayment = (float) $payment->amount - (float) $payment->applied_amount;
            $balanceDue = (float) ($invoice->balance_due ?? $invoice->total);
            $applyAmount = $amount !== null ? min($amount, $remainingPayment) : min($remainingPayment, $balanceDue);

            $applyAmount = max($applyAmount, 0);

            $application = PaymentApplication::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'applied_amount' => $applyAmount,
                'applied_at' => now(),
            ]);

            $payment->update([
                'applied_amount' => round($payment->applied_amount + $applyAmount, 2),
                'overpayment_amount' => round(max(0, $remainingPayment - $applyAmount), 2),
            ]);

            $newBalance = round($balanceDue - $applyAmount, 2);
            $invoiceUpdates = [
                'balance_due' => max($newBalance, 0),
            ];

            if ($newBalance <= 0) {
                $invoiceUpdates['status'] = 'paid';
                $invoiceUpdates['paid_at'] = $invoice->paid_at ?? now();
            } elseif ($applyAmount > 0 && $invoice->status === 'sent') {
                $invoiceUpdates['status'] = 'partial';
            }

            $invoice->update($invoiceUpdates);

            return $application;
        });
    }
}
