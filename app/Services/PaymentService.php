<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Organization;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private ?object $stripe = null;

    public function __construct()
    {
        if (config('services.stripe.secret')) {
            // Lazy load Stripe SDK
            if (class_exists(\Stripe\Stripe::class)) {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $this->stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            }
        }
    }

    /**
     * Create a payment intent for an invoice
     */
    public function createPaymentIntent(Invoice $invoice): array
    {
        $this->ensureStripeConfigured();

        try {
            $customer = $this->getOrCreateStripeCustomer($invoice->organization);

            $intent = $this->stripe->paymentIntents->create([
                'amount' => (int) ($invoice->total * 100), // Amount in cents
                'currency' => config('services.stripe.currency', 'usd'),
                'customer' => $customer->id,
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'organization_id' => $invoice->organization_id,
                ],
                'description' => "Invoice #{$invoice->id} - {$invoice->organization?->name}",
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return [
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
                'amount' => $invoice->total,
            ];
        } catch (Exception $e) {
            Log::error('Stripe payment intent creation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Unable to initialize payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful payment (webhook or redirect)
     */
    public function handlePaymentSuccess(string $paymentIntentId): ?Payment
    {
        $this->ensureStripeConfigured();

        try {
            $intent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            if ($intent->status !== 'succeeded') {
                return null;
            }

            $invoiceId = $intent->metadata['invoice_id'] ?? null;
            if (!$invoiceId) {
                Log::warning('Payment intent missing invoice_id metadata', ['id' => $paymentIntentId]);
                return null;
            }

            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                return null;
            }

            // Check if payment already recorded
            $existingPayment = Payment::where('reference', $paymentIntentId)->first();
            if ($existingPayment) {
                return $existingPayment;
            }

            // Record payment
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $intent->amount_received / 100,
                'method' => 'card',
                'reference' => $paymentIntentId,
                'paid_at' => now(),
            ]);

            // Update invoice status
            $totalPaid = $invoice->payments()->sum('amount');
            if ($totalPaid >= $invoice->total) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            return $payment;
        } catch (Exception $e) {
            Log::error('Payment confirmation failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a payment link for email/SMS
     */
    public function createPaymentLink(Invoice $invoice): string
    {
        $this->ensureStripeConfigured();

        try {
            // Create a product for this invoice
            $product = $this->stripe->products->create([
                'name' => "Invoice #{$invoice->id}",
                'description' => "Service invoice for {$invoice->organization?->name}",
            ]);

            // Create a price
            $price = $this->stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => (int) ($invoice->total * 100),
                'currency' => config('services.stripe.currency', 'usd'),
            ]);

            // Create payment link
            $link = $this->stripe->paymentLinks->create([
                'line_items' => [
                    ['price' => $price->id, 'quantity' => 1],
                ],
                'after_completion' => [
                    'type' => 'redirect',
                    'redirect' => ['url' => route('billing.payment-success', ['invoice' => $invoice->id])],
                ],
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'organization_id' => $invoice->organization_id,
                ],
            ]);

            return $link->url;
        } catch (Exception $e) {
            Log::error('Payment link creation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to create payment link: ' . $e->getMessage());
        }
    }

    /**
     * Create or retrieve Stripe customer for organization
     */
    private function getOrCreateStripeCustomer(Organization $organization): object
    {
        // Check if customer already exists
        if ($organization->stripe_customer_id) {
            try {
                return $this->stripe->customers->retrieve($organization->stripe_customer_id);
            } catch (Exception $e) {
                // Customer may have been deleted, create new one
            }
        }

        // Create new customer
        $customer = $this->stripe->customers->create([
            'name' => $organization->name,
            'email' => $organization->billing_email ?? $organization->primary_contact_email,
            'phone' => $organization->primary_contact_phone,
            'address' => [
                'line1' => $organization->billing_address ?? '',
            ],
            'metadata' => [
                'organization_id' => $organization->id,
            ],
        ]);

        // Save Stripe customer ID
        $organization->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Set up subscription billing for service agreement
     */
    public function createSubscription(Organization $organization, string $priceId): array
    {
        $this->ensureStripeConfigured();

        try {
            $customer = $this->getOrCreateStripeCustomer($organization);

            $subscription = $this->stripe->subscriptions->create([
                'customer' => $customer->id,
                'items' => [['price' => $priceId]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'organization_id' => $organization->id,
                ],
            ]);

            return [
                'subscription_id' => $subscription->id,
                'client_secret' => $subscription->latest_invoice->payment_intent->client_secret,
                'status' => $subscription->status,
            ];
        } catch (Exception $e) {
            Log::error('Subscription creation failed', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to create subscription: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): bool
    {
        $this->ensureStripeConfigured();

        try {
            $this->stripe->subscriptions->cancel($subscriptionId);
            return true;
        } catch (Exception $e) {
            Log::error('Subscription cancellation failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get customer's payment methods
     */
    public function getPaymentMethods(Organization $organization): array
    {
        if (!$organization->stripe_customer_id) {
            return [];
        }

        $this->ensureStripeConfigured();

        try {
            $methods = $this->stripe->paymentMethods->all([
                'customer' => $organization->stripe_customer_id,
                'type' => 'card',
            ]);

            return collect($methods->data)->map(fn($method) => [
                'id' => $method->id,
                'brand' => $method->card->brand,
                'last4' => $method->card->last4,
                'exp_month' => $method->card->exp_month,
                'exp_year' => $method->card->exp_year,
            ])->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Process refund
     */
    public function refund(Payment $payment, ?float $amount = null): bool
    {
        $this->ensureStripeConfigured();

        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $payment->reference,
                'amount' => $amount ? (int) ($amount * 100) : null,
            ]);

            if ($refund->status === 'succeeded') {
                $payment->update([
                    'refunded_at' => now(),
                    'refund_amount' => ($refund->amount / 100),
                ]);
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error('Refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function ensureStripeConfigured(): void
    {
        if (!$this->stripe) {
            throw new Exception('Stripe is not configured. Please set STRIPE_SECRET in your environment.');
        }
    }
}
