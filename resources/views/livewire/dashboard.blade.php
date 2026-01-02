<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Welcome back, {{ $user->name }}</h1>
                <p class="text-sm text-gray-500">Role: {{ $roleLabel }} • {{ $todayLabel }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
            <p class="text-xs uppercase tracking-wide text-gray-500">Profile Overview</p>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div class="space-y-1">
                    <p>Name: {{ $user->name }}</p>
                    <p>Role: {{ $roles ? implode(', ', $roles) : '—' }}</p>
                    <p>Department: {{ $user->department ?? '—' }}</p>
                    <p>Employee ID: {{ $user->employee_id ?? '—' }}</p>
                </div>
                <div class="space-y-1">
                    <p>Email: {{ $user->email }}</p>
                    <p>Phone: {{ $user->phone ?? '—' }}</p>
                    <p>Job Title: {{ $user->job_title ?? '—' }}</p>
                    <p>Permissions: {{ $permissionCount }}</p>
                </div>
            </div>
        </div>

        @if ($availability['show'] ?? false)
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Availability</p>
                    <p class="text-lg font-semibold {{ $availability['color'] ?? 'text-gray-500' }}">{{ $availability['label'] ?? 'Offline' }}</p>
                    <p class="text-xs text-gray-500">Updated {{ $availability['updated'] ?? 'just now' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($availabilityOptions as $value => $label)
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm" wire:click="updateAvailability('{{ $value }}')">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($summaryCards as $card)
                <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $card['value'] }}</p>
                    @if (! empty($card['subtext']))
                        <p class="text-xs text-gray-500">{{ $card['subtext'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach ($sections as $section)
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">{{ $section['title'] }}</h2>
                        @if (! empty($section['action']))
                            <a class="text-sm text-indigo-600" href="{{ $section['action']['href'] }}" wire:navigate>
                                {{ $section['action']['label'] }}
                            </a>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @forelse ($section['items'] as $item)
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900">{{ $item['title'] }}</p>
                                        @if (! empty($item['badges']))
                                            @foreach ($item['badges'] as $badge)
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $badge['class'] }}">
                                                    {{ $badge['label'] }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $item['meta'] }}</p>
                                </div>
                                @if (! empty($item['href']))
                                    <a class="text-xs text-indigo-600" href="{{ $item['href'] }}" wire:navigate>View</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ $section['empty'] }}</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
