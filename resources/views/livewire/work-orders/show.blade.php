<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @php
            $statusLabel = ucfirst(str_replace('_', ' ', $workOrder->status));
            $priorityLabel = ucfirst($workOrder->priority);
            $statusBadge = match ($workOrder->status) {
                'submitted' => 'bg-gray-100 text-gray-700',
                'assigned' => 'bg-blue-100 text-blue-700',
                'in_progress' => 'bg-indigo-100 text-indigo-700',
                'on_hold' => 'bg-yellow-100 text-yellow-700',
                'completed' => 'bg-green-100 text-green-700',
                'closed' => 'bg-green-100 text-green-700',
                'canceled' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-700',
            };
            $priorityBadge = match ($workOrder->priority) {
                'urgent' => 'bg-red-100 text-red-700',
                'high' => 'bg-orange-100 text-orange-700',
                default => 'bg-gray-100 text-gray-700',
            };
        @endphp

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('work-orders.index') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800" wire:navigate>
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Work Orders
                </a>
                <h1 class="text-2xl font-semibold text-gray-900">Work Order #{{ $workOrder->id }}</h1>
                <p class="text-sm text-gray-500">{{ $workOrder->subject }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusBadge }}">
                    {{ $statusLabel }}
                </span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $priorityBadge }}">
                    {{ $priorityLabel }}
                </span>
                
                {{-- Actions Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Actions
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 z-50 py-2" x-cloak>
                        @if($canCreate)
                            <button wire:click="cloneWorkOrder" @click="open = false" wire:loading.attr="disabled" class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 disabled:opacity-50">
                                <svg wire:loading.remove wire:target="cloneWorkOrder" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <svg wire:loading wire:target="cloneWorkOrder" class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="cloneWorkOrder">Clone Work Order</span>
                                <span wire:loading wire:target="cloneWorkOrder">Cloning...</span>
                            </button>
                        @endif
                        <button onclick="window.print()" class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print
                        </button>
                        @if($workOrder->status === 'completed' || $workOrder->status === 'closed')
                            <a href="{{ route('billing.index') }}" class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2" wire:navigate>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                View Invoice
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('status'))
            <div class="rounded-lg bg-green-50 p-4 border border-green-200">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm text-green-700">{{ session('status') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 p-4 border border-red-200">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="text-sm text-red-700">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Current Status</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $statusSummary['title'] ?? 'Status update' }}</p>
                    <p class="text-sm text-gray-600">{{ $statusSummary['description'] ?? '' }}</p>
                </div>
                <div class="text-xs text-gray-500">
                    Requested {{ $workOrder->requested_at?->format('M d, H:i') ?? $workOrder->created_at?->format('M d, H:i') ?? '—' }}
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    @foreach ($statusSteps as $step)
                        <span>{{ $step['label'] }}</span>
                    @endforeach
                </div>
                <div class="mt-2 flex items-center">
                    @foreach ($statusSteps as $step)
                        @php
                            $circleColor = match ($step['state']) {
                                'complete' => 'bg-green-500',
                                'current' => 'bg-indigo-600',
                                default => 'bg-gray-300',
                            };
                            $lineColor = match ($step['state']) {
                                'complete' => 'bg-green-400',
                                'current' => 'bg-indigo-300',
                                default => 'bg-gray-200',
                            };
                        @endphp
                        <div class="flex items-center">
                            <div class="h-3 w-3 rounded-full {{ $circleColor }}"></div>
                            @if (! $loop->last)
                                <div class="h-0.5 w-10 md:w-16 {{ $lineColor }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if ($workOrder->status === 'on_hold')
                    <p class="mt-3 text-sm text-yellow-700">On hold: {{ $workOrder->on_hold_reason ?? 'Awaiting next steps.' }}</p>
                @endif
                @if ($workOrder->status === 'canceled')
                    <p class="mt-3 text-sm text-red-600">This request has been canceled.</p>
                @endif
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-700">
                <div>
                    <p class="text-xs text-gray-500">Assigned To</p>
                    <p>{{ $workOrder->assignedTo?->name ?? 'Unassigned' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Scheduled</p>
                    <p>{{ $workOrder->scheduled_start_at?->format('M d, H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Time Window</p>
                    <p>{{ $workOrder->time_window ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Category</p>
                    <p>{{ $workOrder->category?->name ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <p class="text-xs text-gray-500">Organization</p>
                            <p>{{ $workOrder->organization?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Equipment</p>
                            <p>{{ $workOrder->equipment?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Requested By</p>
                            <p>{{ $workOrder->requestedBy?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Priority</p>
                            <p>{{ $priorityLabel }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Location</p>
                            <p>{{ $workOrder->location_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Address</p>
                            <p>{{ $workOrder->location_address ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 text-sm text-gray-700">
                        <p class="text-xs text-gray-500">Description</p>
                        <p>{{ $workOrder->description ?? 'No description provided.' }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Appointments</h2>
                    <div class="space-y-3">
                        @forelse ($workOrder->appointments as $appointment)
                            <div class="text-sm text-gray-700">
                                <p>{{ $appointment->scheduled_start_at?->format('M d, H:i') }} • {{ $appointment->status }}</p>
                                <p class="text-xs text-gray-500">{{ $appointment->assignedTo?->name ?? 'Unassigned' }} {{ $appointment->time_window ? '• '.$appointment->time_window : '' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No appointments scheduled yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Report</h2>
                    @php
                        $report = $workOrder->report;
                        $diagnosticMinutes = $report?->diagnostic_minutes;
                        $repairMinutes = $report?->repair_minutes;
                        $testingMinutes = $report?->testing_minutes;
                        $reportTotal = collect([$diagnosticMinutes, $repairMinutes, $testingMinutes])->filter()->sum();
                    @endphp
                    @if ($report || in_array($workOrder->status, ['completed', 'closed'], true))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Arrived</p>
                                <p>{{ $serviceMetrics['arrived_at']?->format('M d, H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Started</p>
                                <p>{{ $serviceMetrics['started_at']?->format('M d, H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Completed</p>
                                <p>{{ $serviceMetrics['completed_at']?->format('M d, H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Time</p>
                                <p>{{ $serviceMetrics['duration_minutes'] ? $serviceMetrics['duration_minutes'].' minutes' : '—' }}</p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-700">
                            <div>
                                <p class="text-xs text-gray-500">Diagnostic</p>
                                <p>{{ $diagnosticMinutes !== null ? $diagnosticMinutes.' min' : '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Repair</p>
                                <p>{{ $repairMinutes !== null ? $repairMinutes.' min' : '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Testing</p>
                                <p>{{ $testingMinutes !== null ? $testingMinutes.' min' : '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Report Total</p>
                                <p>{{ $reportTotal ? $reportTotal.' min' : '—' }}</p>
                            </div>
                        </div>
                        @if ($report)
                            <div class="mt-4 space-y-3 text-sm text-gray-700">
                                <div>
                                    <p class="text-xs text-gray-500">Diagnosis</p>
                                    <p>{{ $report->diagnosis_summary }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Work Performed</p>
                                    <p>{{ $report->work_performed }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Test Results</p>
                                    <p>{{ $report->test_results ?? 'No test results recorded.' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Recommendations</p>
                                    <p>{{ $report->recommendations ?? 'No recommendations provided.' }}</p>
                                </div>
                            </div>
                        @else
                            <p class="mt-4 text-sm text-gray-500">Service report details will be added by the technician.</p>
                        @endif
                        <div class="mt-4 text-sm text-gray-700">
                            <p class="text-xs text-gray-500">Parts Used</p>
                            <div class="space-y-2 mt-2">
                                @forelse ($workOrder->parts as $part)
                                    <div>
                                        <p>{{ $part->part?->name ?? 'Part' }} • Qty {{ $part->quantity }}</p>
                                        <p class="text-xs text-gray-500">Part #: {{ $part->part?->sku ?? '—' }} • Unit price: ${{ number_format($part->unit_price, 2) }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No parts logged.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-700">
                            <p class="text-xs text-gray-500">Technician Notes</p>
                            <div class="space-y-2 mt-2">
                                @forelse ($workOrder->events->where('type', 'note') as $event)
                                    <div>
                                        <p>{{ $event->note }}</p>
                                        <p class="text-xs text-gray-500">{{ $event->user?->name ?? 'System' }} • {{ $event->created_at?->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No technician notes available.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="mt-6">
                            <p class="text-xs text-gray-500">Photo Documentation</p>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                                @foreach (['before' => 'Before', 'during' => 'During', 'after' => 'After', 'report' => 'Report'] as $kind => $label)
                                    @php
                                        $photos = $photoGroups[$kind] ?? collect();
                                    @endphp
                                    <div class="space-y-2">
                                        <p class="text-sm font-medium text-gray-900">{{ $label }}</p>
                                        @if ($photos->isEmpty())
                                            <p class="text-xs text-gray-500">No photos</p>
                                        @else
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach ($photos as $photo)
                                                    <a href="{{ asset('storage/'.$photo->file_path) }}" target="_blank" rel="noreferrer">
                                                        <img class="h-20 w-full rounded-md object-cover border border-gray-200" src="{{ asset('storage/'.$photo->file_path) }}" alt="{{ $photo->label ?? $label.' photo' }}" loading="lazy" />
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">The service report will appear once the work is completed.</p>
                    @endif

                    @if ($canManageReport)
                        <div class="mt-6 border-t border-gray-100 pt-4">
                            <h3 class="text-sm font-semibold text-gray-900">Technician Report Editor</h3>
                            <form wire:submit.prevent="saveReport" class="mt-3 space-y-3">
                                <div>
                                    <label class="text-xs text-gray-500">Diagnosis Summary</label>
                                    <textarea wire:model="reportForm.diagnosis_summary" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                                    @error('reportForm.diagnosis_summary') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Work Performed</label>
                                    <textarea wire:model="reportForm.work_performed" class="mt-1 w-full rounded-md border-gray-300" rows="3"></textarea>
                                    @error('reportForm.work_performed') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Test Results</label>
                                    <textarea wire:model="reportForm.test_results" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                                    @error('reportForm.test_results') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Recommendations</label>
                                    <textarea wire:model="reportForm.recommendations" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                                    @error('reportForm.recommendations') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Diagnostic Minutes</label>
                                        <input type="number" wire:model="reportForm.diagnostic_minutes" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('reportForm.diagnostic_minutes') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Repair Minutes</label>
                                        <input type="number" wire:model="reportForm.repair_minutes" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('reportForm.repair_minutes') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Testing Minutes</label>
                                        <input type="number" wire:model="reportForm.testing_minutes" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('reportForm.testing_minutes') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="saveReport">Save Report</span>
                                    <span wire:loading wire:target="saveReport">Saving...</span>
                                </button>
                            </form>
                            <form wire:submit.prevent="uploadReportPhotos" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                                <div>
                                    <label class="text-xs text-gray-500">Photo Category</label>
                                    <select wire:model="reportPhotoKind" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="before">Before</option>
                                        <option value="during">During</option>
                                        <option value="after">After</option>
                                        <option value="report">Report</option>
                                    </select>
                                    @error('reportPhotoKind') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs text-gray-500">Upload Photos</label>
                                    <input type="file" wire:model="reportPhotos" multiple accept="image/*" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('reportPhotos') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    @error('reportPhotos.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-3">
                                    <button class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="uploadReportPhotos">Upload Photos</span>
                                        <span wire:loading wire:target="uploadReportPhotos">Uploading...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline & Updates</h2>
                    <div class="space-y-3">
                        @forelse ($timeline as $entry)
                            <div>
                                <p class="text-sm text-gray-700">{{ $entry['summary'] }}</p>
                                <p class="text-xs text-gray-500">{{ $entry['actor'] }} • {{ $entry['timestamp']?->format('M d, Y H:i') ?? '—' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No updates yet.</p>
                        @endforelse
                    </div>

                    @if ($canAddNote)
                        <form wire:submit.prevent="addNote" class="mt-4">
                            <textarea wire:model="note" class="w-full rounded-md border-gray-300" rows="3" placeholder="Add a note"></textarea>
                            @error('note') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <button class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="addNote">Add Note</span>
                                <span wire:loading wire:target="addNote">Adding...</span>
                            </button>
                        </form>
                    @endif
                </div>

                <div id="work-order-messages" class="bg-white shadow-sm rounded-lg p-6 border border-gray-100" wire:poll.10s>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Messages</h2>
                    @if ($messageThread && $messageThread->messages->isNotEmpty())
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            @foreach ($messageThread->messages->sortBy('created_at') as $message)
                                @php
                                    $isMine = $viewer && $message->user_id === $viewer->id;
                                @endphp
                                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-md rounded-lg px-3 py-2 {{ $isMine ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                                        <p class="text-xs {{ $isMine ? 'text-indigo-100' : 'text-gray-500' }}">
                                            {{ $isMine ? 'You' : ($message->user?->name ?? 'User') }}
                                            • {{ $message->created_at?->format('M d, H:i') ?? '—' }}
                                        </p>
                                        <p class="text-sm leading-relaxed">{{ $message->body }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No messages yet. Send a note to your technician or dispatcher.</p>
                    @endif
                    <form wire:submit.prevent="sendMessage" class="mt-4">
                        <textarea wire:model="messageBody" class="w-full rounded-md border-gray-300" rows="2" placeholder="Type a message"></textarea>
                        @error('messageBody') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <button class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="sendMessage">Send Message</span>
                            <span wire:loading wire:target="sendMessage">Sending...</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                @if ($canUpdateStatus || $canMarkArrived)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Actions</h2>
                        <div class="space-y-3">
                            @if ($canUpdateStatus)
                                <select wire:model="status" class="w-full rounded-md border-gray-300">
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption }}">{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                                    @endforeach
                                </select>
                                <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:click="updateStatus" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="updateStatus">Update Status</span>
                                    <span wire:loading wire:target="updateStatus">Updating...</span>
                                </button>
                            @endif
                            @if ($canMarkArrived)
                                <button class="w-full px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 inline-flex items-center justify-center" 
                                        wire:click="markArrived" 
                                        wire:loading.attr="disabled"
                                        wire:target="markArrived"
                                        @disabled($workOrder->arrived_at)>
                                    <span wire:loading wire:target="markArrived" class="mr-2">
                                        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    {{ $workOrder->arrived_at ? 'Arrived Recorded' : 'Mark Arrived' }}
                                </button>
                                <p class="text-xs text-gray-500">
                                    Arrival: {{ $workOrder->arrived_at?->format('M d, H:i') ?? 'Not recorded' }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($canAssign)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Assign Technician</h2>
                        <select wire:model="assignedToUserId" class="w-full rounded-md border-gray-300">
                            <option value="">Unassigned</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                            @endforeach
                        </select>
                        <button class="mt-3 w-full px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 inline-flex items-center justify-center" 
                                wire:click="assignTechnician"
                                wire:loading.attr="disabled"
                                wire:target="assignTechnician">
                            <span wire:loading wire:target="assignTechnician" class="mr-2">
                                <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            Save Assignment
                        </button>
                    </div>
                @endif

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Assigned Technician</h2>
                    @if ($workOrder->assignedTo)
                        @php
                            $nameParts = preg_split('/\s+/', trim($workOrder->assignedTo->name));
                            $initials = '';
                            foreach ($nameParts as $part) {
                                if ($part !== '') {
                                    $initials .= strtoupper(substr($part, 0, 1));
                                }
                            }
                            $initials = substr($initials, 0, 2);
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold">
                                {{ $initials }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $workOrder->assignedTo->name }}</p>
                                <p class="text-xs text-gray-500">Role: {{ $workOrder->assignedTo->job_title ?? 'Technician' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1 text-sm text-gray-700">
                            <p>Phone: {{ $workOrder->assignedTo->phone ?? '—' }}</p>
                            <p>Email: {{ $workOrder->assignedTo->email }}</p>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            <p>Service Specialty: {{ $workOrder->category?->name ?? 'General service' }}</p>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            @if ($technicianInsights['rating'])
                                Rating: {{ number_format($technicianInsights['rating'], 1) }} / 5 ({{ $technicianInsights['rating_count'] }} reviews)
                            @else
                                No ratings yet
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Availability: {{ ucfirst($workOrder->assignedTo->availability_status ?? 'unknown') }}
                        </p>
                        <a href="#work-order-messages" class="mt-3 inline-block text-sm text-indigo-600">Message technician</a>
                    @else
                        <p class="text-sm text-gray-500">No technician assigned yet.</p>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Next Appointment</h2>
                    @if ($nextAppointment)
                        <p class="text-sm text-gray-700">
                            {{ $nextAppointment->scheduled_start_at?->format('M d, H:i') ?? '—' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ ucfirst($nextAppointment->status) }}{{ $nextAppointment->time_window ? ' • '.$nextAppointment->time_window : '' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Estimated duration: {{ $estimatedDuration ? $estimatedDuration.' minutes' : '—' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $nextAppointment->assignedTo?->name ?? 'Unassigned' }}
                        </p>
                        @if ($tracking['eta_minutes'])
                            <p class="mt-2 text-xs text-gray-500">Estimated travel time: {{ $tracking['eta_minutes'] }} minutes</p>
                        @endif
                        @if ($tracking['map_url'])
                            <div class="mt-3 text-xs text-gray-500">
                                <p>Last known technician location: {{ $tracking['technician_coords']['lat'] }}, {{ $tracking['technician_coords']['lng'] }}</p>
                                <p>Service location: {{ $tracking['site_coords']['lat'] }}, {{ $tracking['site_coords']['lng'] }}</p>
                                <a class="text-indigo-600" href="{{ $tracking['map_url'] }}" target="_blank" rel="noreferrer">Open map</a>
                            </div>
                        @else
                            <p class="mt-2 text-xs text-gray-500">Live location tracking not available.</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">No appointment scheduled yet.</p>
                    @endif
                </div>

                @if ($canSignOff || $workOrder->customer_signature_at)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Sign-Off</h2>
                        @if ($workOrder->customer_signature_at)
                            <p class="text-sm text-gray-700">Signed by {{ $workOrder->customer_signature_name ?? 'Customer' }}</p>
                            <p class="text-xs text-gray-500">Signed {{ $workOrder->customer_signature_at?->format('M d, H:i') }}</p>
                            <div class="mt-3 text-xs text-gray-600 space-y-1">
                                <p>Equipment functional: {{ $workOrder->customer_signoff_functional === null ? '—' : ($workOrder->customer_signoff_functional ? 'Yes' : 'No') }}</p>
                                <p>Technician professional: {{ $workOrder->customer_signoff_professional === null ? '—' : ($workOrder->customer_signoff_professional ? 'Yes' : 'No') }}</p>
                                <p>Satisfied overall: {{ $workOrder->customer_signoff_satisfied === null ? '—' : ($workOrder->customer_signoff_satisfied ? 'Yes' : 'No') }}</p>
                                <p>Comments: {{ $workOrder->customer_signoff_comments ?? '—' }}</p>
                            </div>
                        @elseif ($canSignOff)
                            <form wire:submit.prevent="submitSignoff" class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-500">Signature Name</label>
                                    <input wire:model="signatureName" class="mt-1 w-full rounded-md border-gray-300" placeholder="Enter your name" />
                                    @error('signatureName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Is your equipment functioning correctly?</label>
                                    <select wire:model="signoff.functional" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('signoff.functional') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Was the technician professional and courteous?</label>
                                    <select wire:model="signoff.professional" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('signoff.professional') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Are you satisfied with the work performed?</label>
                                    <select wire:model="signoff.satisfied" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('signoff.satisfied') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Comments</label>
                                    <textarea wire:model="signoff.comments" class="mt-1 w-full rounded-md border-gray-300" rows="2" placeholder="Optional comments"></textarea>
                                    @error('signoff.comments') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="submitSignoff">Approve Work</span>
                                    <span wire:loading wire:target="submitSignoff">Processing...</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                @if ($workOrder->feedback)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Feedback</h2>
                        <div class="grid grid-cols-1 gap-2 text-sm text-gray-700">
                            <p>Overall: {{ $workOrder->feedback->rating }}/5</p>
                            <p>Professionalism: {{ $workOrder->feedback->professionalism_rating ?? '—' }}/5</p>
                            <p>Knowledge: {{ $workOrder->feedback->knowledge_rating ?? '—' }}/5</p>
                            <p>Communication: {{ $workOrder->feedback->communication_rating ?? '—' }}/5</p>
                            <p>Timeliness: {{ $workOrder->feedback->timeliness_rating ?? '—' }}/5</p>
                            <p>Quality: {{ $workOrder->feedback->quality_rating ?? '—' }}/5</p>
                            <p>Would recommend: {{ $workOrder->feedback->would_recommend === null ? '—' : ($workOrder->feedback->would_recommend ? 'Yes' : 'No') }}</p>
                        </div>
                        @if ($workOrder->feedback->comments)
                            <p class="text-sm text-gray-600 mt-3">{{ $workOrder->feedback->comments }}</p>
                        @endif
                    </div>
                @elseif ($canLeaveFeedback)
                    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Leave Feedback</h2>
                        <form wire:submit.prevent="submitFeedback" class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-500">Overall Satisfaction</label>
                                <select wire:model="feedback.overall" class="mt-1 w-full rounded-md border-gray-300">
                                    <option value="0">Select rating</option>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                                @error('feedback.overall') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-gray-500">Professionalism</label>
                                    <select wire:model="feedback.professionalism" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="0">Select rating</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('feedback.professionalism') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Technical Knowledge</label>
                                    <select wire:model="feedback.knowledge" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="0">Select rating</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('feedback.knowledge') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Communication</label>
                                    <select wire:model="feedback.communication" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="0">Select rating</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('feedback.communication') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Timeliness</label>
                                    <select wire:model="feedback.timeliness" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="0">Select rating</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('feedback.timeliness') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Quality of Work</label>
                                    <select wire:model="feedback.quality" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="0">Select rating</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('feedback.quality') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Would you recommend us?</label>
                                    <select wire:model="feedback.would_recommend" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    @error('feedback.would_recommend') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Comments</label>
                                <textarea wire:model="feedback.comments" class="mt-1 w-full rounded-md border-gray-300" rows="3" placeholder="Share your experience"></textarea>
                                @error('feedback.comments') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submitFeedback">Submit Feedback</span>
                                <span wire:loading wire:target="submitFeedback">Submitting...</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
