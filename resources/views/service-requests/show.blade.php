@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="serviceRequestDetail()">
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Title and Badges --}}
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Request #{{ str_pad($serviceRequest->id, 5, '0', STR_PAD_LEFT) }}</h1>
                    
                    {{-- Status Badge --}}
                    @php
                        $statusColors = [
                            'pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                            'assigned' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        ];
                        $priorityColors = [
                            'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        ];
                    @endphp
                    
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$serviceRequest->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}
                    </span>
                    
                    {{-- Priority Badge --}}
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $priorityColors[$serviceRequest->priority] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($serviceRequest->priority) }}
                    </span>
                    
                    @if($serviceRequest->isOverdue())
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-600 text-white animate-pulse">
                        Overdue
                    </span>
                    @endif
                </div>
                
                {{-- Breadcrumbs --}}
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ route('service-requests.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">Service Requests</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">#{{ str_pad($serviceRequest->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('service-requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium rounded-lg transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
                
                @can('update', $serviceRequest)
                <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                @endcan
                
                @can('delete', $serviceRequest)
                <form action="{{ route('service-requests.destroy', $serviceRequest) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this service request?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </button>
                </form>
                @endcan
                
                <button @click="window.print()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 print:hidden">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print / PDF
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content Column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Main Information Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Request Details</h2>
                </div>
                
                <div class="p-6 space-y-6">
                    {{-- Customer Information --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Customer Information</h3>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-lg font-semibold">{{ substr($serviceRequest->customer->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <a href="#" class="text-lg font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $serviceRequest->customer->name }}
                                    </a>
                                    @if($serviceRequest->customer->email)
                                    <div class="flex items-center gap-2 mt-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <a href="mailto:{{ $serviceRequest->customer->email }}" class="text-sm text-gray-600 dark:text-gray-300 hover:text-blue-600">
                                            {{ $serviceRequest->customer->email }}
                                        </a>
                                    </div>
                                    @endif
                                    @if($serviceRequest->customer->phone)
                                    <div class="flex items-center gap-2 mt-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <a href="tel:{{ $serviceRequest->customer->phone }}" class="text-sm text-gray-600 dark:text-gray-300 hover:text-blue-600">
                                            {{ $serviceRequest->customer->phone }}
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Equipment Information --}}
                    @if($serviceRequest->equipment)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Equipment Information</h3>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $serviceRequest->equipment->type ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Manufacturer</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $serviceRequest->equipment->manufacturer ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Model</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $serviceRequest->equipment->model ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Serial Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $serviceRequest->equipment->serial_number ?? 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    @endif

                    {{-- Description --}}
                    @if($serviceRequest->description)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Description</h3>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $serviceRequest->description }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Schedule & Time Information --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Schedule & Time</h3>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Scheduled Date
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">
                                        {{ $serviceRequest->scheduled_at ? $serviceRequest->scheduled_at->format('M d, Y h:i A') : 'Not scheduled' }}
                                    </dd>
                                </div>
                                @if($serviceRequest->started_at)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Started At</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $serviceRequest->started_at->format('M d, Y h:i A') }}</dd>
                                </div>
                                @endif
                                @if($serviceRequest->completed_at)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Completed At</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ $serviceRequest->completed_at->format('M d, Y h:i A') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Hours & Cost Comparison --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Hours & Cost Analysis</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Hours Card --}}
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase">Hours</span>
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Estimated:</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($serviceRequest->estimated_hours ?? 0, 2) }}h</span>
                                    </div>
                                    @if($serviceRequest->actual_hours)
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Actual:</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($serviceRequest->actual_hours, 2) }}h</span>
                                    </div>
                                    <div class="pt-2 border-t border-blue-200 dark:border-blue-800">
                                        @php
                                            $hoursDiff = $serviceRequest->actual_hours - ($serviceRequest->estimated_hours ?? 0);
                                            $hoursVariance = $serviceRequest->estimated_hours > 0 
                                                ? (($hoursDiff / $serviceRequest->estimated_hours) * 100) 
                                                : 0;
                                        @endphp
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-gray-600 dark:text-gray-300">Variance:</span>
                                            <span class="text-sm font-semibold {{ $hoursDiff > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                {{ $hoursDiff > 0 ? '+' : '' }}{{ number_format($hoursDiff, 2) }}h ({{ number_format($hoursVariance, 1) }}%)
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Cost Card --}}
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-green-600 dark:text-green-400 uppercase">Cost</span>
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Estimated:</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($serviceRequest->estimated_cost, 2) }}</span>
                                    </div>
                                    @if($serviceRequest->actual_cost > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Actual:</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($serviceRequest->actual_cost, 2) }}</span>
                                    </div>
                                    <div class="pt-2 border-t border-green-200 dark:border-green-800">
                                        @php
                                            $costDiff = $serviceRequest->actual_cost - $serviceRequest->estimated_cost;
                                            $costVariance = $serviceRequest->estimated_cost > 0 
                                                ? (($costDiff / $serviceRequest->estimated_cost) * 100) 
                                                : 0;
                                        @endphp
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-gray-600 dark:text-gray-300">Variance:</span>
                                            <span class="text-sm font-semibold {{ $costDiff > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                {{ $costDiff > 0 ? '+' : '' }}${{ number_format($costDiff, 2) }} ({{ number_format($costVariance, 1) }}%)
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden" x-data="{ activeTab: 'customer' }">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Notes</h2>
                </div>
                
                {{-- Tabs --}}
                <div class="border-b border-gray-200 dark:border-gray-600">
                    <nav class="flex -mb-px">
                        <button @click="activeTab = 'customer'" 
                                :class="activeTab === 'customer' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                                class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                            Customer Notes
                        </button>
                        <button @click="activeTab = 'technician'" 
                                :class="activeTab === 'technician' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                                class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                            Technician Notes
                        </button>
                        <button @click="activeTab = 'internal'" 
                                :class="activeTab === 'internal' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                                class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                            Internal Notes
                        </button>
                    </nav>
                </div>
                
                {{-- Tab Content --}}
                <div class="p-6">
                    <div x-show="activeTab === 'customer'" class="space-y-4">
                        @if($serviceRequest->customer_notes)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $serviceRequest->customer_notes }}</p>
                        </div>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No customer notes available.</p>
                        @endif
                    </div>
                    
                    <div x-show="activeTab === 'technician'" class="space-y-4">
                        @if($serviceRequest->technician_notes)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $serviceRequest->technician_notes }}</p>
                        </div>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No technician notes available.</p>
                        @endif
                    </div>
                    
                    <div x-show="activeTab === 'internal'" class="space-y-4">
                        @if($serviceRequest->internal_notes)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $serviceRequest->internal_notes }}</p>
                        </div>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No internal notes available.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Attachments Section --}}
            @if($serviceRequest->attachments && $serviceRequest->attachments->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Attachments</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($serviceRequest->attachments as $attachment)
                        <div class="relative group">
                            <div class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                                @if(str_starts_with($attachment->mime_type ?? '', 'image/'))
                                <img src="{{ $attachment->url }}" alt="{{ $attachment->filename }}" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 truncate">{{ $attachment->filename }}</p>
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                <a href="{{ $attachment->url }}" download class="p-1 bg-blue-600 hover:bg-blue-700 text-white rounded shadow-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Parts Used Section --}}
            @if($serviceRequest->status === 'completed' && $serviceRequest->items && $serviceRequest->items->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Parts Used</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Part Name</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit Cost</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @php $totalPartsCost = 0; @endphp
                            @foreach($serviceRequest->items as $item)
                            @php 
                                $itemTotal = $item->quantity * $item->unit_price;
                                $totalPartsCost += $itemTotal;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item->part_name ?? $item->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white text-right">${{ number_format($itemTotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white text-right">Total Parts Cost:</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white text-right">${{ number_format($totalPartsCost, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

            {{-- Activity Log Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden print:break-inside-avoid">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Activity Log</h2>
                </div>
                
                <div class="p-6 max-h-96 overflow-y-auto">
                    @if($serviceRequest->activityLogs && $serviceRequest->activityLogs->count() > 0)
                    <div class="space-y-4">
                        @foreach($serviceRequest->activityLogs as $log)
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->description }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            @if($log->causer)
                                            by {{ $log->causer->name }} • 
                                            @endif
                                            {{ $log->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $log->created_at->format('M d, h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No activity logs available.</p>
                    @endif
                </div>
            </div>

            {{-- Context-Aware Action Buttons --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden print:hidden">
                <div class="p-6">
                    <div class="flex flex-wrap gap-3">
                        @if($serviceRequest->status === 'pending')
                            @can('approve', $serviceRequest)
                            <form action="{{ route('service-requests.approve', $serviceRequest) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Approve Request
                                </button>
                            </form>
                            @endcan
                            
                            @can('reject', $serviceRequest)
                            <form action="{{ route('service-requests.reject', $serviceRequest) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this request? Please provide a reason.');" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Reject Request
                                </button>
                            </form>
                            @endcan
                        @endif

                        @if($serviceRequest->status === 'assigned')
                            @if(auth()->user()->id === $serviceRequest->technician_id)
                            <form action="{{ route('service-requests.update-status', $serviceRequest) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Start Work
                                </button>
                            </form>
                            @endif
                        @endif

                        @if($serviceRequest->status === 'in_progress')
                            @if(auth()->user()->id === $serviceRequest->technician_id)
                            <form action="{{ route('service-requests.update-status', $serviceRequest) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Complete Work
                                </button>
                            </form>
                            @endif
                        @endif

                        @if($serviceRequest->status === 'completed')
                            <button disabled class="inline-flex items-center px-6 py-3 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                                Rate Service (Coming Soon)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Column --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Status Timeline --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Status Timeline</h2>
                </div>
                <div class="p-6">
                    @if($serviceRequest->activityLogs && $serviceRequest->activityLogs->where('event', 'status_changed')->count() > 0)
                    <div class="relative">
                        <div class="absolute top-0 bottom-0 left-4 w-0.5 bg-gray-200 dark:bg-gray-600"></div>
                        <div class="space-y-6">
                            @foreach($serviceRequest->activityLogs->where('event', 'status_changed')->sortByDesc('created_at') as $statusLog)
                            <div class="relative flex items-start gap-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $loop->first ? 'bg-blue-600' : 'bg-gray-400 dark:bg-gray-600' }} flex items-center justify-center z-10">
                                    @if($loop->first)
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    @else
                                    <div class="w-3 h-3 bg-white rounded-full"></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0 pb-6">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ ucfirst(str_replace('_', ' ', $statusLog->properties['new_status'] ?? '')) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $statusLog->created_at->format('M d, Y h:i A') }}
                                    </p>
                                    @if($statusLog->causer)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        by {{ $statusLog->causer->name }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No status changes recorded.</p>
                    @endif
                </div>
            </div>

            {{-- Assignment Section --}}
            @if($serviceRequest->technician)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Assigned Technician</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-2xl font-semibold">{{ substr($serviceRequest->technician->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $serviceRequest->technician->name }}</h3>
                            @if($serviceRequest->technician->email)
                            <a href="mailto:{{ $serviceRequest->technician->email }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 block">
                                {{ $serviceRequest->technician->email }}
                            </a>
                            @endif
                            @if($serviceRequest->technician->phone)
                            <a href="tel:{{ $serviceRequest->technician->phone }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 block">
                                {{ $serviceRequest->technician->phone }}
                            </a>
                            @endif
                        </div>
                    </div>
                    
                    @can('assign', $serviceRequest)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="block text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150">
                            Reassign Technician
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Assignment</h2>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No technician assigned yet.</p>
                    @can('assign', $serviceRequest)
                    <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="block text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150">
                        Assign Technician
                    </a>
                    @endcan
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function serviceRequestDetail() {
    return {
        showRejectModal: false,
        
        init() {
            console.log('Service Request Detail initialized');
            
            // Placeholder for real-time updates with Laravel Echo
            // Uncomment when Echo is configured:
            // window.Echo.private('service-request.{{ $serviceRequest->id }}')
            //     .listen('ServiceRequestUpdated', (e) => {
            //         console.log('Service request updated:', e);
            //         location.reload();
            //     });
        }
    }
}
</script>
@endpush

{{-- Print-friendly CSS --}}
<style>
@media print {
    .print\:hidden {
        display: none !important;
    }
    
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .dark\:bg-gray-800,
    .dark\:bg-gray-700 {
        background-color: #fff !important;
    }
    
    .shadow-md {
        box-shadow: none !important;
    }
    
    /* Avoid page breaks inside sections */
    .print\:break-inside-avoid {
        page-break-inside: avoid;
    }
    
    /* Grid layout for print */
    .lg\:grid-cols-3 {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection