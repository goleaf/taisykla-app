@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="serviceRequestsFilter()">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Service Requests</h1>
        
        <!-- Breadcrumbs -->
        <nav class="flex mt-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Service Requests</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Actions Section -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex flex-wrap gap-2">
            @can('create', App\Models\ServiceRequest::class)
            <a href="{{ route('service-requests.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Request
            </a>
            @endcan
            
            <button @click="exportToCSV" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export to CSV
            </button>
        </div>

        <div class="relative" x-show="selectedItems.length > 0">
            <button @click="showBulkActions = !showBulkActions" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white font-medium rounded-lg transition duration-150">
                Bulk Actions (<span x-text="selectedItems.length"></span>)
                <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
            
            <div x-show="showBulkActions" @click.away="showBulkActions = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-10">
                <a href="#" @click.prevent="bulkAssign" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Assign Technician</a>
                <a href="#" @click.prevent="bulkUpdateStatus" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Update Status</a>
                <a href="#" @click.prevent="bulkDelete" class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">Delete Selected</a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <button @click="filtersExpanded = !filtersExpanded" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                <span class="font-semibold text-gray-900 dark:text-white">Filters</span>
                <span x-show="hasActiveFilters()" class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Active</span>
            </div>
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform" :class="{'rotate-180': filtersExpanded}" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>

        <div x-show="filtersExpanded" x-collapse class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select x-model="filters.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="assigned">Assigned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
                    <select x-model="filters.priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                @if(auth()->user()->hasRole(['admin', 'manager']))
                <!-- Customer Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Customer</label>
                    <input type="text" 
                           x-model="filters.customer" 
                           @input.debounce.500ms="applyFilters"
                           placeholder="Search customer..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Technician Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Technician</label>
                    <select x-model="filters.technician" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Technicians</option>
                        @foreach($technicians ?? [] as $technician)
                        <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                    <input type="date" 
                           x-model="filters.date_from" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                    <input type="date" 
                           x-model="filters.date_to" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Keyword Search -->
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Keyword Search</label>
                    <input type="text" 
                           x-model="filters.search" 
                           @input.debounce.500ms="applyFilters"
                           placeholder="Search by ID, customer, equipment, or description..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex justify-end gap-2">
                <button @click="clearFilters" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium rounded-lg transition">
                    Clear Filters
                </button>
                <button @click="applyFilters" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div x-show="loading" class="flex justify-center items-center py-12">
        <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Results Table -->
    <div x-show="!loading" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <!-- Table Header with count and per page selector -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing <span class="font-semibold">{{ $serviceRequests->firstItem() ?? 0 }}</span> to 
                <span class="font-semibold">{{ $serviceRequests->lastItem() ?? 0 }}</span> of 
                <span class="font-semibold">{{ $serviceRequests->total() }}</span> results
            </div>
            
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-700 dark:text-gray-300">Per page:</label>
                <select x-model="perPage" @change="changePerPage" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        @if($serviceRequests->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" @change="toggleAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="sortBy('id')">
                            <div class="flex items-center">
                                ID
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="sortBy('customer')">
                            <div class="flex items-center">
                                Customer
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Equipment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="sortBy('priority')">
                            <div class="flex items-center">
                                Priority
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="sortBy('status')">
                            <div class="flex items-center">
                                Status
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="sortBy('scheduled_date')">
                            <div class="flex items-center">
                                Scheduled Date
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Technician</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($serviceRequests as $request)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer {{ $request->isOverdue() ? 'bg-red-50 dark:bg-red-900/20' : '' }}" 
                        @click="window.location.href = '{{ route('service-requests.show', $request) }}'"
                        x-data="{ tooltip: false }">
                        <td class="px-6 py-4 whitespace-nowrap" @click.stop>
                            <input type="checkbox" 
                                   :checked="selectedItems.includes({{ $request->id }})" 
                                   @change="toggleItem({{ $request->id }})"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            #{{ $request->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $request->customer->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $request->customer->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white" 
                                 @mouseenter="tooltip = true" 
                                 @mouseleave="tooltip = false"
                                 x-data="{ text: '{{ $request->equipment->name ?? 'N/A' }}' }">
                                <span x-text="text.length > 30 ? text.substring(0, 30) + '...' : text"></span>
                                <div x-show="tooltip && text.length > 30" 
                                     class="absolute z-10 px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm tooltip dark:bg-gray-700"
                                     style="display: none;">
                                    <span x-text="text"></span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $priorityColors = [
                                    'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                    'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                ];
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $priorityColors[$request->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($request->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                    'assigned' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                    'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                ];
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $request->scheduled_date ? $request->scheduled_date->format('M d, Y') : 'Not scheduled' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $request->technician->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" @click.stop>
                            <div class="flex items-center gap-2">
                                @can('view', $request)
                                <a href="{{ route('service-requests.show', $request) }}" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                   title="View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('update', $request)
                                <a href="{{ route('service-requests.edit', $request) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                   title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('delete', $request)
                                <form action="{{ route('service-requests.destroy', $request) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this service request?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($serviceRequests as $request)
            <div class="p-4 {{ $request->isOverdue() ? 'bg-red-50 dark:bg-red-900/20' : '' }}" 
                 @click="window.location.href = '{{ route('service-requests.show', $request) }}'">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               :checked="selectedItems.includes({{ $request->id }})" 
                               @change="toggleItem({{ $request->id }})"
                               @click.stop
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">#{{ $request->id }}</span>
                    </div>
                    <div class="flex gap-1">
                        @php
                            $priorityColors = [
                                'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                            ];
                            $statusColors = [
                                'pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                'assigned' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $priorityColors[$request->priority] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($request->priority) }}
                        </span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                        </span>
                    </div>
                </div>
                
                <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">{{ $request->customer->name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $request->equipment->name ?? 'N/A' }}</div>
                
                <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
                    <span>{{ $request->scheduled_date ? $request->scheduled_date->format('M d, Y') : 'Not scheduled' }}</span>
                    <span>{{ $request->technician->name ?? 'Unassigned' }}</span>
                </div>
                
                <div class="flex gap-2 mt-3" @click.stop>
                    @can('view', $request)
                    <a href="{{ route('service-requests.show', $request) }}" class="flex-1 text-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded">
                        View
                    </a>
                    @endcan
                    
                    @can('update', $request)
                    <a href="{{ route('service-requests.edit', $request) }}" class="flex-1 text-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded">
                        Edit
                    </a>
                    @endcan
                    
                    @can('delete', $request)
                    <form action="{{ route('service-requests.destroy', $request) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure?');"
                          class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded">
                            Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $serviceRequests->links() }}
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No service requests found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new service request.</p>
            @can('create', App\Models\ServiceRequest::class)
            <div class="mt-6">
                <a href="{{ route('service-requests.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Request
                </a>
            </div>
            @endcan
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function serviceRequestsFilter() {
    return {
        filtersExpanded: false,
        loading: false,
        showBulkActions: false,
        selectedItems: [],
        perPage: {{ request('per_page', 25) }},
        filters: {
            status: '{{ request('status', '') }}',
            priority: '{{ request('priority', '') }}',
            customer: '{{ request('customer', '') }}',
            technician: '{{ request('technician', '') }}',
            date_from: '{{ request('date_from', '') }}',
            date_to: '{{ request('date_to', '') }}',
            search: '{{ request('search', '') }}'
        },
        
        hasActiveFilters() {
            return Object.values(this.filters).some(value => value !== '');
        },
        
        applyFilters() {
            this.loading = true;
            const params = new URLSearchParams();
            
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.append(key, value);
            });
            
            params.append('per_page', this.perPage);
            
            window.location.href = `{{ route('service-requests.index') }}?${params.toString()}`;
        },
        
        clearFilters() {
            this.filters = {
                status: '',
                priority: '',
                customer: '',
                technician: '',
                date_from: '',
                date_to: '',
                search: ''
            };
            this.applyFilters();
        },
        
        changePerPage() {
            this.applyFilters();
        },
        
        sortBy(column) {
            const params = new URLSearchParams(window.location.search);
            const currentSort = params.get('sort');
            const currentDirection = params.get('direction');
            
            if (currentSort === column && currentDirection === 'asc') {
                params.set('direction', 'desc');
            } else {
                params.set('sort', column);
                params.set('direction', 'asc');
            }
            
            window.location.href = `{{ route('service-requests.index') }}?${params.toString()}`;
        },
        
        toggleItem(id) {
            const index = this.selectedItems.indexOf(id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(id);
            }
        },
        
        toggleAll(event) {
            if (event.target.checked) {
                this.selectedItems = [
                    @foreach($serviceRequests as $request)
                    {{ $request->id }},
                    @endforeach
                ];
            } else {
                this.selectedItems = [];
            }
        },
        
        bulkAssign() {
            if (this.selectedItems.length === 0) {
                alert('Please select at least one item');
                return;
            }
            // Implement bulk assign logic
            console.log('Bulk assign:', this.selectedItems);
        },
        
        bulkUpdateStatus() {
            if (this.selectedItems.length === 0) {
                alert('Please select at least one item');
                return;
            }
            // Implement bulk update status logic
            console.log('Bulk update status:', this.selectedItems);
        },
        
        bulkDelete() {
            if (this.selectedItems.length === 0) {
                alert('Please select at least one item');
                return;
            }
            
            if (confirm(`Are you sure you want to delete ${this.selectedItems.length} service request(s)?`)) {
                // Implement bulk delete logic
                console.log('Bulk delete:', this.selectedItems);
            }
        },
        
        exportToCSV() {
            const params = new URLSearchParams(window.location.search);
            params.append('export', 'csv');
            window.location.href = `{{ route('service-requests.index') }}?${params.toString()}`;
        }
    }
}
</script>
@endpush
@endsection