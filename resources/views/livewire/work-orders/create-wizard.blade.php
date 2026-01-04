<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-purple-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('work-orders.index') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 mb-4" wire:navigate>
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Work Orders
            </a>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Create Work Order</h1>
            <p class="text-gray-500 mt-1">Complete the wizard to submit your service request</p>
        </div>

        {{-- Progress Steps --}}
        <div class="mb-8">
            <div class="flex items-center justify-between relative">
                <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200"></div>
                <div class="absolute top-5 left-0 h-0.5 bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-500" style="width: {{ (($currentStep - 1) / ($totalSteps - 1)) * 100 }}%"></div>
                @foreach($stepTitles as $step => $title)
                    <button wire:click="goToStep({{ $step }})" class="relative z-10 flex flex-col items-center group">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300 {{ $step < $currentStep ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg' : ($step === $currentStep ? 'bg-white border-2 border-indigo-600 text-indigo-600 shadow-lg ring-4 ring-indigo-100' : 'bg-white border-2 border-gray-300 text-gray-400') }}">
                            @if($step < $currentStep)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                {{ $step }}
                            @endif
                        </div>
                        <span class="mt-2 text-xs font-medium {{ $step <= $currentStep ? 'text-indigo-600' : 'text-gray-400' }}">{{ $title }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Wizard Content --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-6 sm:p-8">
                {{-- Step 1: Customer Selection --}}
                @if($currentStep === 1)
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-indigo-100 rounded-xl"><svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Select Customer</h2>
                                <p class="text-sm text-gray-500">Choose the organization for this service request</p>
                            </div>
                        </div>

                        @if(!$isClient)
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="customerSearch" class="w-full pl-10 pr-4 py-3 rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Search customers...">
                                <svg class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                                @forelse($organizations as $org)
                                    <button type="button" wire:click="selectCustomer({{ $org->id }})" class="p-4 rounded-xl border-2 text-left transition-all duration-200 {{ $selectedOrganizationId === $org->id ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50' }}">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-semibold">{{ strtoupper(substr($org->name, 0, 1)) }}</div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-gray-900 truncate">{{ $org->name }}</h3>
                                                <p class="text-sm text-gray-500">{{ $org->type ?? 'Customer' }}</p>
                                                @if($org->serviceAgreement)
                                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $org->serviceAgreement->name ?? 'Active SLA' }}</span>
                                                @endif
                                            </div>
                                            @if($selectedOrganizationId === $org->id)
                                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            @endif
                                        </div>
                                    </button>
                                @empty
                                    <div class="col-span-2 text-center py-8 text-gray-500">No customers found</div>
                                @endforelse
                            </div>
                        @endif

                        @if($selectedOrganization)
                            <div class="mt-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl">
                                <h3 class="font-semibold text-gray-900 mb-3">Recent Service History</h3>
                                @forelse($recentHistory->take(3) as $wo)
                                    <div class="flex items-center justify-between py-2 border-b border-indigo-100 last:border-0">
                                        <span class="text-sm text-gray-600">#{{ $wo->id }} - {{ Str::limit($wo->subject, 30) }}</span>
                                        <span class="text-xs px-2 py-1 rounded-full {{ $wo->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($wo->status) }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No previous service history</p>
                                @endforelse
                            </div>
                        @endif
                        @error('selectedOrganizationId') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                    </div>

                {{-- Step 2: Equipment Selection --}}
                @elseif($currentStep === 2)
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-purple-100 rounded-xl"><svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg></div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Select Equipment</h2>
                                <p class="text-sm text-gray-500">Choose the equipment requiring service</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input type="text" wire:model.live.debounce.300ms="equipmentSearch" class="md:col-span-2 px-4 py-2.5 rounded-lg border-gray-200" placeholder="Search equipment...">
                            <select wire:model.live="equipmentLocationFilter" class="px-4 py-2.5 rounded-lg border-gray-200">
                                <option value="">All locations</option>
                                @foreach($equipmentLocations as $loc)<option value="{{ $loc }}">{{ $loc }}</option>@endforeach
                            </select>
                            <select wire:model.live="equipmentStatusFilter" class="px-4 py-2.5 rounded-lg border-gray-200">
                                <option value="">All statuses</option>
                                @foreach($equipmentStatuses as $stat)<option value="{{ $stat }}">{{ $stat }}</option>@endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[28rem] overflow-y-auto">
                            @forelse($equipment as $eq)
                                @php $metrics = $equipmentMetrics[$eq->id] ?? []; @endphp
                                <button type="button" wire:click="selectEquipment({{ $eq->id }})" class="p-4 rounded-xl border-2 text-left transition-all {{ $selectedEquipmentId === $eq->id ? 'border-purple-600 bg-purple-50 ring-2 ring-purple-200' : 'border-gray-200 hover:border-purple-300' }}">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="font-semibold text-gray-900">{{ $eq->name }}</h3>
                                            <p class="text-sm text-gray-500">{{ $eq->manufacturer }} {{ $eq->model }}</p>
                                        </div>
                                        @if($metrics['health_score'] ?? null)
                                            <div class="text-right">
                                                <div class="text-sm font-semibold {{ ($metrics['health_score'] ?? 0) >= 80 ? 'text-green-600' : (($metrics['health_score'] ?? 0) >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $metrics['health_score'] }}%</div>
                                                <div class="text-xs text-gray-400">Health</div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $eq->location_name ?? 'No location' }}</span>
                                        @if($metrics['has_warranty'] ?? false)<span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Warranty</span>@endif
                                    </div>
                                </button>
                            @empty
                                <div class="col-span-3 text-center py-8 text-gray-500">No equipment found</div>
                            @endforelse
                        </div>
                        @error('selectedEquipmentId') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                    </div>

                {{-- Step 3: Problem Description --}}
                @elseif($currentStep === 3)
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-orange-100 rounded-xl"><svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Describe the Problem</h2>
                                <p class="text-sm text-gray-500">Provide details about the issue</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Templates</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($problemTemplates as $key => $template)
                                    <button type="button" wire:click="$set('problemTemplate', '{{ $key }}')" class="px-3 py-1.5 rounded-full text-sm {{ $problemTemplate === $key ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">{{ $template['label'] }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                            <select wire:model="categoryId" class="w-full rounded-lg border-gray-200">
                                <option value="">Select category</option>
                                @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                            </select>
                            @error('categoryId') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="subject" class="w-full rounded-lg border-gray-200" placeholder="Brief summary of the issue">
                            @error('subject') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <div class="flex justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                                <span class="text-xs text-gray-400">{{ strlen($description) }}/2000</span>
                            </div>
                            <textarea wire:model="description" rows="5" class="w-full rounded-lg border-gray-200" placeholder="Describe the problem in detail..."></textarea>
                            @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Attachments</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 transition-colors">
                                <input type="file" wire:model="issueMedia" multiple accept="image/*,video/*" class="hidden" id="media-upload">
                                <label for="media-upload" class="cursor-pointer">
                                    <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="mt-2 text-sm text-gray-600">Drag & drop or click to upload</p>
                                </label>
                            </div>
                            @if(count($issueMedia) > 0)
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($issueMedia as $i => $media)
                                        <div class="relative">
                                            <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                                                @if(str_starts_with($media->getMimeType(), 'image/'))
                                                    <img src="{{ $media->temporaryUrl() }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="removeMedia({{ $i }})" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center">×</button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                {{-- Step 4: Priority & Scheduling --}}
                @elseif($currentStep === 4)
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-blue-100 rounded-xl"><svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Priority & Schedule</h2>
                                <p class="text-sm text-gray-500">Set priority level and preferred timing</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Priority Level</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($priorityOptions as $key => $opt)
                                    <button type="button" wire:click="selectPriority('{{ $key }}')" class="p-4 rounded-xl border-2 text-left transition-all {{ $priority === $key ? 'border-blue-600 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-200 hover:border-blue-300' }}">
                                        <div class="text-2xl mb-2">{{ $opt['icon'] }}</div>
                                        <h3 class="font-semibold text-gray-900">{{ $opt['label'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ $opt['sla'] }}</p>
                                        <p class="text-sm font-medium text-indigo-600 mt-1">{{ $opt['cost'] }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Select Date</label>
                            <div class="grid grid-cols-7 gap-2">
                                @foreach(array_slice($availabilityDays, 0, 14) as $day)
                                    <button type="button" wire:click="$set('scheduledDate', '{{ $day['date'] }}')" class="p-2 rounded-lg text-center transition-all {{ $scheduledDate === $day['date'] ? 'bg-blue-600 text-white' : ($day['status'] === 'full' ? 'bg-red-50 text-red-400 cursor-not-allowed' : 'bg-gray-50 hover:bg-blue-50') }}" {{ $day['status'] === 'full' ? 'disabled' : '' }}>
                                        <div class="text-xs {{ $scheduledDate === $day['date'] ? 'text-blue-100' : 'text-gray-400' }}">{{ $day['day_short'] }}</div>
                                        <div class="text-lg font-semibold">{{ $day['day_num'] }}</div>
                                        <div class="text-xs {{ $day['status'] === 'open' ? 'text-green-600' : ($day['status'] === 'full' ? 'text-red-500' : 'text-yellow-600') }}">{{ $day['slots'] }}</div>
                                    </button>
                                @endforeach
                            </div>
                            @error('scheduledDate') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Time Window</label>
                            <div class="grid grid-cols-3 gap-4">
                                @foreach(['morning' => 'Morning (8AM-12PM)', 'afternoon' => 'Afternoon (12PM-5PM)', 'specific' => 'Specific Time'] as $key => $label)
                                    <button type="button" wire:click="$set('timeWindowPreset', '{{ $key }}')" class="p-3 rounded-lg border-2 text-center {{ $timeWindowPreset === $key ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">{{ $label }}</button>
                                @endforeach
                            </div>
                            @if($timeWindowPreset === 'specific')
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-600">Start Time</label>
                                        <input type="time" wire:model="scheduledTime" class="w-full rounded-lg border-gray-200">
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-600">End Time (optional)</label>
                                        <input type="time" wire:model="scheduledEndTime" class="w-full rounded-lg border-gray-200">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Access Requirements</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($accessRequirementOptions as $key => $label)
                                    <button type="button" wire:click="toggleAccessRequirement('{{ $key }}')" class="px-3 py-1.5 rounded-full text-sm {{ in_array($key, $selectedAccessRequirements) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                            <textarea wire:model="specialInstructions" rows="3" class="w-full rounded-lg border-gray-200" placeholder="Any special access codes, parking instructions, or notes..."></textarea>
                        </div>
                    </div>

                {{-- Step 5: Assignment --}}
                @elseif($currentStep === 5)
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-green-100 rounded-xl"><svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Assign Technician</h2>
                                <p class="text-sm text-gray-500">{{ $canAssign ? 'Select a technician or leave unassigned' : 'A technician will be assigned by dispatch' }}</p>
                            </div>
                        </div>

                        @if($canAssign)
                            @if($recommendedTechnician)
                                <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span class="font-semibold text-green-800">Recommended</span>
                                    </div>
                                    <button type="button" wire:click="selectTechnician({{ $recommendedTechnician->id }})" class="w-full p-4 bg-white rounded-lg border-2 {{ $assignedTechnicianId === $recommendedTechnician->id ? 'border-green-600' : 'border-transparent' }}">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-full bg-green-500 text-white flex items-center justify-center font-semibold">{{ strtoupper(substr($recommendedTechnician->name, 0, 2)) }}</div>
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900">{{ $recommendedTechnician->name }}</h3>
                                                <p class="text-sm text-gray-500">{{ $recommendedTechnician->job_title ?? 'Field Technician' }}</p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-bold text-green-600">{{ $technicianMatches[$recommendedTechnician->id]['score'] ?? 0 }}%</div>
                                                <div class="text-xs text-gray-500">Match</div>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($technicians->where('id', '!=', $recommendedTechnician?->id) as $tech)
                                    @php $match = $technicianMatches[$tech->id] ?? []; @endphp
                                    <button type="button" wire:click="selectTechnician({{ $tech->id }})" class="p-4 rounded-xl border-2 text-left {{ $assignedTechnicianId === $tech->id ? 'border-green-600 bg-green-50' : 'border-gray-200 hover:border-green-300' }}">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-semibold text-gray-600">{{ strtoupper(substr($tech->name, 0, 2)) }}</div>
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900">{{ $tech->name }}</h3>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full {{ ($match['is_available'] ?? false) ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                                    <span class="text-xs text-gray-500">{{ ucfirst($match['availability'] ?? 'Unknown') }}</span>
                                                </div>
                                            </div>
                                            <div class="text-sm font-medium {{ ($match['score'] ?? 0) >= 80 ? 'text-green-600' : 'text-gray-500' }}">{{ $match['score'] ?? 0 }}%</div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            <button type="button" wire:click="selectTechnician(null)" class="w-full p-4 rounded-xl border-2 border-dashed {{ $assignedTechnicianId === null ? 'border-gray-400 bg-gray-50' : 'border-gray-300' }}">
                                <p class="text-center text-gray-600">Leave unassigned for dispatch to assign</p>
                            </button>
                        @else
                            <div class="p-6 bg-gray-50 rounded-xl text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <p class="mt-4 text-gray-600">Our dispatch team will assign the best available technician for your request</p>
                            </div>
                        @endif
                    </div>

                {{-- Step 6: Review & Submit --}}
                @elseif($currentStep === 6)
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-indigo-100 rounded-xl"><svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Review & Submit</h2>
                                <p class="text-sm text-gray-500">Confirm your work order details</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Summary Cards --}}
                            <div class="space-y-4">
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <div class="flex justify-between items-center mb-2"><h3 class="font-semibold text-gray-900">Customer</h3><button type="button" wire:click="goToStep(1)" class="text-indigo-600 text-sm">Edit</button></div>
                                    <p class="text-gray-700">{{ $selectedOrganization?->name ?? 'Not selected' }}</p>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <div class="flex justify-between items-center mb-2"><h3 class="font-semibold text-gray-900">Equipment</h3><button type="button" wire:click="goToStep(2)" class="text-indigo-600 text-sm">Edit</button></div>
                                    <p class="text-gray-700">{{ $selectedEquipment?->name ?? 'Not selected' }}</p>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <div class="flex justify-between items-center mb-2"><h3 class="font-semibold text-gray-900">Problem</h3><button type="button" wire:click="goToStep(3)" class="text-indigo-600 text-sm">Edit</button></div>
                                    <p class="font-medium text-gray-900">{{ $subject }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($description, 100) }}</p>
                                    @if(count($issueMedia))<p class="text-xs text-gray-500 mt-2">{{ count($issueMedia) }} attachment(s)</p>@endif
                                </div>
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <div class="flex justify-between items-center mb-2"><h3 class="font-semibold text-gray-900">Schedule</h3><button type="button" wire:click="goToStep(4)" class="text-indigo-600 text-sm">Edit</button></div>
                                    <p class="text-gray-700">{{ $scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('l, M j, Y') : 'Not scheduled' }}</p>
                                    <p class="text-sm text-gray-500">{{ ucfirst($timeWindowPreset) }} • {{ ucfirst($priority) }} Priority</p>
                                </div>
                            </div>

                            {{-- Cost Estimate --}}
                            <div class="space-y-4">
                                <div class="p-4 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl">
                                    <h3 class="font-semibold text-gray-900 mb-4">Cost Estimate</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-600">Labor ({{ ucfirst($priority) }})</span><span>€{{ number_format($estimatedCost['labor'], 2) }}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Trip Fee</span><span>€{{ number_format($estimatedCost['trip_fee'], 2) }}</span></div>
                                        @if($estimatedCost['attachments'] > 0)<div class="flex justify-between"><span class="text-gray-600">Documentation</span><span>€{{ number_format($estimatedCost['attachments'], 2) }}</span></div>@endif
                                        <div class="border-t border-gray-200 pt-2 flex justify-between"><span class="text-gray-600">Subtotal</span><span>€{{ number_format($estimatedCost['subtotal'], 2) }}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">VAT (21%)</span><span>€{{ number_format($estimatedCost['tax'], 2) }}</span></div>
                                        <div class="border-t border-gray-300 pt-2 flex justify-between font-semibold text-lg"><span>Estimated Total</span><span class="text-indigo-600">{{ $estimatedCost['formatted'] }}</span></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-4">* Final cost may vary based on parts and actual service time</p>
                                </div>

                                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                                    <label class="flex items-start gap-3">
                                        <input type="checkbox" wire:model="termsAccepted" class="mt-1 rounded border-gray-300 text-indigo-600">
                                        <span class="text-sm text-gray-700">I acknowledge that this is an estimate, and I agree to the <a href="#" class="text-indigo-600 underline">Terms of Service</a> and authorize the service work to be performed.</span>
                                    </label>
                                    @error('termsAccepted') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Navigation --}}
            <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <button type="button" wire:click="previousStep" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition-colors {{ $currentStep === 1 ? 'invisible' : '' }}">
                    ← Previous
                </button>
                @if($currentStep < $totalSteps)
                    <button type="button" wire:click="nextStep" class="px-8 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-200">
                        Next →
                    </button>
                @else
                    <button type="button" wire:click="submit" wire:loading.attr="disabled" class="px-8 py-2.5 rounded-xl bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg shadow-green-200 disabled:opacity-50">
                        <span wire:loading.remove wire:target="submit">Submit Work Order</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div x-data="{ show: false }" @show-confirmation.window="show = true" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.away="show = false">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Confirm Submission</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to submit this work order? You'll be redirected to the work order details page.</p>
            <div class="flex gap-4">
                <button type="button" @click="show = false" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700">Cancel</button>
                <button type="button" wire:click="confirmSubmit" @click="show = false" class="flex-1 px-4 py-2 rounded-lg bg-green-600 text-white">Confirm</button>
            </div>
        </div>
    </div>
</div>
