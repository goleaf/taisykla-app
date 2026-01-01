<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Reports & Analytics</h1>
            <p class="text-sm text-gray-500">Track performance, revenue, and service trends.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Total Revenue</p>
                <p class="text-2xl font-semibold text-gray-900">${{ number_format($totalRevenue, 2) }}</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Revenue This Month</p>
                <p class="text-2xl font-semibold text-gray-900">${{ number_format($monthlyRevenue, 2) }}</p>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                <p class="text-xs text-gray-500 uppercase">Avg. Satisfaction</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($averageRating, 1) }}/5</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Work Orders by Status</h2>
                <div class="space-y-3">
                    @foreach ($statusCounts as $status)
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ ucfirst(str_replace('_', ' ', $status->status)) }}</span>
                            <span class="font-medium text-gray-900">{{ $status->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Work Categories</h2>
                <div class="space-y-3">
                    @foreach ($categoryCounts as $category)
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ $category->name }}</span>
                            <span class="font-medium text-gray-900">{{ $category->work_orders_count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
