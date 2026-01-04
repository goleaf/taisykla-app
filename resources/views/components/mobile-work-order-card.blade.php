@props([
    'workOrder',
    'showActions' => true,
    'compact' => false
])

@php
    $priorityStyles = [
        'urgent' => 'border-l-red-500 bg-red-50',
        'high' => 'border-l-orange-400 bg-orange-50',
        'standard' => 'border-l-blue-400 bg-white',
    ];
    
    $statusStyles = [
        'submitted' => 'bg-gray-100 text-gray-700',
        'assigned' => 'bg-blue-100 text-blue-700',
        'in_progress' => 'bg-indigo-100 text-indigo-700',
        'on_hold' => 'bg-yellow-100 text-yellow-700',
        'completed' => 'bg-green-100 text-green-700',
        'closed' => 'bg-emerald-100 text-emerald-700',
        'canceled' => 'bg-red-100 text-red-700',
    ];
    
    $cardStyle = $priorityStyles[$workOrder->priority] ?? 'border-l-gray-300 bg-white';
    $statusBadge = $statusStyles[$workOrder->status] ?? 'bg-gray-100 text-gray-700';
@endphp

<div 
    class="border-l-4 rounded-lg shadow-sm {{ $cardStyle }} active:scale-[0.98] transition-transform"
    {{ $attributes }}
>
    {{-- Swipeable container for mobile actions --}}
    <div class="relative overflow-hidden">
        {{-- Main Card Content --}}
        <div class="p-4 space-y-3">
            {{-- Header Row --}}
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-gray-400">#{{ $workOrder->id }}</span>
                        @if ($workOrder->priority === 'urgent')
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-900 truncate">{{ $workOrder->subject }}</h3>
                </div>
                
                <span class="flex-shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusBadge }}">
                    {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                </span>
            </div>
            
            {{-- Customer & Location --}}
            <div class="space-y-1">
                @if ($workOrder->organization)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="truncate">{{ $workOrder->organization->name }}</span>
                    </div>
                @endif
                
                @if ($workOrder->location_address)
                    <a 
                        href="https://maps.google.com/?q={{ urlencode($workOrder->location_address) }}"
                        target="_blank"
                        class="flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate">{{ $workOrder->location_address }}</span>
                    </a>
                @endif
            </div>
            
            @unless ($compact)
                {{-- Schedule Info --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                    @if ($workOrder->scheduled_start_at)
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $workOrder->scheduled_start_at->format('M d') }}
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $workOrder->scheduled_start_at->format('g:i A') }}
                            @if ($workOrder->time_window)
                                ({{ $workOrder->time_window }})
                            @endif
                        </div>
                    @else
                        <span class="text-amber-600">Not scheduled</span>
                    @endif
                    
                    @if ($workOrder->estimated_minutes)
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Est. {{ $workOrder->estimated_minutes }} min
                        </div>
                    @endif
                </div>
                
                {{-- Assigned Technician --}}
                @if ($workOrder->assignedTo)
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-medium">
                            {{ strtoupper(substr($workOrder->assignedTo->name, 0, 1)) }}
                        </div>
                        <span class="text-sm text-gray-700">{{ $workOrder->assignedTo->name }}</span>
                    </div>
                @endif
            @endunless
            
            {{-- Quick Actions --}}
            @if ($showActions)
                <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100">
                    <a 
                        href="{{ route('work-orders.show', $workOrder) }}"
                        class="flex-1 min-w-[100px] inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-sm font-medium text-indigo-700 bg-indigo-50 rounded-lg hover:bg-indigo-100 active:bg-indigo-200 transition"
                        wire:navigate
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View
                    </a>
                    
                    @if ($workOrder->status === 'assigned' || $workOrder->status === 'in_progress')
                        @if ($workOrder->location_latitude && $workOrder->location_longitude)
                            <a 
                                href="https://www.google.com/maps/dir/?api=1&destination={{ $workOrder->location_latitude }},{{ $workOrder->location_longitude }}"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-sm font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 active:bg-green-200 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                Navigate
                            </a>
                        @endif
                        
                        <a 
                            href="tel:{{ $workOrder->organization?->primary_contact_phone }}"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 active:bg-blue-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Call
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
