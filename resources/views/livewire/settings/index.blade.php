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
    <div class="mt-6">
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
