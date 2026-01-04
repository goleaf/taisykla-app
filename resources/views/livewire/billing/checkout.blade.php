<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-indigo-50 py-12">
    <div class="max-w-2xl mx-auto px-4">
        {{-- Success State --}}
        @if ($paymentComplete)
            <div class="bg-white rounded-3xl shadow-xl p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
                <p class="text-gray-600 mb-6">Thank you for your payment. A receipt has been sent to your email.</p>

                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg text-sm text-gray-600 mb-8">
                    <span>Invoice #{{ $invoice->id }}</span>
                    <span>•</span>
                    <span>${{ number_format($invoice->total, 2) }}</span>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('billing.index') }}"
                        class="px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition"
                        wire:navigate>
                        View All Invoices
                    </a>
                    <a href="{{ route('dashboard') }}"
                        class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition"
                        wire:navigate>
                        Return to Dashboard
                    </a>
                </div>
            </div>
        @else
            {{-- Payment Form --}}
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-8">
                    <h1 class="text-2xl font-bold mb-2">Complete Payment</h1>
                    <p class="text-indigo-100">Invoice #{{ $invoice->id }}</p>
                </div>

                {{-- Invoice Summary --}}
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-500">Billed to</span>
                        <span class="font-medium text-gray-900">{{ $invoice->organization?->name }}</span>
                    </div>

                    @if ($invoice->workOrder)
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-gray-500">Service</span>
                            <span class="text-gray-700">{{ $invoice->workOrder->subject }}</span>
                        </div>
                    @endif

                    @if ($invoice->items && $invoice->items->count() > 0)
                        <div class="border-t border-gray-100 pt-4 mt-4">
                            <p class="text-sm text-gray-500 mb-3">Line Items</p>
                            @foreach ($invoice->items as $item)
                                <div class="flex justify-between text-sm py-1">
                                    <span class="text-gray-700">{{ $item->description }}</span>
                                    <span class="text-gray-900">${{ number_format($item->total, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="border-t border-gray-100 pt-4 mt-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-gray-700">${{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-500">Tax</span>
                            <span class="text-gray-700">${{ number_format($invoice->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-gray-900">Total Due</span>
                            <span class="text-indigo-600">${{ number_format($invoice->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Section --}}
                <div class="p-6">
                    @if ($errorMessage)
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-700">{{ $errorMessage }}</p>
                        </div>
                    @endif

                    @if (!$clientSecret)
                        {{-- Initialize Payment Button --}}
                        <button wire:click="initializePayment" wire:loading.attr="disabled"
                            class="w-full py-4 bg-indigo-600 text-white text-lg font-semibold rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition flex items-center justify-center gap-2">
                            <wire:loading wire:target="initializePayment">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </wire:loading>
                            <wire:loading.remove wire:target="initializePayment">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </wire:loading.remove>
                            Pay ${{ number_format($invoice->total, 2) }}
                        </button>
                    @else
                        {{-- Stripe Elements Placeholder --}}
                        <div x-data="{
                                        stripe: null,
                                        elements: null,
                                        card: null,
                                        loading: false,
                                        error: '',

                                        async init() {
                                            // Load Stripe.js
                                            if (!window.Stripe) {
                                                const script = document.createElement('script');
                                                script.src = 'https://js.stripe.com/v3/';
                                                script.onload = () => this.initStripe();
                                                document.head.appendChild(script);
                                            } else {
                                                this.initStripe();
                                            }
                                        },

                                        initStripe() {
                                            this.stripe = Stripe('{{ config('services.stripe.key') }}');
                                            this.elements = this.stripe.elements({
                                                clientSecret: '{{ $clientSecret }}'
                                            });

                                            const paymentElement = this.elements.create('payment');
                                            paymentElement.mount(this.$refs.paymentElement);
                                        },

                                        async submit() {
                                            if (this.loading) return;
                                            this.loading = true;
                                            this.error = '';

                                            const { error } = await this.stripe.confirmPayment({
                                                elements: this.elements,
                                                confirmParams: {
                                                    return_url: '{{ route('billing.payment-success', ['invoice' => $invoice->id]) }}'
                                                }
                                            });

                                            if (error) {
                                                this.error = error.message;
                                                this.loading = false;
                                            }
                                        }
                                    }">
                            <div x-ref="paymentElement" class="mb-6 p-4 border border-gray-200 rounded-xl min-h-[150px]"></div>

                            <template x-if="error">
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"
                                    x-text="error"></div>
                            </template>

                            <button @click="submit()" :disabled="loading"
                                class="w-full py-4 bg-indigo-600 text-white text-lg font-semibold rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition flex items-center justify-center gap-2">
                                <template x-if="loading">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </template>
                                <span
                                    x-text="loading ? 'Processing...' : 'Pay ${{ number_format($invoice->total, 2) }}'"></span>
                            </button>
                        </div>
                    @endif

                    {{-- Security Notice --}}
                    <div class="mt-6 flex items-center justify-center gap-2 text-xs text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Secured by Stripe • 256-bit SSL encryption
                    </div>
                </div>
            </div>

            {{-- Back Link --}}
            <div class="mt-6 text-center">
                <a href="{{ route('billing.index') }}" class="text-gray-500 hover:text-gray-700" wire:navigate>
                    ← Back to invoices
                </a>
            </div>
        @endif
    </div>
</div>