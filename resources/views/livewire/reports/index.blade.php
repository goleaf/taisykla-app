<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Reports & Analytics</h1>
            <p class="text-sm text-gray-500">Track performance, revenue, and service trends.</p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Saved Reports</h2>
                        <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showCreate')">
                            New Report
                        </button>
                    </div>

                    <div class="space-y-4">
                        @forelse ($reports as $report)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">{{ $report->name }}</h3>
                                        <p class="text-xs text-gray-500">
                                            {{ $reportTypes[$report->report_type] ?? $report->report_type }}
                                            @if ($report->is_public)
                                                · Public
                                            @endif
                                        </p>
                                        @if ($report->description)
                                            <p class="text-sm text-gray-600 mt-2">{{ $report->description }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md" wire:click="previewReport({{ $report->id }})">Preview</button>
                                        <a class="px-3 py-1 text-sm border border-gray-300 rounded-md" href="{{ route('reports.export', $report) }}">CSV</a>
                                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-md" wire:click="startSchedule({{ $report->id }})">Schedule</button>
                                    </div>
                                </div>

                                <div class="mt-3 text-xs text-gray-500 flex flex-wrap gap-4">
                                    <span>Created by: {{ $report->createdBy?->name ?? 'System' }}</span>
                                    <span>Schedules: {{ $report->schedules->count() }}</span>
                                    <span>Last run: {{ $report->runs->first()?->run_at?->diffForHumans() ?? 'Never' }}</span>
                                </div>

                                @if ($report->schedules->isNotEmpty())
                                    <div class="mt-3 space-y-1 text-xs text-gray-600">
                                        @foreach ($report->schedules as $schedule)
                                            <div>
                                                {{ ucfirst($schedule->frequency) }}
                                                @if ($schedule->frequency === 'weekly')
                                                    · Day {{ $schedule->day_of_week }}
                                                @elseif ($schedule->frequency === 'monthly')
                                                    · Day {{ $schedule->day_of_month }}
                                                @endif
                                                · {{ $schedule->time_of_day ?? 'No time' }}
                                                · {{ $schedule->is_active ? 'Active' : 'Paused' }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($scheduleReportId === $report->id)
                                    <form wire:submit.prevent="createSchedule" class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                                        <div>
                                            <label class="text-xs text-gray-500">Frequency</label>
                                            <select wire:model="newSchedule.frequency" class="mt-1 w-full rounded-md border-gray-300">
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Day of Week</label>
                                            <input type="number" wire:model="newSchedule.day_of_week" class="mt-1 w-full rounded-md border-gray-300" min="0" max="6" />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Day of Month</label>
                                            <input type="number" wire:model="newSchedule.day_of_month" class="mt-1 w-full rounded-md border-gray-300" min="1" max="31" />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Time of Day</label>
                                            <input type="time" wire:model="newSchedule.time_of_day" class="mt-1 w-full rounded-md border-gray-300" />
                                        </div>
                                        <div class="md:col-span-5">
                                            <label class="text-xs text-gray-500">Recipients (comma separated)</label>
                                            <input wire:model="newSchedule.recipients" class="mt-1 w-full rounded-md border-gray-300" />
                                        </div>
                                        <div class="md:col-span-5 flex items-center gap-3">
                                            <button class="px-3 py-1 bg-indigo-600 text-white rounded-md">Save Schedule</button>
                                            <button type="button" class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$set('scheduleReportId', null)">Cancel</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No reports created yet.</p>
                        @endforelse
                    </div>
                </div>

                @if ($showPreview && ! empty($preview))
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">{{ $previewTitle }}</h2>
                            <button class="text-sm text-gray-500" wire:click="clearPreview">Close</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @foreach ($preview['columns'] ?? [] as $column)
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach (($preview['rows'] ?? []) as $row)
                                        <tr>
                                            @foreach ($preview['columns'] ?? [] as $column)
                                                <td class="px-3 py-2 text-sm text-gray-700">{{ $row[$column] ?? '—' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Create Report</h2>
                        @if ($showCreate)
                            <button class="text-sm text-gray-500" wire:click="$toggle('showCreate')">Close</button>
                        @endif
                    </div>

                    @if ($showCreate)
                        <form wire:submit.prevent="createReport" class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-500">Name</label>
                                <input wire:model="newReport.name" class="mt-1 w-full rounded-md border-gray-300" />
                                @error('newReport.name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Report Type</label>
                                <select wire:model="newReport.report_type" class="mt-1 w-full rounded-md border-gray-300">
                                    @foreach ($reportTypes as $type => $label)
                                        <option value="{{ $type }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($newReport['report_type'] === 'custom')
                                <div>
                                    <label class="text-xs text-gray-500">Data Source</label>
                                    <select wire:model="newReport.data_source" class="mt-1 w-full rounded-md border-gray-300">
                                        @foreach ($dataSources as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Fields (comma separated)</label>
                                    <input wire:model="newReport.fields" class="mt-1 w-full rounded-md border-gray-300" placeholder="id, status, priority" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Group By (comma separated)</label>
                                    <input wire:model="newReport.group_by" class="mt-1 w-full rounded-md border-gray-300" placeholder="status" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Sort By (field:direction)</label>
                                    <input wire:model="newReport.sort_by" class="mt-1 w-full rounded-md border-gray-300" placeholder="created_at:desc" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Filters (JSON)</label>
                                    <textarea wire:model="newReport.filters" class="mt-1 w-full rounded-md border-gray-300" rows="3" placeholder='[{"field":"status","operator":"=","value":"completed"}]'></textarea>
                                </div>
                            @endif
                            <div>
                                <label class="text-xs text-gray-500">Description</label>
                                <textarea wire:model="newReport.description" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <input type="checkbox" wire:model="newReport.is_public" class="rounded border-gray-300" />
                                <span>Visible to all users</span>
                            </div>
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create Report</button>
                        </form>
                    @else
                        <p class="text-sm text-gray-500">Select "New Report" to define a custom or template-based report.</p>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Report Templates</h2>
                    <div class="space-y-3 text-sm">
                        @foreach ($reportTypes as $type => $label)
                            @if ($type !== 'custom')
                                <div class="flex items-center justify-between">
                                    <span>{{ $label }}</span>
                                    <button class="text-xs text-indigo-600" wire:click="previewTemplate('{{ $type }}')">Preview</button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
