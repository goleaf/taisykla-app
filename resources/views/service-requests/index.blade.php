@extends('layouts.app')

@section('title', 'Service Requests')

@section('content')
    <div class="py-8" x-data="serviceRequestsPage()" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Page Header --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Service Requests</h1>
                    <p class="text-sm text-gray-500">Manage and track all service requests in your organization.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Export CSV Button --}}
                    <button type="button" @click="exportToCsv()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </button>

                    {{-- Create New Request Button (permission check) --}}
                    @can('create', App\Models\ServiceRequest::class)
                        <a href="{{ route('service-requests.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create New Request
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Filter Section (Collapsible Card) --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 overflow-hidden">
                <button type="button" @click="filtersOpen = !filtersOpen"
                    class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Filters</span>
                        <span x-show="activeFiltersCount > 0" x-text="activeFiltersCount"
                            class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium text-white bg-indigo-600 rounded-full"></span>
                    </div>
                    <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                        :class="{ 'rotate-180': filtersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="filtersOpen" x-collapse class="border-t border-gray-100">
                    <div class="p-6 space-y-4">
                        {{-- Filter Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            {{-- Status Filter --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                                <select x-model="filters.status" @change="applyFilters()"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            {{-- Priority Filter --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                                <select x-model="filters.priority" @change="applyFilters()"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Priorities</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>

                            {{-- Customer Search (Admin/Manager only) --}}
                            @canany(['viewAny', 'manage'], App\Models\ServiceRequest::class)
                                <div x-data="{ customerSearch: '', customerResults: [], showCustomerDropdown: false }"
                                    class="relative">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Customer</label>
                                    <input type="text" x-model="customerSearch" @input.debounce.300ms="searchCustomers()"
                                        @focus="showCustomerDropdown = customerResults.length > 0"
                                        @click.away="showCustomerDropdown = false" placeholder="Search customers..."
                                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <div x-show="showCustomerDropdown && customerResults.length > 0" x-transition
                                        class="absolute z-50 w-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 max-h-48 overflow-y-auto">
                                        <template x-for="customer in customerResults" :key="customer.id">
                                            <button type="button" @click="selectCustomer(customer)"
                                                class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 focus:bg-gray-50"
                                                x-text="customer.name"></button>
                                        </template>
                                    </div>
                                </div>
                            @endcanany

                            {{-- Technician Filter (Admin/Manager only) --}}
                            @canany(['viewAny', 'manage'], App\Models\ServiceRequest::class)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Technician</label>
                                    <select x-model="filters.technician_id" @change="applyFilters()"
                                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">All Technicians</option>
                                        @foreach($technicians ?? [] as $technician)
                                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endcanany

                            {{-- Date From --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Scheduled From</label>
                                <input type="date" x-model="filters.date_from" @change="applyFilters()"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Date To --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Scheduled To</label>
                                <input type="date" x-model="filters.date_to" @change="applyFilters()"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Keyword Search --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Keyword Search</label>
                                <div class="relative">
                                    <input type="text" x-model="filters.search" @input.debounce.300ms="applyFilters()"
                                        placeholder="Search by ID, description, notes..."
                                        class="w-full pl-10 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Filter Actions --}}
                        <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-gray-100">
                            <button type="button" @click="clearFilters()"
                                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear Filters
                            </button>
                            <button type="button" @click="applyFilters()"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bulk Actions Section --}}
            <div x-show="selectedItems.length > 0" x-transition
                class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <span class="text-sm font-medium text-indigo-900">
                        <span x-text="selectedItems.length"></span> items selected
                    </span>
                    <div class="flex items-center gap-3">
                        <div class="relative" x-data="{ bulkActionsOpen: false }">
                            <button type="button" @click="bulkActionsOpen = !bulkActionsOpen"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Bulk Actions
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="bulkActionsOpen" @click.away="bulkActionsOpen = false" x-transition
                                class="absolute right-0 z-50 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                                <button type="button" @click="bulkUpdateStatus('assigned')"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50">
                                    Mark as Assigned
                                </button>
                                <button type="button" @click="bulkUpdateStatus('in_progress')"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50">
                                    Mark as In Progress
                                </button>
                                <button type="button" @click="bulkUpdateStatus('completed')"
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50">
                                    Mark as Completed
                                </button>
                                <hr class="my-1">
                                <button type="button" @click="bulkDelete()"
                                    class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                    Delete Selected
                                </button>
                            </div>
                        </div>
                        <button type="button" @click="clearSelection()"
                            class="text-sm text-indigo-600 hover:text-indigo-800">
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>

            {{-- Results Section --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 overflow-hidden relative">
                {{-- Loading Spinner Overlay --}}
                <div x-show="loading" x-transition.opacity
                    class="absolute inset-0 bg-white/60 z-20 flex items-center justify-center backdrop-blur-[1px]">
                    <div class="flex flex-col items-center">
                        <svg class="w-10 h-10 text-indigo-600 animate-spin mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="text-sm font-medium text-indigo-600">Loading...</span>
                    </div>
                </div>

                {{-- Results Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500">
                            Showing <span class="font-medium text-gray-900" x-text="pagination.from || 0"></span>
                            to <span class="font-medium text-gray-900" x-text="pagination.to || 0"></span>
                            of <span class="font-medium text-gray-900" x-text="pagination.total || 0"></span> results
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm text-gray-500">Per page:</label>
                        <select x-model="perPage" @change="applyFilters()"
                            class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" @change="toggleSelectAll($event)" :checked="allSelected"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button type="button" @click="sortBy('id')"
                                        class="inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-900">
                                        ID
                                        <template x-if="sortField === 'id'">
                                            <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortDirection === 'desc' }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </template>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button type="button" @click="sortBy('customer')"
                                        class="inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-900">
                                        Customer
                                        <template x-if="sortField === 'customer'">
                                            <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortDirection === 'desc' }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </template>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Equipment
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button type="button" @click="sortBy('priority')"
                                        class="inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-900">
                                        Priority
                                        <template x-if="sortField === 'priority'">
                                            <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortDirection === 'desc' }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </template>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button type="button" @click="sortBy('status')"
                                        class="inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-900">
                                        Status
                                        <template x-if="sortField === 'status'">
                                            <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortDirection === 'desc' }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </template>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button type="button" @click="sortBy('scheduled_at')"
                                        class="inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-900">
                                        Scheduled Date
                                        <template x-if="sortField === 'scheduled_at'">
                                            <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': sortDirection === 'desc' }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </template>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Technician
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($serviceRequests ?? [] as $request)
                                <tr class="hover:bg-gray-50 transition-colors cursor-pointer {{ $request->isOverdue() ? 'bg-red-50' : '' }}"
                                    @click="viewRequest({{ $request->id }})">
                                    <td class="px-6 py-4" @click.stop>
                                        <input type="checkbox" value="{{ $request->id }}" x-model="selectedItems"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-indigo-600">#{{ $request->id }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                <span
                                                    class="text-xs font-medium text-gray-600">{{ substr($request->customer?->name ?? 'N/A', 0, 2) }}</span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 truncate max-w-[150px]"
                                                    title="{{ $request->customer?->name }}">
                                                    {{ $request->customer?->name ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900 truncate max-w-[150px]"
                                            title="{{ $request->equipment?->name }}">
                                            {{ $request->equipment?->name ?? 'N/A' }}
                                        </p>
                                        @if($request->equipment?->serial_number)
                                            <p class="text-xs text-gray-500">{{ $request->equipment->serial_number }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $priorityClasses = match ($request->priority) {
                                                'urgent' => 'bg-red-100 text-red-800 ring-red-600/20',
                                                'high' => 'bg-orange-100 text-orange-800 ring-orange-600/20',
                                                'medium' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
                                                'low' => 'bg-green-100 text-green-800 ring-green-600/20',
                                                default => 'bg-gray-100 text-gray-800 ring-gray-600/20',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $priorityClasses }}">
                                            {{ ucfirst($request->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusClasses = match ($request->status) {
                                                'pending' => 'bg-gray-100 text-gray-800 ring-gray-600/20',
                                                'assigned' => 'bg-blue-100 text-blue-800 ring-blue-600/20',
                                                'in_progress' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
                                                'completed' => 'bg-green-100 text-green-800 ring-green-600/20',
                                                'cancelled' => 'bg-red-100 text-red-800 ring-red-600/20',
                                                default => 'bg-gray-100 text-gray-800 ring-gray-600/20',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusClasses }}">
                                            {{ $request->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($request->scheduled_at)
                                            <p class="text-sm text-gray-900">{{ $request->scheduled_at->format('M d, Y') }}</p>
                                            <p class="text-xs text-gray-500">{{ $request->scheduled_at->format('h:i A') }}</p>
                                        @else
                                            <span class="text-sm text-gray-400">Not scheduled</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($request->technician)
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                                                    <span
                                                        class="text-xs font-medium text-indigo-600">{{ substr($request->technician->name, 0, 1) }}</span>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-900 truncate max-w-[100px]"
                                                    title="{{ $request->technician->name }}">
                                                    {{ $request->technician->name }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right" @click.stop>
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('service-requests.show', $request) }}"
                                                class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                title="View">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            @can('update', $request)
                                                <a href="{{ route('service-requests.edit', $request) }}"
                                                    class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                    title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            @endcan
                                            @can('delete', $request)
                                                <button type="button" @click.stop="confirmDelete({{ $request->id }})"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                {{-- Empty State --}}
                                <tr>
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">No service requests found</h3>
                                            <p class="text-sm text-gray-500 mb-4">Try adjusting your filters or create a new
                                                service request.</p>
                                            @can('create', App\Models\ServiceRequest::class)
                                                <a href="{{ route('service-requests.create') }}"
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    Create New Request
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card View --}}
                <div class="lg:hidden divide-y divide-gray-100">
                    @forelse($serviceRequests ?? [] as $request)
                        <div class="p-4 hover:bg-gray-50 transition-colors {{ $request->isOverdue() ? 'bg-red-50' : '' }}"
                            @click="viewRequest({{ $request->id }})">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" value="{{ $request->id }}" x-model="selectedItems" @click.stop
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div>
                                        <span class="text-sm font-medium text-indigo-600">#{{ $request->id }}</span>
                                        <p class="text-sm font-medium text-gray-900">{{ $request->customer?->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    @php
                                        $priorityClasses = match ($request->priority) {
                                            'urgent' => 'bg-red-100 text-red-800',
                                            'high' => 'bg-orange-100 text-orange-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'low' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusClasses = match ($request->status) {
                                            'pending' => 'bg-gray-100 text-gray-800',
                                            'assigned' => 'bg-blue-100 text-blue-800',
                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $priorityClasses }}">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClasses }}">
                                        {{ $request->status_label }}
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm text-gray-500">
                                @if($request->equipment)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                        </svg>
                                        <span class="truncate">{{ $request->equipment->name }}</span>
                                    </div>
                                @endif
                                @if($request->scheduled_at)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $request->scheduled_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                @endif
                                @if($request->technician)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>{{ $request->technician->name }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-gray-100" @click.stop>
                                <a href="{{ route('service-requests.show', $request) }}"
                                    class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                                    View
                                </a>
                                @can('update', $request)
                                    <a href="{{ route('service-requests.edit', $request) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200">
                                        Edit
                                    </a>
                                @endcan
                                @can('delete', $request)
                                    <button type="button" @click.stop="confirmDelete({{ $request->id }})"
                                        class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200">
                                        Delete
                                    </button>
                                @endcan
                            </div>
                        </div>
                    @empty
                        {{-- Mobile Empty State --}}
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <p class="text-sm text-gray-500">No service requests found</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if(isset($serviceRequests) && $serviceRequests->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $serviceRequests->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
        <div x-show="deleteModal" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="deleteModal = false"></div>

                <div x-show="deleteModal" x-transition
                    class="relative w-full max-w-md transform overflow-hidden rounded-xl bg-white shadow-xl transition-all">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Delete Service Request</h3>
                        <p class="text-sm text-center text-gray-500 mb-6">
                            Are you sure you want to delete this service request? This action cannot be undone.
                        </p>
                        <div class="flex gap-3">
                            <button type="button" @click="deleteModal = false"
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button type="button" @click="executeDelete()"
                                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function serviceRequestsPage() {
                return {
                    // State
                    filtersOpen: false,
                    loading: false,
                    deleteModal: false,
                    deleteId: null,
                    selectedItems: [],
                    allSelected: false,

                    // Filters
                    filters: {
                        status: '',
                        priority: '',
                        customer_id: '',
                        technician_id: '',
                        date_from: '',
                        date_to: '',
                        search: ''
                    },

                    // Sorting
                    sortField: 'created_at',
                    sortDirection: 'desc',

                    // Pagination
                    perPage: 25,
                    pagination: {
                        from: 0,
                        to: 0,
                        total: 0,
                        currentPage: 1,
                        lastPage: 1
                    },

                    // Computed
                    get activeFiltersCount() {
                        return Object.values(this.filters).filter(v => v !== '').length;
                    },

                    // Methods
                    init() {
                        // Initialize from URL params if present
                        const params = new URLSearchParams(window.location.search);
                        if (params.has('status')) this.filters.status = params.get('status');
                        if (params.has('priority')) this.filters.priority = params.get('priority');
                        if (params.has('search')) this.filters.search = params.get('search');
                        if (params.has('per_page')) this.perPage = parseInt(params.get('per_page'));
                        if (params.has('sort')) this.sortField = params.get('sort');
                        if (params.has('direction')) this.sortDirection = params.get('direction');
                    },

                    applyFilters() {
                        this.loading = true;

                        const params = new URLSearchParams();
                        Object.entries(this.filters).forEach(([key, value]) => {
                            if (value) params.set(key, value);
                        });
                        params.set('per_page', this.perPage);
                        params.set('sort', this.sortField);
                        params.set('direction', this.sortDirection);

                        // Update URL and reload
                        window.location.href = `${window.location.pathname}?${params.toString()}`;
                    },

                    clearFilters() {
                        this.filters = {
                            status: '',
                            priority: '',
                            customer_id: '',
                            technician_id: '',
                            date_from: '',
                            date_to: '',
                            search: ''
                        };
                        this.applyFilters();
                    },

                    sortBy(field) {
                        if (this.sortField === field) {
                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortField = field;
                            this.sortDirection = 'asc';
                        }
                        this.applyFilters();
                    },

                    viewRequest(id) {
                        window.location.href = `/service-requests/${id}`;
                    },

                    confirmDelete(id) {
                        this.deleteId = id;
                        this.deleteModal = true;
                    },

                    executeDelete() {
                        if (!this.deleteId) return;

                        // Submit form for deletion
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/service-requests/${this.deleteId}`;

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        form.appendChild(methodInput);

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                        form.appendChild(csrfInput);

                        document.body.appendChild(form);
                        form.submit();
                    },

                    toggleSelectAll(event) {
                        if (event.target.checked) {
                            this.selectedItems = @json(($serviceRequests ?? collect())->pluck('id')->map(fn($id) => (string) $id)->toArray());
                            this.allSelected = true;
                        } else {
                            this.selectedItems = [];
                            this.allSelected = false;
                        }
                    },

                    clearSelection() {
                        this.selectedItems = [];
                        this.allSelected = false;
                    },

                    bulkUpdateStatus(status) {
                        if (this.selectedItems.length === 0) return;

                        this.loading = true;

                        fetch('/service-requests/bulk-update-status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                ids: this.selectedItems,
                                status: status
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.reload();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.loading = false;
                            });
                    },

                    bulkDelete() {
                        if (this.selectedItems.length === 0) return;

                        if (!confirm(`Are you sure you want to delete ${this.selectedItems.length} service requests?`)) {
                            return;
                        }

                        this.loading = true;

                        fetch('/service-requests/bulk-delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                ids: this.selectedItems
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.reload();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.loading = false;
                            });
                    },

                    exportToCsv() {
                        const params = new URLSearchParams();
                        Object.entries(this.filters).forEach(([key, value]) => {
                            if (value) params.set(key, value);
                        });
                        params.set('format', 'csv');

                        window.location.href = `/service-requests/export?${params.toString()}`;
                    },

                    searchCustomers() {
                        // This would be an API call in production
                        // For now, just a placeholder
                    },

                    selectCustomer(customer) {
                        this.filters.customer_id = customer.id;
                        this.applyFilters();
                    }
                }
            }
        </script>
    @endpush
@endsection