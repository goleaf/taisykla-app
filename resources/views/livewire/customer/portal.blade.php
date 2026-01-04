<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Service Portal</h1>
                <p class="text-gray-500">Track your service requests and equipment</p>
            </div>
            
            @can(\App\Support\PermissionCatalog::WORK_ORDERS_CREATE)
                <button
                    wire:click="startNewRequest"
                    class="inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Request Service
                </button>
            @endcan
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Requests</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Active</p>
                        <p class="text-3xl font-bold text-amber-600">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">This Month</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['this_month'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-6">
                @foreach (['overview' => 'Overview', 'requests' => 'My Requests', 'equipment' => 'Equipment'] as $tab => $label)
                    <button
                        wire:click="setTab('{{ $tab }}')"
                        class="pb-3 px-1 font-medium text-sm transition border-b-2 {{ $activeTab === $tab ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab Content --}}
        @if ($activeTab === 'overview')
            {{-- Active Requests --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Active Requests</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($recentRequests as $request)
                        @php
                            $statusColors = [
                                'submitted' => 'bg-gray-100 text-gray-700',
                                'assigned' => 'bg-blue-100 text-blue-700',
                                'in_progress' => 'bg-indigo-100 text-indigo-700',
                            ];
                        @endphp
                        <div class="p-5 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-mono text-gray-400">#{{ $request->id }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 truncate">{{ $request->subject }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Submitted {{ $request->requested_at?->diffForHumans() }}
                                        @if ($request->assignedTo)
                                            • Assigned to {{ $request->assignedTo->name }}
                                        @endif
                                    </p>
                                </div>
                                <a 
                                    href="{{ route('work-orders.show', $request) }}"
                                    class="flex-shrink-0 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                    wire:navigate
                                >
                                    View Details
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p>No active service requests</p>
                            @can(\App\Support\PermissionCatalog::WORK_ORDERS_CREATE)
                                <button wire:click="startNewRequest" class="mt-3 text-indigo-600 font-medium hover:underline">
                                    Submit a new request
                                </button>
                            @endcan
                        </div>
                    @endforelse
                </div>
            </div>

        @elseif ($activeTab === 'requests')
            {{-- Filters --}}
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px]">
                    <input 
                        type="text" 
                        wire:model.debounce.300ms="requestSearch" 
                        placeholder="Search requests..."
                        class="w-full rounded-xl border-gray-300 shadow-sm"
                    >
                </div>
                <select 
                    wire:model="requestStatus" 
                    class="rounded-xl border-gray-300 shadow-sm"
                >
                    <option value="">All Status</option>
                    <option value="submitted">Submitted</option>
                    <option value="assigned">Assigned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            {{-- Requests List --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @forelse ($requests as $request)
                        <x-mobile-work-order-card :work-order="$request" :show-actions="true" />
                    @empty
                        <div class="p-10 text-center text-gray-500">
                            No requests found matching your criteria
                        </div>
                    @endforelse
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $requests->links() }}
                </div>
            </div>

        @elseif ($activeTab === 'equipment')
            {{-- Equipment Search --}}
            <div class="mb-6">
                <input 
                    type="text" 
                    wire:model.debounce.300ms="equipmentSearch" 
                    placeholder="Search equipment..."
                    class="w-full max-w-md rounded-xl border-gray-300 shadow-sm"
                >
            </div>

            {{-- Equipment Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($equipment as $item)
                    @php
                        $statusColors = [
                            'operational' => 'bg-green-100 text-green-700',
                            'needs_maintenance' => 'bg-yellow-100 text-yellow-700',
                            'under_repair' => 'bg-orange-100 text-orange-700',
                            'out_of_service' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                </svg>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                            </span>
                        </div>
                        
                        <h3 class="font-semibold text-gray-900 mb-1">{{ $item->name }}</h3>
                        <p class="text-sm text-gray-500 mb-3">{{ $item->manufacturer }} {{ $item->model }}</p>
                        
                        <div class="text-xs text-gray-400 space-y-1">
                            @if ($item->serial_number)
                                <p>S/N: {{ $item->serial_number }}</p>
                            @endif
                            @if ($item->location_name)
                                <p>📍 {{ $item->location_name }}</p>
                            @endif
                            @if ($item->last_service_at)
                                <p>Last service: {{ $item->last_service_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                        
                        <div class="mt-4 pt-3 border-t border-gray-100 flex gap-2">
                            <a 
                                href="{{ route('equipment.show', $item) }}" 
                                class="flex-1 text-center text-sm font-medium text-indigo-600 hover:bg-indigo-50 py-2 rounded-lg transition"
                                wire:navigate
                            >
                                View Details
                            </a>
                            @can(\App\Support\PermissionCatalog::WORK_ORDERS_CREATE)
                                <button 
                                    wire:click="$set('newRequest.equipment_id', {{ $item->id }}); $call('startNewRequest')"
                                    class="flex-1 text-center text-sm font-medium text-green-600 hover:bg-green-50 py-2 rounded-lg transition"
                                >
                                    Request Service
                                </button>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-10 text-center text-gray-500 bg-white rounded-2xl border border-gray-100">
                        No equipment found
                    </div>
                @endforelse
            </div>
            
            <div class="mt-6">
                {{ $equipment->links() }}
            </div>
        @endif
    </div>

    {{-- New Request Modal --}}
    @if ($showRequestForm)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end md:items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/75 transition-opacity" wire:click="cancelRequest"></div>
                
                <div class="relative bg-white rounded-t-3xl md:rounded-2xl text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg">
                    <form wire:submit.prevent="submitRequest">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="text-xl font-bold text-gray-900">Request Service</h3>
                            <p class="text-sm text-gray-500 mt-1">Submit a new service request</p>
                        </div>
                        
                        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                            {{-- Equipment --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Equipment (Optional)</label>
                                <select 
                                    wire:model="newRequest.equipment_id" 
                                    class="w-full rounded-xl border-gray-300"
                                >
                                    <option value="">Select equipment...</option>
                                    @foreach ($equipmentOptions as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} @if($item->serial_number) - {{ $item->serial_number }} @endif</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- Subject --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Issue Summary *</label>
                                <input 
                                    type="text" 
                                    wire:model="newRequest.subject" 
                                    placeholder="Brief description of the issue"
                                    class="w-full rounded-xl border-gray-300"
                                    required
                                >
                                @error('newRequest.subject') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            {{-- Description --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Details *</label>
                                <textarea 
                                    wire:model="newRequest.description" 
                                    rows="4"
                                    placeholder="Describe the problem in detail..."
                                    class="w-full rounded-xl border-gray-300"
                                    required
                                ></textarea>
                                @error('newRequest.description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            {{-- Priority --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <div class="flex gap-2">
                                    @foreach (['standard' => 'Standard', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                                        <label class="flex-1">
                                            <input 
                                                type="radio" 
                                                wire:model="newRequest.priority" 
                                                value="{{ $value }}"
                                                class="sr-only peer"
                                            >
                                            <div class="text-center py-2 px-3 rounded-xl border-2 cursor-pointer transition peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 border-gray-200 hover:border-gray-300">
                                                {{ $label }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            {{-- Preferred Date/Time --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Date</label>
                                    <input 
                                        type="date" 
                                        wire:model="newRequest.preferred_date"
                                        min="{{ now()->format('Y-m-d') }}"
                                        class="w-full rounded-xl border-gray-300"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time Window</label>
                                    <select wire:model="newRequest.preferred_time" class="w-full rounded-xl border-gray-300">
                                        <option value="">Any time</option>
                                        <option value="morning">Morning (8am-12pm)</option>
                                        <option value="afternoon">Afternoon (12pm-5pm)</option>
                                        <option value="evening">Evening (5pm-8pm)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6 bg-gray-50 flex gap-3">
                            <button
                                type="button"
                                wire:click="cancelRequest"
                                class="flex-1 py-3 px-4 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-100 transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex-1 py-3 px-4 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition"
                            >
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
