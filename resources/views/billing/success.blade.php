<x-app-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 text-center">
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

            <div class="flex flex-col gap-3">
                <a href="{{ route('billing.index') }}"
                    class="px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition">
                    View All Invoices
                </a>
                <a href="{{ route('dashboard') }}"
                    class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition">
                    Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>