<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('equipment.index') }}" class="text-sm text-indigo-600" wire:navigate>← Back to Equipment</a>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $equipment->name }}</h1>
                <p class="text-sm text-gray-500">{{ $equipment->type }} • {{ $equipment->category?->name ?? 'Uncategorized' }}</p>
            </div>
            <span @class([
                'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
                'bg-green-100 text-green-700' => $equipment->status === 'operational',
                'bg-yellow-100 text-yellow-700' => $equipment->status === 'needs_attention',
                'bg-orange-100 text-orange-700' => $equipment->status === 'in_repair',
                'bg-gray-100 text-gray-700' => $equipment->status === 'retired',
            ])>
                {{ ucfirst(str_replace('_', ' ', $equipment->status)) }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Equipment Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <p class="text-xs text-gray-500">Manufacturer</p>
                            <p>{{ $equipment->manufacturer ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Model</p>
                            <p>{{ $equipment->model ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Serial Number</p>
                            <p>{{ $equipment->serial_number ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Asset Tag</p>
                            <p>{{ $equipment->asset_tag ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Purchased</p>
                            <p>{{ $equipment->purchase_date?->format('M d, Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Assigned To</p>
                            <p>{{ $equipment->assignedUser?->name ?? '—' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs text-gray-500">Location</p>
                            <p>{{ $equipment->location_name ?? '—' }} @if ($equipment->location_address) • {{ $equipment->location_address }} @endif</p>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-gray-700">
                        <p class="text-xs text-gray-500">Notes / Technical Specifications</p>
                        <p>{{ $equipment->notes ?? 'No specifications recorded.' }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Warranty Information</h2>
                    @forelse ($equipment->warranties as $warranty)
                        <div class="border-b border-gray-100 pb-4 mb-4 last:border-b-0 last:mb-0 last:pb-0">
                            <div class="flex flex-wrap items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $warranty->provider_name ?? 'Warranty Provider' }}</p>
                                    <p class="text-xs text-gray-500">{{ $warranty->coverage_type ?? 'Coverage' }}</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    {{ $warranty->starts_at?->format('M d, Y') ?? '—' }} - {{ $warranty->ends_at?->format('M d, Y') ?? '—' }}
                                </p>
                            </div>
                            @if ($warranty->coverage_details)
                                <p class="text-sm text-gray-700 mt-2">{{ $warranty->coverage_details }}</p>
                            @endif
                            @if ($warranty->claim_instructions)
                                <p class="text-xs text-gray-500 mt-2">Claim instructions: {{ $warranty->claim_instructions }}</p>
                            @endif
                            @if ($warranty->claims->isNotEmpty())
                                <div class="mt-2 text-xs text-gray-500">
                                    Claims: {{ $warranty->claims->count() }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No warranty records available.</p>
                    @endforelse
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Maintenance History</h2>
                    <div class="space-y-4">
                        @forelse ($maintenanceHistory as $order)
                            <div class="border-b border-gray-100 pb-3 last:border-b-0">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">#{{ $order->id }} {{ $order->subject }}</p>
                                        <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $order->status)) }} • {{ $order->assignedTo?->name ?? 'Unassigned' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">
                                            {{ $order->completed_at?->format('M d, Y') ?? $order->created_at?->format('M d, Y') }}
                                        </p>
                                        <a class="text-xs text-indigo-600" href="{{ route('work-orders.show', $order) }}" wire:navigate>View work order</a>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Parts replaced: {{ $order->parts->count() }} • Cost: {{ $order->total_cost ? '$'.number_format($order->total_cost, 2) : '—' }}
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No service history yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Health & Lifecycle</h2>
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 rounded-full bg-indigo-50 flex items-center justify-center text-xl font-semibold text-indigo-700">
                            {{ $health['score'] }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $health['label'] }}</p>
                            <p class="text-xs text-gray-500">{{ $health['summary'] }}</p>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-gray-500 space-y-1">
                        <p>Age: {{ $health['age_years'] !== null ? $health['age_years'].' years' : '—' }}</p>
                        <p>Service visits: {{ $health['service_count'] }}</p>
                        <p>Last serviced: {{ $lastService?->format('M d, Y') ?? '—' }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Photos & Attachments</h2>
                    @if ($equipment->attachments->isEmpty())
                        <p class="text-sm text-gray-500">No attachments uploaded.</p>
                    @else
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($equipment->attachments as $attachment)
                                <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" rel="noreferrer">
                                    <img class="h-24 w-full rounded-md object-cover border border-gray-200" src="{{ asset('storage/'.$attachment->file_path) }}" alt="{{ $attachment->label ?? 'Attachment' }}" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
