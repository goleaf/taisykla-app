@php
    use App\Support\RoleCatalog;
@endphp

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Settings &amp; Administration</h1>
            <p class="text-sm text-gray-500">Manage system configuration, users, and comprehensive rules.</p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                @foreach([
                        'general' => 'General',
                        'users' => 'Users & Security',
                        'services' => 'Services',
                        'workflow' => 'Workflow',
                        'communication' => 'Communication',
                        'integrations' => 'Integrations',
                        'customization' => 'Customization',
                        'compliance' => 'Compliance',
                        'system' => 'System'
                    ] as $key => $label)
                        <button 
                            wire:click="$set('activeTab', '{{ $key }}')"
                            class="{{ $activeTab === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                                   whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-150 ease-in-out"
                        >
                            {{ $label }}
                    </button>
                @endforeach
        </nav>
        </div>
        {{-- Tab Content --}}
    <div class="mt-6 relative min-h-[400px]">
            <div wire:loading wire:target="activeTab" class="absolute inset-0 bg-white/40 z-30 backdrop-blur-[1px] flex items-center justify-center rounded-xl">
                <div class="flex flex-col items-center">
                    <svg class="w-10 h-10 text-indigo-600 animate-spin mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium text-indigo-600">Loading settings...</span>
                </div>
            </div>
            @if($activeTab === 'general')
                @include('livewire.settings.tabs.general')
            @elseif($activeTab === 'users')
                @include('livewire.settings.tabs.users')
            @elseif($activeTab === 'services')
                @include('livewire.settings.tabs.services')
            @elseif($activeTab === 'workflow')
                @include('livewire.settings.tabs.workflow')
            @elseif($activeTab === 'communication')
                @include('livewire.settings.tabs.communication')
            @elseif($activeTab === 'integrations')
                @include('livewire.settings.tabs.integrations')
            @elseif($activeTab === 'customization')
                @include('livewire.settings.tabs.customization')
            @elseif($activeTab === 'compliance')
                @include('livewire.settings.tabs.compliance')
            @elseif($activeTab === 'system')
                @include('livewire.settings.tabs.system')
            @endif
        </div>
    </div>
</div>
