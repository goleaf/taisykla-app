<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Services\PaymentService;
use App\Support\PermissionCatalog;
use Livewire\Component;

class Checkout extends Component
{
    public Invoice $invoice;
    public ?string $clientSecret = null;
    public ?string $paymentIntentId = null;
    public string $errorMessage = '';
    public bool $processing = false;
    public bool $paymentComplete = false;

    public function mount(Invoice $invoice): void
    {
        $user = auth()->user();
        abort_unless($user?->can(PermissionCatalog::BILLING_VIEW), 403);

        // Verify access to this invoice
        $canAccess = $user->can(PermissionCatalog::BILLING_VIEW_ALL)
            || ($user->can(PermissionCatalog::BILLING_VIEW_ORG) && $invoice->organization_id === $user->organization_id)
            || ($user->can(PermissionCatalog::BILLING_VIEW_OWN) && $invoice->organization_id === $user->organization_id);

        abort_unless($canAccess, 403);

        // Can't pay already paid invoices
        if ($invoice->status === 'paid') {
            $this->paymentComplete = true;
            return;
        }

        $this->invoice = $invoice->load(['organization', 'items', 'workOrder']);
    }

    public function initializePayment(): void
    {
        $this->errorMessage = '';
        $this->processing = true;

        try {
            $paymentService = app(PaymentService::class);
            $result = $paymentService->createPaymentIntent($this->invoice);

            $this->clientSecret = $result['client_secret'];
            $this->paymentIntentId = $result['payment_intent_id'];
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function confirmPayment(): void
    {
        if (!$this->paymentIntentId) {
            return;
        }

        $this->processing = true;
        $this->errorMessage = '';

        try {
            $paymentService = app(PaymentService::class);
            $payment = $paymentService->handlePaymentSuccess($this->paymentIntentId);

            if ($payment) {
                $this->paymentComplete = true;
                $this->invoice->refresh();
            } else {
                $this->errorMessage = 'Payment could not be confirmed. Please try again.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.billing.checkout');
    }
}
