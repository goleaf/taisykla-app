<div x-data="{ 
    isOpen: @entangle('isOpen'),
    open() { $wire.open() },
    close() { $wire.close() }
}" 
@keydown.window.prevent.cmd.k="open()"
@keydown.window.prevent.ctrl.k="open()"
@keydown.escape.window="close()"
class="relative">

    <!-- Trigger -->
    <button @click="open()" class="flex items-center gap-2 px-3 py-1.5 text-gray-400 bg-gray-50 border border-gray-300 rounded-md hover:text-gray-500 hover:border-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors w-64">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <span class="text-sm">Search...</span>
        <span class="ml-auto text-xs text-gray-400 border border-gray-200 rounded px-1.5 py-0.5">⌘K</span>
    </button>

    <!-- Modal Backdrop -->
    <div x-show="isOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-gray-500 bg-opacity-75 transition-opacity" 
        style="display: none;"></div>

    <!-- Modal Content -->
    <div x-show="isOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="close()"
        class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20" 
        style="display: none;">

        <div class="mx-auto max-w-2xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 transition-all">
            <div class="relative">
                <svg class="pointer-events-none absolute top-3.5 left-4 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input x-ref="searchInput" 
                    wire:model.live.debounce.300ms="query" 
                    type="text" 
                    class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 placeholder-gray-500 focus:ring-0 sm:text-sm" 
                    placeholder="Search orders, customers, equipment..." 
                    role="combobox" 
                    aria-expanded="false" 
                    aria-controls="options">
            </div>

            @if(strlen($query) >= 2)
                @if(empty($this->results['workOrders']) && empty($this->results['customers']) && empty($this->results['equipment']))
                    <div class="p-4 text-center text-sm text-gray-500">
                        No results found for "{{ $query }}".
                    </div>
                @else
                    <ul class="max-h-96 scroll-py-2 overflow-y-auto py-2 text-sm text-gray-800" id="options" role="listbox">
                        
                        @if(count($this->results['workOrders']) > 0)
                            <li class="px-4 py-2 text-xs font-semibold text-gray-500 bg-gray-50">Work Orders</li>
                            @foreach($this->results['workOrders'] as $wo)
                                <li class="group cursor-pointer select-none px-4 py-2 hover:bg-indigo-600 hover:text-white" role="option" tabindex="-1">
                                    <a href="{{ route('work-orders.show', $wo->id) }}" class="block">
                                        <div class="font-medium">#{{ $wo->id }} - {{ $wo->subject }}</div>
                                        <div class="text-xs text-gray-500 group-hover:text-indigo-200 truncate">{{ $wo->description }}</div>
                                    </a>
                                </li>
                            @endforeach
                        @endif

                        @if(count($this->results['customers']) > 0)
                            <li class="px-4 py-2 text-xs font-semibold text-gray-500 bg-gray-50 mt-2">Customers</li>
                            @foreach($this->results['customers'] as $customer)
                                <li class="group cursor-pointer select-none px-4 py-2 hover:bg-indigo-600 hover:text-white" role="option" tabindex="-1">
                                    <a href="{{ route('clients.show', $customer->id) }}" class="block">
                                        <div class="font-medium">{{ $customer->name }}</div>
                                        <div class="text-xs text-gray-500 group-hover:text-indigo-200">
                                            {{ $customer->primary_contact_name }} @if($customer->primary_contact_email) &middot; {{ $customer->primary_contact_email }} @endif
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        @endif

                        @if(count($this->results['equipment']) > 0)
                            <li class="px-4 py-2 text-xs font-semibold text-gray-500 bg-gray-50 mt-2">Equipment</li>
                            @foreach($this->results['equipment'] as $eq)
                                <li class="group cursor-pointer select-none px-4 py-2 hover:bg-indigo-600 hover:text-white" role="option" tabindex="-1">
                                    <a href="{{ route('equipment.show', $eq->id) }}" class="block">
                                        <div class="font-medium">{{ $eq->name }}</div>
                                        <div class="text-xs text-gray-500 group-hover:text-indigo-200">
                                            SN: {{ $eq->serial_number }} @if($eq->model) &middot; Model: {{ $eq->model }} @endif
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        @endif

                    </ul>
                @endif
            @endif
            
             <div class="bg-gray-50 px-4 py-3 text-xs text-gray-500 border-t border-gray-100 flex justify-between">
                <span>Type 2+ characters to search</span>
                <span>ESC to close</span>
            </div>
        </div>
    </div>
</div>
