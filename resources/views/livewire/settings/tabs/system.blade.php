<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- System Health --}}
        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">System State</h2>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-sm font-medium text-gray-600">Operational</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm mb-6">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase font-medium">Active Users</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeUsers }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase font-medium">Work Orders (Open)</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $openWorkOrders }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-600">Last Backup</span>
                    <span class="font-mono text-gray-900">{{ $backupLastRunAt ?? 'Never' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-600">Overdue Orders</span>
                    <span class="font-mono text-red-600 font-bold">{{ $overdueWorkOrders }}</span>
                </div>
                <div class="flex items-center justify-between text-sm py-2">
                    <span class="text-gray-600">Support Tickets</span>
                    <span class="font-mono text-gray-900">{{ $openSupportTickets }}</span>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <button
                    class="w-full justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    wire:click="markBackupComplete">
                    Run Manual Backup
                </button>
            </div>
        </div>

        {{-- Raw Settings --}}
        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Advanced Settings</h2>
                <button class="text-indigo-600 text-sm hover:text-indigo-800"
                    wire:click="$toggle('showSettingCreate')">Add Key</button>
            </div>

            @if ($showSettingCreate)
                <div class="mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                    <form wire:submit.prevent="createSetting" class="grid grid-cols-1 gap-3">
                        <div class="flex gap-3">
                            <input wire:model="newSetting.group" class="rounded-md border-gray-300 w-1/3 text-sm"
                                placeholder="Group" />
                            <input wire:model="newSetting.key" class="rounded-md border-gray-300 w-2/3 text-sm"
                                placeholder="Key" />
                        </div>
                        <input wire:model="newSetting.value" class="rounded-md border-gray-300 text-sm"
                            placeholder="Value (raw)" />
                        <button class="px-3 py-1 bg-gray-800 text-white rounded text-sm hover:bg-black">Set</button>
                    </form>
                </div>
            @endif

            <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                @forelse ($systemSettings as $setting)
                    <div class="p-3 bg-gray-50 rounded border border-gray-200 text-sm">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-mono text-xs text-indigo-600">{{ $setting->group }} .
                                {{ $setting->key }}</span>
                            <button class="text-xs text-gray-400 hover:text-gray-600"
                                wire:click="updateSetting({{ $setting->id }})">Update</button>
                        </div>
                        <input wire:model.blur="settingValues.{{ $setting->id }}"
                            class="w-full bg-white border-gray-300 rounded text-xs font-mono" />
                    </div>
                @empty
                    <div class="text-gray-500 text-center py-4 text-xs">No advanced settings defined.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Audit Log --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Audit Log</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                                {{ $log->created_at?->format('M d, H:i') }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $log->action }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ class_basename($log->subject_type) }}
                                #{{ $log->subject_id }}</td>
                            <td class="px-3 py-2 text-gray-600 truncate max-w-xs">{{ $log->description ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-3 py-4 text-center text-gray-500" colspan="5">No audit entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>