<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Access & Permissions') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('View your assigned roles and active system permissions.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        {{-- Roles Block --}}
        <div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Assigned Roles</h3>
            <div class="flex flex-wrap gap-3">
                @forelse ($roles as $role)
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ $role['label'] }}
                    </div>
                @empty
                    <span class="text-sm text-gray-500 italic">No roles assigned.</span>
                @endforelse
            </div>
        </div>

        {{-- Permissions Grid --}}
        <div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Effective Strategy</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($groupedPermissions as $group => $permissions)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-700 mb-2 border-b border-gray-200 pb-1">{{ $group }}</h4>
                        <ul class="space-y-1">
                            @foreach ($permissions as $permission)
                                <li class="text-sm text-gray-600 flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span
                                        class="break-words">{{ str_replace('_', ' ', Str::after($permission->name, '.')) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            @if($groupedPermissions->isEmpty())
                <div class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded border border-gray-100">
                    No custom permissions found.
                </div>
            @endif
        </div>
    </div>
</section>