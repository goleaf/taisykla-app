<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Reports & Analytics</h1>
                <p class="text-sm text-gray-500">Operational intelligence, financial insights, and predictive signals in one place.</p>
            </div>
            <div class="text-xs text-gray-400">Last updated {{ now()->toDayDateTimeString() }}</div>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex items-center gap-2 overflow-x-auto border-b border-gray-200 pb-2">
            @php
                $tabs = [
                    'overview' => 'Overview',
                    'reports' => 'Reports',
                    'builder' => 'Builder',
                    'dashboards' => 'Dashboards',
                    'schedules' => 'Schedules',
                    'exports' => 'Exports',
                    'permissions' => 'Permissions',
                    'analytics' => 'Analytics',
                ];
            @endphp
            @foreach ($tabs as $key => $label)
                <button
                    class="px-3 py-1 rounded-full text-sm border {{ $activeTab === $key ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600' }}"
                    wire:click="$set('activeTab', '{{ $key }}')"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($activeTab === 'overview')
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

            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Report Templates</h2>
                    <button class="text-xs text-indigo-600" wire:click="$set('activeTab', 'builder')">Open Builder</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    @foreach ($reportCategories as $category => $types)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase">{{ str_replace('_', ' ', $category) }}</h3>
                            <div class="mt-3 space-y-2">
                                @foreach ($types as $type)
                                    @if ($type !== 'custom')
                                        <div class="flex items-center justify-between">
                                            <span>{{ $reportTypes[$type] ?? $type }}</span>
                                            <button class="text-xs text-indigo-600" wire:click="previewTemplate('{{ $type }}')">Preview</button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($activeTab === 'reports')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Saved Reports</h2>
                            <button class="px-3 py-1 border border-gray-300 rounded-md text-sm" wire:click="$set('activeTab', 'builder')">Create Report</button>
                        </div>

                        <div class="space-y-4">
                            @forelse ($reports as $report)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900">{{ $report->name }}</h3>
                                            <p class="text-xs text-gray-500">
                                                {{ $reportTypes[$report->report_type] ?? $report->report_type }} · {{ ucfirst($report->category ?? 'custom') }}
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
                                            @if ($canExport)
                                                <a class="px-3 py-1 text-sm border border-gray-300 rounded-md" href="{{ route('reports.export', $report) }}?format=csv">CSV</a>
                                                <a class="px-3 py-1 text-sm border border-gray-300 rounded-md" href="{{ route('reports.export', $report) }}?format=xlsx">XLSX</a>
                                                <a class="px-3 py-1 text-sm border border-gray-300 rounded-md" href="{{ route('reports.export', $report) }}?format=pdf">PDF</a>
                                                <a class="px-3 py-1 text-sm border border-gray-300 rounded-md" href="{{ route('reports.export', $report) }}?format=csv&async=1">Queue Export</a>
                                            @endif
                                            @if ($canManage)
                                                <button class="px-3 py-1 text-sm border border-gray-300 rounded-md" wire:click="startSchedule({{ $report->id }})">Schedule</button>
                                            @endif
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
                                                    · {{ strtoupper($schedule->format ?? 'CSV') }}
                                                    · {{ $schedule->is_active ? 'Active' : 'Paused' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($scheduleReportId === $report->id && $canManage)
                                        <form wire:submit.prevent="createSchedule" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
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
                                            <div>
                                                <label class="text-xs text-gray-500">Format</label>
                                                <select wire:model="newSchedule.format" class="mt-1 w-full rounded-md border-gray-300">
                                                    <option value="csv">CSV</option>
                                                    <option value="xlsx">XLSX</option>
                                                    <option value="pdf">PDF</option>
                                                    <option value="json">JSON</option>
                                                    <option value="xml">XML</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-500">Timezone</label>
                                                <input wire:model="newSchedule.timezone" class="mt-1 w-full rounded-md border-gray-300" placeholder="UTC" />
                                            </div>
                                            <div class="md:col-span-3">
                                                <label class="text-xs text-gray-500">Recipients (comma separated)</label>
                                                <input wire:model="newSchedule.recipients" class="mt-1 w-full rounded-md border-gray-300" />
                                            </div>
                                            <div class="md:col-span-3">
                                                <label class="text-xs text-gray-500">Delivery Channels (comma separated)</label>
                                                <input wire:model="newSchedule.delivery_channels" class="mt-1 w-full rounded-md border-gray-300" placeholder="email, in-app" />
                                            </div>
                                            <div class="md:col-span-3">
                                                <label class="text-xs text-gray-500">Parameters (JSON)</label>
                                                <textarea wire:model="newSchedule.parameters" class="mt-1 w-full rounded-md border-gray-300" rows="2" placeholder='{"start_date":"2026-01-01","end_date":"2026-01-31"}'></textarea>
                                            </div>
                                            <div class="md:col-span-3">
                                                <label class="text-xs text-gray-500">Filters (JSON)</label>
                                                <textarea wire:model="newSchedule.filters" class="mt-1 w-full rounded-md border-gray-300" rows="2" placeholder='[{"field":"status","operator":"=","value":"completed"}]'></textarea>
                                            </div>
                                            <div class="md:col-span-3 flex items-center gap-3">
                                                <button class="px-3 py-1 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                                                    <span wire:loading.remove wire:target="createSchedule">Save Schedule</span>
                                                    <span wire:loading wire:target="createSchedule">Saving...</span>
                                                </button>
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
                </div>

                <div class="space-y-6">
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
                    @else
                        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6 text-sm text-gray-500">
                            Select a report to preview the latest dataset.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($activeTab === 'builder')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Report Builder</h2>
                            <button class="text-sm text-gray-500" wire:click="$toggle('showCreate')">
                                {{ $showCreate ? 'Hide' : 'Show' }}
                            </button>
                        </div>

                        @if ($showCreate && $canManage)
                            <form wire:submit.prevent="createReport" class="space-y-4">
                                <div>
                                    <label class="text-xs text-gray-500">Name</label>
                                    <input wire:model="newReport.name" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('newReport.name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Category</label>
                                        <select wire:model="newReport.category" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($reportCategories as $category => $types)
                                                <option value="{{ $category }}">{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Report Type</label>
                                        <select wire:model="newReport.report_type" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($reportTypes as $type => $label)
                                                <option value="{{ $type }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Visualization</label>
                                    <select wire:model="newReport.visualization" class="mt-1 w-full rounded-md border-gray-300">
                                        @foreach ($visualizationOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
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
                                        <label class="text-xs text-gray-500">Calculated Fields (JSON)</label>
                                        <textarea wire:model="newReport.calculated_fields" class="mt-1 w-full rounded-md border-gray-300" rows="3" placeholder='[{"name":"margin","formula":"total_cost - parts_cost","format":"currency"}]'></textarea>
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
                                    <label class="text-xs text-gray-500">Benchmarking (JSON)</label>
                                    <textarea wire:model="newReport.compare" class="mt-1 w-full rounded-md border-gray-300" rows="2" placeholder='{"type":"previous_period"}'></textarea>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Description</label>
                                    <textarea wire:model="newReport.description" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">Share with Roles (comma separated)</label>
                                        <input wire:model="newReport.share_roles" class="mt-1 w-full rounded-md border-gray-300" placeholder="operations_manager, billing_specialist" />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Allowed Fields (comma separated)</label>
                                        <input wire:model="newReport.allowed_fields" class="mt-1 w-full rounded-md border-gray-300" placeholder="id, status, total_cost" />
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="newReport.share_can_edit" class="rounded border-gray-300" />
                                        <span>Allow edits for shared roles</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="newReport.share_can_share" class="rounded border-gray-300" />
                                        <span>Allow sharing</span>
                                    </label>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <input type="checkbox" wire:model="newReport.is_public" class="rounded border-gray-300" />
                                    <span>Visible to all users</span>
                                </div>
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="createReport">Create Report</span>
                                    <span wire:loading wire:target="createReport">Creating...</span>
                                </button>
                            </form>
                        @else
                            <p class="text-sm text-gray-500">Enable the builder to create a custom or template-based report.</p>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Templates by Category</h2>
                        <div class="space-y-4 text-sm">
                            @foreach ($reportCategories as $category => $types)
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">{{ str_replace('_', ' ', $category) }}</p>
                                    <div class="mt-2 space-y-2">
                                        @foreach ($types as $type)
                                            <div class="flex items-center justify-between">
                                                <span>{{ $reportTypes[$type] ?? $type }}</span>
                                                <button class="text-xs text-indigo-600" wire:click="previewTemplate('{{ $type }}')">Preview</button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($activeTab === 'dashboards')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Dashboards</h2>
                        <div class="space-y-2">
                            @forelse ($dashboards as $dashboard)
                                <button
                                    class="w-full text-left px-3 py-2 rounded-md border {{ $activeDashboardId === $dashboard->id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200' }}"
                                    wire:click="selectDashboard({{ $dashboard->id }})"
                                >
                                    <div class="text-sm font-medium text-gray-900">{{ $dashboard->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $dashboard->dashboard_type)) }}</div>
                                </button>
                            @empty
                                <p class="text-sm text-gray-500">No dashboards created yet.</p>
                            @endforelse
                        </div>
                    </div>

                    @if ($canManage)
                        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Create Dashboard</h3>
                            <form wire:submit.prevent="createDashboard" class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-500">Name</label>
                                    <input wire:model="newDashboard.name" class="mt-1 w-full rounded-md border-gray-300" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Type</label>
                                    <select wire:model="newDashboard.dashboard_type" class="mt-1 w-full rounded-md border-gray-300">
                                        @foreach ($dashboardTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Description</label>
                                    <textarea wire:model="newDashboard.description" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <input type="checkbox" wire:model="newDashboard.is_default" class="rounded border-gray-300" />
                                    <span>Set as default</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <input type="checkbox" wire:model="newDashboard.is_public" class="rounded border-gray-300" />
                                    <span>Visible to all users</span>
                                </div>
                                <button class="px-3 py-1 bg-indigo-600 text-white rounded-md">Create Dashboard</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Widgets</h2>
                            <span class="text-xs text-gray-500">Drag to reorder</span>
                        </div>
                        @if ($activeDashboardId)
                            <div id="dashboard-widgets" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @php
                                    $activeDashboard = $dashboards->firstWhere('id', $activeDashboardId);
                                @endphp
                                @foreach ($activeDashboard?->widgets ?? [] as $widget)
                                    <div
                                        class="border border-gray-200 rounded-lg p-4 bg-white" draggable="true" data-widget-id="{{ $widget->id }}">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $widget->title }}</p>
                                                <p class="text-xs text-gray-500">{{ ucfirst($widget->widget_type) }}</p>
                                            </div>
                                            <span class="text-xs text-gray-400">{{ $widget->report?->name ?? 'Custom' }}</span>
                                        </div>
                                        <div class="mt-4 text-sm text-gray-700">
                                            @php
                                                $widgetData = $dashboardData[$widget->id] ?? [];
                                                $metric = $widgetData['meta']['total_revenue'] ?? $widgetData['meta']['total_cost'] ?? $widgetData['meta']['count'] ?? null;
                                            @endphp
                                            @if ($metric)
                                                <div class="text-2xl font-semibold">{{ $metric }}</div>
                                            @else
                                                <div class="text-sm text-gray-500">Linked to report data</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Select a dashboard to view widgets.</p>
                        @endif
                    </div>

                    @if ($canManage && $activeDashboardId)
                        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Widget</h3>
                            <form wire:submit.prevent="createWidget" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-gray-500">Title</label>
                                    <input wire:model="newWidget.title" class="mt-1 w-full rounded-md border-gray-300" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Type</label>
                                    <select wire:model="newWidget.widget_type" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="kpi">KPI</option>
                                        <option value="chart">Chart</option>
                                        <option value="table">Table</option>
                                        <option value="list">List</option>
                                        <option value="map">Map</option>
                                        <option value="progress">Progress</option>
                                        <option value="text">Text</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs text-gray-500">Attach Report</label>
                                    <select wire:model="newWidget.report_id" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">Select a report</option>
                                        @foreach ($reports as $report)
                                            <option value="{{ $report->id }}">{{ $report->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs text-gray-500">Widget Config (JSON)</label>
                                    <textarea wire:model="newWidget.config" class="mt-1 w-full rounded-md border-gray-300" rows="2" placeholder='{"metric":"total_revenue"}'></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <button class="px-3 py-1 bg-indigo-600 text-white rounded-md">Add Widget</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($activeTab === 'schedules')
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Scheduled Reports</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Report</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Format</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Next Run</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Recipients</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($schedules as $schedule)
                                <tr>
                                    <td class="px-3 py-2">{{ $schedule->report?->name ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($schedule->frequency) }}</td>
                                    <td class="px-3 py-2">{{ strtoupper($schedule->format ?? 'CSV') }}</td>
                                    <td class="px-3 py-2">{{ $schedule->next_run_at?->toDateTimeString() ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ implode(', ', $schedule->recipients ?? []) ?: '—' }}</td>
                                    <td class="px-3 py-2">{{ $schedule->is_active ? 'Active' : 'Paused' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-3 text-sm text-gray-500">No schedules configured.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($activeTab === 'exports')
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Export Queue</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Report</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Format</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rows</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Requested By</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($exports as $export)
                                <tr>
                                    <td class="px-3 py-2">{{ $export->report?->name ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ strtoupper($export->format) }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($export->status) }}</td>
                                    <td class="px-3 py-2">{{ $export->row_count ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $export->requestedBy?->name ?? 'System' }}</td>
                                    <td class="px-3 py-2">
                                        @if ($export->status === 'completed' && $export->file_path)
                                            <a class="text-indigo-600 text-xs" href="{{ route('reports.exports.download', $export) }}">Download</a>
                                        @else
                                            <span class="text-xs text-gray-400">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-3 text-sm text-gray-500">No exports queued.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($activeTab === 'permissions')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Report Permissions</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Report</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">View</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Edit</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fields</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($permissions as $permission)
                                    <tr>
                                        <td class="px-3 py-2">{{ $permission->report?->name ?? '—' }}</td>
                                        <td class="px-3 py-2">{{ $permission->role ?? '—' }}</td>
                                        <td class="px-3 py-2">{{ $permission->can_view ? 'Yes' : 'No' }}</td>
                                        <td class="px-3 py-2">{{ $permission->can_edit ? 'Yes' : 'No' }}</td>
                                        <td class="px-3 py-2">{{ implode(', ', $permission->allowed_fields ?? []) ?: 'All' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-3 text-sm text-gray-500">No permissions configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Permission</h3>
                    <form wire:submit.prevent="savePermission" class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-500">Report</label>
                            <select wire:model="permissionForm.report_id" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Select report</option>
                                @foreach ($reports as $report)
                                    <option value="{{ $report->id }}">{{ $report->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Role</label>
                            <select wire:model="permissionForm.role" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">Select role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Allowed Fields (comma separated)</label>
                            <input wire:model="permissionForm.allowed_fields" class="mt-1 w-full rounded-md border-gray-300" />
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <input type="checkbox" wire:model="permissionForm.can_view" class="rounded border-gray-300" />
                            <span>Allow view</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <input type="checkbox" wire:model="permissionForm.can_edit" class="rounded border-gray-300" />
                            <span>Allow edit</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <input type="checkbox" wire:model="permissionForm.can_share" class="rounded border-gray-300" />
                            <span>Allow sharing</span>
                        </div>
                        <button class="px-3 py-1 bg-indigo-600 text-white rounded-md">Save Permission</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($activeTab === 'analytics')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Predictive Analytics</h2>
                        <button class="text-xs text-indigo-600 disabled:opacity-50" wire:click="loadAnalytics" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="loadAnalytics">Refresh</span>
                            <span wire:loading wire:target="loadAnalytics">Refreshing...</span>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    @foreach ($analytics['columns'] ?? [] as $column)
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach (($analytics['rows'] ?? []) as $row)
                                    <tr>
                                        @foreach ($analytics['columns'] ?? [] as $column)
                                            <td class="px-3 py-2 text-sm text-gray-700">{{ $row[$column] ?? '—' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Equipment Risk</h3>
                        <div class="space-y-2 text-xs text-gray-600">
                            @foreach (($analytics['meta']['equipment_risk'] ?? []) as $risk)
                                <div class="flex items-center justify-between">
                                    <span>{{ $risk['equipment'] ?? 'Unknown' }}</span>
                                    <span>{{ $risk['risk_score'] ?? '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Customer Churn Risk</h3>
                        <div class="space-y-2 text-xs text-gray-600">
                            @foreach (($analytics['meta']['customer_churn_risk'] ?? []) as $risk)
                                <div class="flex items-center justify-between">
                                    <span>{{ $risk['customer'] ?? 'Unknown' }}</span>
                                    <span>{{ $risk['risk_label'] ?? '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        const initWidgets = () => {
            const widgetContainer = document.getElementById('dashboard-widgets');
            if (!widgetContainer || widgetContainer.dataset.ready === 'true') {
                return;
            }

            widgetContainer.dataset.ready = 'true';
            let dragged = null;

            widgetContainer.addEventListener('dragstart', (event) => {
                dragged = event.target.closest('[data-widget-id]');
                if (dragged) {
                    event.dataTransfer.effectAllowed = 'move';
                }
            });

            widgetContainer.addEventListener('dragover', (event) => {
                event.preventDefault();
                const target = event.target.closest('[data-widget-id]');
                if (!dragged || !target || dragged === target) {
                    return;
                }

                const bounds = target.getBoundingClientRect();
                const offset = event.clientY - bounds.top;
                if (offset > bounds.height / 2) {
                    target.after(dragged);
                } else {
                    target.before(dragged);
                }
            });

            widgetContainer.addEventListener('drop', () => {
                const orderedIds = Array.from(widgetContainer.querySelectorAll('[data-widget-id]'))
                    .map((item) => item.getAttribute('data-widget-id'));

                if (orderedIds.length > 0) {
                    @this.call('reorderWidgets', orderedIds);
                }
            });
        };

        initWidgets();

        Livewire.hook('message.processed', () => {
            const widgetContainer = document.getElementById('dashboard-widgets');
            if (widgetContainer) {
                widgetContainer.dataset.ready = 'false';
            }
            initWidgets();
        });
    });
</script>
