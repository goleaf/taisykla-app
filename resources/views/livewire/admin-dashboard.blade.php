<div class="min-h-screen bg-gray-900 text-gray-100" wire:poll.60s>
    {{-- Header --}}
    <header class="bg-gray-800 border-b border-gray-700 sticky top-0 z-40">
        <div class="px-4 lg:px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-white">System Administration</h1>
                    <p class="text-gray-400 text-sm">Monitoring & Management Dashboard</p>
                </div>

                <div class="flex items-center gap-4">
                    {{-- Date Range --}}
                    <select wire:model.live="dateRange"
                        class="bg-gray-700 border-gray-600 text-gray-200 text-sm rounded-lg">
                        <option value="7d">Last 7 days</option>
                        <option value="30d">Last 30 days</option>
                        <option value="90d">Last 90 days</option>
                        <option value="1y">Last year</option>
                    </select>

                    {{-- Export Button --}}
                    <button wire:click="exportMetrics"
                        class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </button>

                    {{-- Current Time --}}
                    <div class="text-gray-400 text-sm font-mono">
                        {{ now()->format('H:i:s') }}
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            class="fixed top-20 right-4 z-50 bg-green-600 text-white px-4 py-3 rounded-xl shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="p-4 lg:p-6 space-y-6">
        {{-- Quick Action Buttons --}}
        <section class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('users.index') ?? '#' }}"
                class="p-4 bg-gray-800 rounded-xl hover:bg-gray-700 transition text-center group">
                <div
                    class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-indigo-500/30">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-300">Create User</span>
            </a>

            <a href="{{ route('settings.index') ?? '#' }}"
                class="p-4 bg-gray-800 rounded-xl hover:bg-gray-700 transition text-center group">
                <div
                    class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-purple-500/30">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-300">Settings</span>
            </a>

            <button wire:click="runDatabaseMaintenance"
                class="p-4 bg-gray-800 rounded-xl hover:bg-gray-700 transition text-center group">
                <div
                    class="w-10 h-10 bg-cyan-500/20 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-cyan-500/30">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                </div>
                <span class="text-sm text-gray-300">DB Maintenance</span>
            </button>

            <button wire:click="triggerBackup"
                class="p-4 bg-gray-800 rounded-xl hover:bg-gray-700 transition text-center group">
                <div
                    class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-green-500/30">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>
                <span class="text-sm text-gray-300">Backup Now</span>
            </button>

            <a href="#" class="p-4 bg-gray-800 rounded-xl hover:bg-gray-700 transition text-center group">
                <div
                    class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-amber-500/30">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-300">System Logs</span>
            </a>

            <a href="#" class="p-4 bg-gray-800 rounded-xl hover:bg-gray-700 transition text-center group">
                <div
                    class="w-10 h-10 bg-rose-500/20 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-rose-500/30">
                    <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <span class="text-sm text-gray-300">Integrations</span>
            </a>
        </section>

        {{-- Alert Center --}}
        @if ($alerts->count() > 0)
            <section class="bg-gray-800 rounded-2xl p-4">
                <h2 class="font-semibold text-white mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Alert Center
                    <span class="px-2 py-0.5 bg-amber-500/20 text-amber-400 text-xs font-medium rounded-full">
                        {{ $alerts->count() }}
                    </span>
                </h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($alerts as $alert)
                        @php
                            $alertStyles = [
                                'critical' => 'bg-red-500/10 border-red-500/30 text-red-400',
                                'warning' => 'bg-amber-500/10 border-amber-500/30 text-amber-400',
                                'info' => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
                            ];
                        @endphp
                        <div class="p-3 rounded-xl border {{ $alertStyles[$alert['type']] ?? $alertStyles['info'] }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-medium text-sm">{{ $alert['title'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $alert['message'] }}</p>
                                </div>
                                <span class="px-1.5 py-0.5 text-[10px] uppercase font-bold rounded 
                                            {{ $alert['type'] === 'critical' ? 'bg-red-500 text-white' : '' }}
                                            {{ $alert['type'] === 'warning' ? 'bg-amber-500 text-black' : '' }}
                                            {{ $alert['type'] === 'info' ? 'bg-blue-500 text-white' : '' }}
                                        ">
                                    {{ $alert['type'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Main Dashboard Grid --}}
        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Left Column --}}
            <div class="space-y-6">
                {{-- System Health --}}
                <section class="bg-gray-800 rounded-2xl p-4">
                    <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        System Health
                    </h2>

                    <div class="space-y-4">
                        {{-- Uptime --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Server Uptime</p>
                                <p class="text-2xl font-bold text-white">{{ $systemHealth['uptime'] }}%</p>
                            </div>
                            <div class="w-4 h-4 rounded-full bg-{{ $systemHealth['uptime_status'] }}-500"></div>
                        </div>

                        {{-- Response Time --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Response Time</p>
                                <p class="text-xl font-bold text-white">{{ $systemHealth['response_time'] }}ms</p>
                            </div>
                            <div class="w-4 h-4 rounded-full bg-{{ $systemHealth['response_status'] }}-500"></div>
                        </div>

                        {{-- Database --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Database</p>
                                <p class="text-lg font-medium text-white">{{ $systemHealth['db_connections'] }} tables
                                </p>
                            </div>
                            <div class="w-4 h-4 rounded-full bg-{{ $systemHealth['db_status'] }}-500"></div>
                        </div>

                        {{-- Storage --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm text-gray-400">Storage</p>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm text-white">{{ $systemHealth['storage_used'] }}/{{ $systemHealth['storage_total'] }}
                                        GB</span>
                                    <div class="w-3 h-3 rounded-full bg-{{ $systemHealth['storage_status'] }}-500">
                                    </div>
                                </div>
                            </div>
                            <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-{{ $systemHealth['storage_status'] }}-500 transition-all"
                                    style="width: {{ $systemHealth['storage_percent'] }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">~{{ $systemHealth['storage_projection_days'] }} days
                                until full</p>
                        </div>

                        {{-- Active Sessions --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-700">
                            <p class="text-sm text-gray-400">Active Sessions (24h)</p>
                            <p class="text-lg font-bold text-white">{{ $systemHealth['active_sessions'] }}</p>
                        </div>
                    </div>

                    {{-- API Endpoints --}}
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <p class="text-sm text-gray-400 mb-2">API Endpoints</p>
                        <div class="space-y-1">
                            @foreach ($systemHealth['endpoints'] as $endpoint)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-300">{{ $endpoint['name'] }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500">{{ $endpoint['latency'] }}ms</span>
                                        <span
                                            class="w-2 h-2 rounded-full {{ $endpoint['status'] === 'healthy' ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Security Monitoring --}}
                <section class="bg-gray-800 rounded-2xl p-4">
                    <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Security
                        @if ($securityMetrics['security_alerts'] > 0)
                            <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-medium rounded-full">
                                {{ $securityMetrics['security_alerts'] }} alert(s)
                            </span>
                        @endif
                    </h2>

                    <div class="space-y-3">
                        {{-- Failed Logins --}}
                        <div class="flex items-center justify-between p-3 bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="text-sm text-gray-400">Failed Login Attempts</p>
                                <p class="text-xl font-bold text-white">{{ $securityMetrics['failed_logins'] }}</p>
                            </div>
                            <div class="w-4 h-4 rounded-full bg-{{ $securityMetrics['failed_status'] }}-500"></div>
                        </div>

                        {{-- Locked Accounts --}}
                        <div class="flex items-center justify-between p-3 bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="text-sm text-gray-400">Locked Accounts</p>
                                <p class="text-xl font-bold text-white">{{ $securityMetrics['lockout_count'] }}</p>
                            </div>
                            <div
                                class="w-4 h-4 rounded-full {{ $securityMetrics['lockout_count'] > 0 ? 'bg-amber-500' : 'bg-green-500' }}">
                            </div>
                        </div>

                        {{-- Security Status --}}
                        <div
                            class="p-3 rounded-xl {{ $securityMetrics['security_status'] === 'green' ? 'bg-green-500/10 border border-green-500/30' : ($securityMetrics['security_status'] === 'yellow' ? 'bg-amber-500/10 border border-amber-500/30' : 'bg-red-500/10 border border-red-500/30') }}">
                            <p
                                class="text-sm font-medium {{ $securityMetrics['security_status'] === 'green' ? 'text-green-400' : ($securityMetrics['security_status'] === 'yellow' ? 'text-amber-400' : 'text-red-400') }}">
                                @if ($securityMetrics['security_status'] === 'green')
                                    ✓ All security checks passed
                                @else
                                    ⚠ Security attention required
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Recent Lockouts --}}
                    @if ($securityMetrics['recent_lockouts']->count() > 0)
                        <div class="mt-4 pt-4 border-t border-gray-700">
                            <p class="text-sm text-gray-400 mb-2">Recent Lockouts</p>
                            <div class="space-y-2">
                                @foreach ($securityMetrics['recent_lockouts'] as $lockout)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-300">{{ $lockout->email }}</span>
                                        <span class="text-gray-500">{{ $lockout->locked_until->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            {{-- Middle Column --}}
            <div class="space-y-6">
                {{-- User Management --}}
                <section class="bg-gray-800 rounded-2xl p-4">
                    <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        User Management
                    </h2>

                    {{-- Account Stats --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-3 bg-gray-700/50 rounded-xl text-center">
                            <p class="text-2xl font-bold text-white">{{ $userManagement['total_active'] }}</p>
                            <p class="text-xs text-gray-400">Total Active</p>
                        </div>
                        <div class="p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl text-center">
                            <p class="text-2xl font-bold text-amber-400">{{ $userManagement['attention_needed'] }}</p>
                            <p class="text-xs text-amber-300">Needs Attention</p>
                        </div>
                    </div>

                    {{-- By Role --}}
                    <div class="mb-4">
                        <p class="text-sm text-gray-400 mb-2">Accounts by Role</p>
                        <div class="space-y-2">
                            @foreach ($userManagement['accounts_by_role'] as $role => $count)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-300 capitalize">{{ str_replace('_', ' ', $role) }}</span>
                                    <span class="px-2 py-0.5 bg-gray-700 text-gray-300 text-xs rounded">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Attention Items --}}
                    <div class="pt-4 border-t border-gray-700 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Inactive (90+ days)</span>
                            <span
                                class="px-2 py-0.5 {{ $userManagement['inactive_accounts'] > 0 ? 'bg-amber-500/20 text-amber-400' : 'bg-gray-700 text-gray-400' }} rounded text-xs">
                                {{ $userManagement['inactive_accounts'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Locked Accounts</span>
                            <span
                                class="px-2 py-0.5 {{ $userManagement['locked_accounts'] > 0 ? 'bg-red-500/20 text-red-400' : 'bg-gray-700 text-gray-400' }} rounded text-xs">
                                {{ $userManagement['locked_accounts'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Pending Resets</span>
                            <span class="px-2 py-0.5 bg-gray-700 text-gray-400 rounded text-xs">
                                {{ $userManagement['pending_resets'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Recent Creations --}}
                    @if ($userManagement['recent_creations']->count() > 0)
                        <div class="mt-4 pt-4 border-t border-gray-700">
                            <p class="text-sm text-gray-400 mb-2">Recent Accounts</p>
                            <div class="space-y-2">
                                @foreach ($userManagement['recent_creations']->take(3) as $user)
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-[10px] font-bold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <span class="text-gray-300">{{ $user->name }}</span>
                                        </div>
                                        <span class="text-gray-500">{{ $user->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Compliance Dashboard --}}
                <section class="bg-gray-800 rounded-2xl p-4">
                    <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Compliance
                        <span
                            class="ml-auto text-sm text-cyan-400">{{ round($compliance['overall_compliance']) }}%</span>
                    </h2>

                    <div class="space-y-3">
                        {{-- Backup Status --}}
                        <div class="flex items-center justify-between p-3 bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="text-sm text-gray-400">Last Backup</p>
                                <p class="text-sm font-medium text-white">
                                    {{ $compliance['last_backup']->diffForHumans() }}</p>
                            </div>
                            <div class="w-4 h-4 rounded-full bg-{{ $compliance['backup_status'] }}-500"></div>
                        </div>

                        {{-- Patch Status --}}
                        <div class="flex items-center justify-between p-3 bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="text-sm text-gray-400">Pending Patches</p>
                                <p class="text-sm font-medium text-white">{{ $compliance['patches_pending'] }}</p>
                            </div>
                            <div class="w-4 h-4 rounded-full bg-{{ $compliance['patch_status'] }}-500"></div>
                        </div>

                        {{-- Audit Log --}}
                        <div class="flex items-center justify-between p-3 bg-gray-700/50 rounded-xl">
                            <div>
                                <p class="text-sm text-gray-400">Audit Log Integrity</p>
                                <p class="text-sm font-medium text-white">Verified</p>
                            </div>
                            <div class="w-4 h-4 rounded-full bg-{{ $compliance['audit_log_status'] }}-500"></div>
                        </div>
                    </div>

                    {{-- Regulatory Status --}}
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <p class="text-sm text-gray-400 mb-2">Regulatory Compliance</p>
                        <div class="space-y-2">
                            @foreach ($compliance['regulatory_status'] as $reg)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-300">{{ $reg['regulation'] }}</span>
                                    <span class="px-2 py-0.5 rounded text-xs
                                            {{ $reg['status'] === 'compliant' ? 'bg-green-500/20 text-green-400' : '' }}
                                            {{ $reg['status'] === 'in_progress' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                            {{ $reg['status'] === 'non_compliant' ? 'bg-red-500/20 text-red-400' : '' }}
                                        ">
                                        {{ ucfirst(str_replace('_', ' ', $reg['status'])) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Business Intelligence --}}
                <section class="bg-gray-800 rounded-2xl p-4">
                    <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Business Intelligence
                    </h2>

                    {{-- Revenue --}}
                    <div class="mb-4">
                        <p class="text-sm text-gray-400 mb-1">Revenue This Month</p>
                        <div class="flex items-end gap-2">
                            <p class="text-3xl font-bold text-white">
                                ${{ number_format($businessIntelligence['current_month_revenue'], 0) }}</p>
                            @if ($businessIntelligence['revenue_change'] != 0)
                                <span
                                    class="text-sm mb-1 {{ $businessIntelligence['revenue_change'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $businessIntelligence['revenue_change'] >= 0 ? '+' : '' }}{{ $businessIntelligence['revenue_change'] }}%
                                </span>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-xs text-gray-400">
                            <p>Projected: ${{ number_format($businessIntelligence['projected_revenue'], 0) }}</p>
                            <p>Last Month: ${{ number_format($businessIntelligence['last_month_revenue'], 0) }}</p>
                        </div>
                    </div>

                    {{-- Job Stats --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-3 bg-gray-700/50 rounded-xl text-center">
                            <p class="text-xl font-bold text-white">{{ $businessIntelligence['jobs_this_month'] }}</p>
                            <p class="text-xs text-gray-400">Jobs Created</p>
                        </div>
                        <div class="p-3 bg-gray-700/50 rounded-xl text-center">
                            <p class="text-xl font-bold text-white">
                                {{ $businessIntelligence['jobs_completed_this_month'] }}</p>
                            <p class="text-xs text-gray-400">Jobs Completed</p>
                        </div>
                    </div>

                    {{-- Customer Metrics --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-center">
                            <p class="text-xl font-bold text-emerald-400">+{{ $businessIntelligence['new_customers'] }}
                            </p>
                            <p class="text-xs text-emerald-300">New Customers</p>
                        </div>
                        <div class="p-3 bg-gray-700/50 rounded-xl text-center">
                            <p class="text-xl font-bold text-white">{{ $businessIntelligence['total_customers'] }}</p>
                            <p class="text-xs text-gray-400">Total Customers</p>
                        </div>
                    </div>

                    {{-- Avg Profit --}}
                    <div class="p-3 bg-gray-700/50 rounded-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Avg Profit per Job</span>
                            <span
                                class="text-lg font-bold {{ $businessIntelligence['avg_profit_per_job'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                ${{ number_format($businessIntelligence['avg_profit_per_job'], 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Job Volume Trend (Mini Chart) --}}
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <p class="text-sm text-gray-400 mb-2">Job Volume Trend (12mo)</p>
                        <div class="flex items-end gap-1 h-16">
                            @php
                                $maxCount = collect($businessIntelligence['job_volume_trend'])->max('count') ?: 1;
                            @endphp
                            @foreach ($businessIntelligence['job_volume_trend'] as $point)
                                <div class="flex-1 bg-indigo-500/60 hover:bg-indigo-500 rounded-t transition cursor-pointer"
                                    style="height: {{ ($point['count'] / $maxCount) * 100 }}%"
                                    title="{{ $point['month'] }}: {{ $point['count'] }} jobs"></div>
                            @endforeach
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-500 mt-1">
                            <span>{{ $businessIntelligence['job_volume_trend'][0]['month'] ?? '' }}</span>
                            <span>{{ $businessIntelligence['job_volume_trend'][11]['month'] ?? '' }}</span>
                        </div>
                    </div>

                    {{-- Service Breakdown --}}
                    @if (count($businessIntelligence['service_breakdown']) > 0)
                        <div class="mt-4 pt-4 border-t border-gray-700">
                            <p class="text-sm text-gray-400 mb-2">Service Type Breakdown</p>
                            @php
                                $totalJobs = collect($businessIntelligence['service_breakdown'])->sum('count');
                                $colors = ['bg-indigo-500', 'bg-purple-500', 'bg-cyan-500', 'bg-amber-500', 'bg-rose-500'];
                            @endphp
                            <div class="h-3 bg-gray-700 rounded-full overflow-hidden flex">
                                @foreach ($businessIntelligence['service_breakdown'] as $i => $service)
                                    <div class="{{ $colors[$i % count($colors)] }}"
                                        style="width: {{ ($service['count'] / $totalJobs) * 100 }}%"
                                        title="{{ $service['name'] }}: {{ $service['count'] }}"></div>
                                @endforeach
                            </div>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($businessIntelligence['service_breakdown'] as $i => $service)
                                    <div class="flex items-center gap-1 text-[10px]">
                                        <span class="w-2 h-2 rounded-full {{ $colors[$i % count($colors)] }}"></span>
                                        <span class="text-gray-400">{{ $service['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</div>