<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('equipment.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800" wire:navigate>← Back to Equipment</a>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $equipment->name }}</h1>
                <p class="text-sm text-gray-500">{{ $equipment->type }} • {{ $equipment->category?->name ?? 'Uncategorized' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span @class([
                    'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
                    'bg-green-100 text-green-700' => $equipment->status === 'operational',
                    'bg-yellow-100 text-yellow-700' => $equipment->status === 'needs_attention',
                    'bg-orange-100 text-orange-700' => $equipment->status === 'in_repair',
                    'bg-gray-100 text-gray-700' => in_array($equipment->status, ['retired', 'decommissioned']),
                ])>
                    {{ ucfirst(str_replace('_', ' ', $equipment->status)) }}
                </span>
                @if($equipment->health_score !== null)
                    <span @class([
                        'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
                        'bg-green-100 text-green-700' => $equipment->health_score >= 80,
                        'bg-blue-100 text-blue-700' => $equipment->health_score >= 60 && $equipment->health_score < 80,
                        'bg-yellow-100 text-yellow-700' => $equipment->health_score >= 40 && $equipment->health_score < 60,
                        'bg-red-100 text-red-700' => $equipment->health_score < 40,
                    ])>
                        Health: {{ $equipment->health_score }}%
                    </span>
                @endif
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-8 -mb-px overflow-x-auto" aria-label="Tabs">
                @foreach($tabs as $key => $label)
                    <button
                        wire:click="setTab('{{ $key }}')"
                        @class([
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            'border-indigo-500 text-indigo-600' => $activeTab === $key,
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeTab !== $key,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="min-h-[400px]">
            {{-- Overview Tab --}}
            @if($activeTab === 'overview')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Quick Stats --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h2>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-2xl font-bold text-gray-900">{{ $quickStats['age_years'] ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">Years Old</p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-2xl font-bold text-gray-900">${{ $quickStats['total_service_cost'] }}</p>
                                    <p class="text-xs text-gray-500">Total Service Cost</p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-2xl font-bold text-gray-900">{{ $quickStats['service_count'] }}</p>
                                    <p class="text-xs text-gray-500">Service Events</p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-2xl font-bold text-gray-900">{{ $quickStats['downtime_days'] }}</p>
                                    <p class="text-xs text-gray-500">Downtime Days</p>
                                </div>
                            </div>
                        </div>

                        {{-- Health & Alerts --}}
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Health & Alerts</h2>
                                <button wire:click="recalculateHealthScore" class="text-xs text-indigo-600 hover:text-indigo-800">
                                    Recalculate
                                </button>
                            </div>
                            <div class="flex items-center gap-6 mb-4">
                                <div @class([
                                    'h-20 w-20 rounded-full flex items-center justify-center text-2xl font-bold',
                                    'bg-green-100 text-green-700' => ($health['score'] ?? 0) >= 80,
                                    'bg-blue-100 text-blue-700' => ($health['score'] ?? 0) >= 60 && ($health['score'] ?? 0) < 80,
                                    'bg-yellow-100 text-yellow-700' => ($health['score'] ?? 0) >= 40 && ($health['score'] ?? 0) < 60,
                                    'bg-red-100 text-red-700' => ($health['score'] ?? 0) < 40,
                                ])>
                                    {{ $health['score'] ?? '—' }}
                                </div>
                                <div>
                                    <p class="text-lg font-medium text-gray-900">{{ $health['label'] ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">Grade: {{ $health['grade'] ?? '—' }}</p>
                                </div>
                            </div>
                            @if(!empty($health['alerts']))
                                <div class="space-y-2">
                                    @foreach($health['alerts'] as $alert)
                                        <div @class([
                                            'p-3 rounded-lg text-sm',
                                            'bg-red-50 text-red-700' => $alert['type'] === 'critical',
                                            'bg-yellow-50 text-yellow-700' => $alert['type'] === 'warning',
                                            'bg-blue-50 text-blue-700' => $alert['type'] === 'info',
                                        ])>
                                            <strong>{{ $alert['title'] }}</strong>
                                            <p class="text-xs mt-1">{{ $alert['message'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Recent Service History --}}
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Service History</h2>
                            <div class="space-y-3">
                                @forelse ($maintenanceHistory ?? collect() as $order)
                                    <div class="border-b border-gray-100 pb-3 last:border-b-0">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">#{{ $order->id }} {{ $order->subject }}</p>
                                                <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-gray-500">{{ $order->completed_at?->format('M d, Y') ?? $order->created_at?->format('M d, Y') }}</p>
                                                <a class="text-xs text-indigo-600 hover:text-indigo-800" href="{{ route('work-orders.show', $order) }}" wire:navigate>View</a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No service history yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="space-y-6">
                        {{-- QR Code --}}
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 text-center">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">QR Code</h2>
                            @if($qrCode['qr_code'] ?? null)
                                <img src="{{ $qrCode['qr_url'] }}" alt="QR Code" class="mx-auto mb-3 w-32 h-32">
                                <p class="text-xs text-gray-500 font-mono">{{ $qrCode['qr_code'] }}</p>
                                <button onclick="window.print()" class="mt-3 text-xs text-indigo-600 hover:text-indigo-800">
                                    Print Label
                                </button>
                            @else
                                <button wire:click="generateQrCode" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    Generate QR Code
                                </button>
                            @endif
                        </div>

                        {{-- Photos --}}
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Photos</h2>
                            @if ($equipment->attachments->where('kind', 'photo')->isEmpty())
                                <p class="text-sm text-gray-500">No photos uploaded.</p>
                            @else
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ($equipment->attachments->where('kind', 'photo') as $attachment)
                                        <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank">
                                            <img class="h-24 w-full rounded-md object-cover border border-gray-200" src="{{ asset('storage/'.$attachment->file_path) }}" alt="{{ $attachment->label ?? 'Photo' }}">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Warranty Status --}}
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Warranty Status</h2>
                            <div @class([
                                'p-3 rounded-lg text-sm',
                                'bg-green-50 text-green-700' => $equipment->has_active_warranty,
                                'bg-gray-50 text-gray-500' => !$equipment->has_active_warranty,
                            ])>
                                @if($equipment->has_active_warranty)
                                    <p class="font-medium">Active Warranty</p>
                                    <p class="text-xs mt-1">{{ $equipment->active_warranty?->days_remaining }} days remaining</p>
                                @else
                                    <p class="font-medium">No Active Warranty</p>
                                    <p class="text-xs mt-1">Equipment is not covered</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Specifications Tab --}}
            @if($activeTab === 'specifications')
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Technical Specifications</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach(['basic' => 'Basic Information', 'physical' => 'Physical', 'network' => 'Network', 'location' => 'Location'] as $key => $title)
                            @if(!empty($specifications[$key] ?? []))
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900 mb-3">{{ $title }}</h3>
                                    <dl class="space-y-2">
                                        @foreach($specifications[$key] as $label => $value)
                                            @if($value)
                                                <div class="flex justify-between text-sm">
                                                    <dt class="text-gray-500">{{ $label }}</dt>
                                                    <dd class="text-gray-900 font-medium">{{ $value }}</dd>
                                                </div>
                                            @endif
                                        @endforeach
                                    </dl>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @if(!empty($specifications['custom'] ?? []))
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Custom Specifications</h3>
                            <pre class="bg-gray-50 p-4 rounded-lg text-sm overflow-x-auto">{{ json_encode($specifications['custom'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Warranty Tab --}}
            @if($activeTab === 'warranty')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Warranty Coverage</h2>
                            @forelse ($warranty['warranties'] ?? collect() as $w)
                                <div class="border-b border-gray-100 pb-4 mb-4 last:border-b-0 last:mb-0 last:pb-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $w->provider_name }}</p>
                                            <p class="text-sm text-gray-500">{{ \App\Models\Warranty::coverageOptions()[$w->coverage_type] ?? $w->coverage_type }}</p>
                                        </div>
                                        <span @class([
                                            'px-2 py-1 text-xs rounded-full',
                                            'bg-green-100 text-green-700' => $w->is_active,
                                            'bg-gray-100 text-gray-500' => !$w->is_active,
                                        ])>
                                            {{ $w->is_active ? 'Active' : 'Expired' }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <p>{{ $w->starts_at?->format('M d, Y') }} - {{ $w->ends_at?->format('M d, Y') }}</p>
                                        @if($w->is_active && $w->days_remaining)
                                            <p class="text-green-600">{{ $w->days_remaining }} days remaining</p>
                                        @endif
                                    </div>
                                    @if($w->coverage_details)
                                        <p class="mt-2 text-sm text-gray-700">{{ $w->coverage_details }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No warranty records available.</p>
                            @endforelse
                        </div>

                        {{-- Claims --}}
                        @if(($warranty['claims'] ?? collect())->isNotEmpty())
                            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">Warranty Claims</h2>
                                <div class="space-y-3">
                                    @foreach($warranty['claims'] as $claim)
                                        <div class="p-3 bg-gray-50 rounded-lg text-sm">
                                            <div class="flex justify-between">
                                                <span class="font-medium">Claim #{{ $claim->id }}</span>
                                                <span class="text-gray-500">{{ $claim->submitted_at?->format('M d, Y') }}</span>
                                            </div>
                                            <p class="text-gray-500 mt-1">{{ $claim->details }}</p>
                                            <span @class([
                                                'mt-2 inline-block px-2 py-0.5 text-xs rounded',
                                                'bg-yellow-100 text-yellow-700' => $claim->status === 'submitted',
                                                'bg-green-100 text-green-700' => $claim->status === 'approved',
                                                'bg-red-100 text-red-700' => $claim->status === 'denied',
                                            ])>
                                                {{ ucfirst($claim->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Timeline --}}
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Warranty Timeline</h2>
                        <div class="relative">
                            @foreach($warrantyTimeline ?? [] as $event)
                                <div class="flex gap-4 mb-4 last:mb-0">
                                    <div @class([
                                        'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-xs',
                                        'bg-green-500' => $event['color'] === 'success',
                                        'bg-yellow-500' => $event['color'] === 'warning',
                                        'bg-red-500' => $event['color'] === 'error',
                                        'bg-blue-500' => $event['color'] === 'info',
                                        'opacity-50' => $event['is_future'] ?? false,
                                    ])>
                                        <x-icon :name="$event['icon']" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 @if($event['is_future'] ?? false) opacity-50 @endif">{{ $event['event'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $event['date']->format('M d, Y') }}</p>
                                        @if($event['provider'] ?? null)
                                            <p class="text-xs text-gray-400">{{ $event['provider'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Service History Tab --}}
            @if($activeTab === 'service')
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Service History</h2>
                        <div class="flex gap-3">
                            <select wire:model.live="serviceFilter" class="text-sm border-gray-300 rounded-lg">
                                <option value="">All Types</option>
                                @foreach($serviceTypes ?? [] as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="date" wire:model.live="serviceDateFrom" class="text-sm border-gray-300 rounded-lg" placeholder="From">
                            <input type="date" wire:model.live="serviceDateTo" class="text-sm border-gray-300 rounded-lg" placeholder="To">
                        </div>
                    </div>
                    <div class="space-y-4">
                        @forelse ($serviceHistory ?? collect() as $event)
                            <div class="border border-gray-100 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span @class([
                                            'px-2 py-1 text-xs rounded-full font-medium',
                                            'bg-orange-100 text-orange-700' => $event->event_type === 'repair',
                                            'bg-blue-100 text-blue-700' => $event->event_type === 'maintenance',
                                            'bg-purple-100 text-purple-700' => $event->event_type === 'inspection',
                                            'bg-green-100 text-green-700' => $event->event_type === 'upgrade',
                                            'bg-gray-100 text-gray-700' => $event->event_type === 'installation',
                                        ])>
                                            {{ ucfirst($event->event_type) }}
                                        </span>
                                        <span class="text-sm text-gray-500">{{ $event->completed_at?->format('M d, Y') }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format($event->total_cost, 2) }}</span>
                                </div>
                                @if($event->problem_description)
                                    <p class="text-sm text-gray-700 mb-2"><strong>Problem:</strong> {{ $event->problem_description }}</p>
                                @endif
                                @if($event->resolution_description)
                                    <p class="text-sm text-gray-700 mb-2"><strong>Resolution:</strong> {{ $event->resolution_description }}</p>
                                @endif
                                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                                    @if($event->technician)
                                        <span>Technician: {{ $event->technician->name }}</span>
                                    @endif
                                    @if($event->duration_minutes)
                                        <span>Duration: {{ $event->duration_minutes }} min</span>
                                    @endif
                                    @if($event->parts_replaced)
                                        <span>Parts replaced: {{ count($event->parts_replaced) }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-8">No service events found.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Documents Tab --}}
            @if($activeTab === 'documents')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Documents</h2>
                            @forelse($documents ?? [] as $type => $docs)
                                <div class="mb-6 last:mb-0">
                                    <h3 class="text-sm font-medium text-gray-700 mb-3">{{ \App\Models\EquipmentDocument::typeOptions()[$type] ?? ucfirst($type) }}</h3>
                                    <div class="space-y-2">
                                        @foreach($docs as $doc)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $doc->title }}</p>
                                                    <p class="text-xs text-gray-500">{{ $doc->file_name }} • {{ number_format($doc->file_size / 1024, 1) }} KB</p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800">Download</a>
                                                    <button wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Delete this document?" class="text-xs text-red-600 hover:text-red-800">Delete</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No documents uploaded yet.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Upload Form --}}
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Upload Document</h2>
                        <form wire:submit="uploadDocument" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">File</label>
                                <input type="file" wire:model="documentFile" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('documentFile') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" wire:model="documentTitle" class="w-full text-sm border-gray-300 rounded-lg">
                                @error('documentTitle') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select wire:model="documentType" class="w-full text-sm border-gray-300 rounded-lg">
                                    @foreach($documentTypes ?? [] as $type => $label)
                                        <option value="{{ $type }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <textarea wire:model="documentNotes" rows="2" class="w-full text-sm border-gray-300 rounded-lg"></textarea>
                            </div>
                            <button type="submit" class="w-full py-2 px-4 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                                Upload Document
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Metrics Tab --}}
            @if($activeTab === 'metrics')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- TCO --}}
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Total Cost of Ownership</h2>
                        <div class="text-center mb-6">
                            <p class="text-4xl font-bold text-gray-900">${{ number_format($metrics['tco']['total'] ?? 0, 2) }}</p>
                            <p class="text-sm text-gray-500">Lifetime cost</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xl font-bold text-gray-900">${{ number_format($metrics['tco']['purchase'] ?? 0, 2) }}</p>
                                <p class="text-xs text-gray-500">Purchase Price</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xl font-bold text-gray-900">${{ number_format($metrics['tco']['service'] ?? 0, 2) }}</p>
                                <p class="text-xs text-gray-500">Service Costs</p>
                            </div>
                        </div>
                    </div>

                    {{-- Reliability Metrics --}}
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Reliability Metrics</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-2xl font-bold text-blue-700">{{ $metrics['mtbf_days'] ?? '—' }}</p>
                                <p class="text-xs text-blue-600">MTBF (Days)</p>
                            </div>
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <p class="text-2xl font-bold text-purple-700">{{ $metrics['avg_repair_minutes'] ?? '—' }}</p>
                                <p class="text-xs text-purple-600">Avg Repair (Min)</p>
                            </div>
                            <div class="text-center p-4 bg-orange-50 rounded-lg">
                                <p class="text-2xl font-bold text-orange-700">{{ $metrics['failure_count'] ?? 0 }}</p>
                                <p class="text-xs text-orange-600">Total Failures</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-700">{{ $metrics['lifecycle']['lifecycle_percentage'] ?? '—' }}%</p>
                                <p class="text-xs text-green-600">Lifecycle Used</p>
                            </div>
                        </div>
                    </div>

                    {{-- Lifecycle Timeline --}}
                    <div class="lg:col-span-2 bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Lifecycle Timeline</h2>
                        <div class="flex items-center gap-4 overflow-x-auto pb-4">
                            @foreach($metrics['timeline'] ?? [] as $event)
                                <div class="flex-shrink-0 text-center">
                                    <div @class([
                                        'w-12 h-12 rounded-full flex items-center justify-center text-white mx-auto mb-2',
                                        'bg-green-500' => $event['color'] === 'success',
                                        'bg-yellow-500' => $event['color'] === 'warning',
                                        'bg-red-500' => $event['color'] === 'error',
                                        'bg-blue-500' => $event['color'] === 'info',
                                        'opacity-50' => $event['is_future'] ?? false,
                                    ])>
                                        <x-icon :name="$event['icon']" class="w-5 h-5" />
                                    </div>
                                    <p class="text-xs font-medium text-gray-900 @if($event['is_future'] ?? false) opacity-50 @endif">{{ $event['event'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $event['date']->format('M Y') }}</p>
                                </div>
                                @if(!$loop->last)
                                    <div class="flex-shrink-0 h-0.5 w-8 bg-gray-200"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Cost by Year --}}
                    @if(!empty($metrics['cost_by_year']))
                        <div class="lg:col-span-2 bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Cost Trend</h2>
                            <div class="flex items-end gap-4 h-40">
                                @php $maxCost = max($metrics['cost_by_year']); @endphp
                                @foreach($metrics['cost_by_year'] as $year => $cost)
                                    <div class="flex-1 flex flex-col items-center">
                                        <div class="w-full bg-indigo-500 rounded-t" style="height: {{ $maxCost > 0 ? ($cost / $maxCost) * 100 : 0 }}%"></div>
                                        <p class="text-xs text-gray-500 mt-2">{{ $year }}</p>
                                        <p class="text-xs font-medium text-gray-700">${{ number_format($cost, 0) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
