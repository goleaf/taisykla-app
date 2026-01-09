@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Permission Management</h1>
            <p class="text-gray-500 dark:text-gray-400">Manage roles and their permissions</p>
        </div>
        <div class="space-x-3">
            <a href="{{ route('admin.permissions.users') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Manage Users
            </a>
            <form action="{{ route('admin.permissions.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Sync from Catalog
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Permission Matrix --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="sticky left-0 bg-gray-50 dark:bg-gray-700 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider z-10">
                            Permission
                        </th>
                        @foreach($roles as $role)
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                {{ $roleLabels[$role->name] ?? $role->name }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($groupedPermissions as $module => $modulePermissions)
                        <tr class="bg-gray-100 dark:bg-gray-900">
                            <td colspan="{{ $roles->count() + 1 }}" class="px-6 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">
                                {{ str_replace('_', ' ', $module) }}
                            </td>
                        </tr>
                        @foreach($modulePermissions as $permission)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="sticky left-0 bg-white dark:bg-gray-800 px-6 py-2 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $permission->name }}</code>
                                </td>
                                @foreach($roles as $role)
                                    <td class="px-4 py-2 text-center">
                                        @if($role->name === 'admin')
                                            <span class="text-green-600 dark:text-green-400">✓</span>
                                        @else
                                            <span class="{{ $role->hasPermissionTo($permission->name) ? 'text-green-600 dark:text-green-400' : 'text-gray-300 dark:text-gray-600' }}">
                                                {{ $role->hasPermissionTo($permission->name) ? '✓' : '–' }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Role Cards for Editing --}}
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Edit Role Permissions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles->where('name', '!=', 'admin') as $role)
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    {{ $roleLabels[$role->name] ?? $role->name }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ $role->permissions->count() }} permissions
                </p>
                <button type="button" 
                        onclick="openEditModal('{{ $role->id }}', '{{ $role->name }}')"
                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    Edit Permissions →
                </button>
            </div>
        @endforeach
    </div>
</div>

{{-- Edit Modal (Alpine.js) --}}
<div x-data="{ 
        open: false, 
        roleId: null, 
        roleName: '',
        rolePermissions: [],
        hasPermission(permName) {
            return this.rolePermissions.includes(permName);
        }
     }" 
     @open-edit-modal.window="
        open = true; 
        roleId = $event.detail.roleId; 
        roleName = $event.detail.roleName;
        rolePermissions = $event.detail.permissions;
     "
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="open = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="'Edit ' + roleName + ' Permissions'"></h3>
                <p class="text-sm text-gray-500 mb-4">Select permissions for this role:</p>
                <form :action="`/admin/permissions/roles/${roleId}`" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach($groupedPermissions as $module => $modulePermissions)
                            <div>
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">{{ ucfirst(str_replace('_', ' ', $module)) }}</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($modulePermissions as $permission)
                                        <label class="flex items-center space-x-2 text-sm">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->name }}"
                                                   :checked="hasPermission('{{ $permission->name }}')"
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="open = false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Role permissions data from server
    const rolePermissionsMap = {
        @foreach($roles as $role)
            '{{ $role->id }}': {!! json_encode($role->permissions->pluck('name')) !!},
        @endforeach
    };

    function openEditModal(roleId, roleName) {
        const permissions = rolePermissionsMap[roleId] || [];
        window.dispatchEvent(new CustomEvent('open-edit-modal', { 
            detail: { roleId, roleName, permissions } 
        }));
    }
</script>
@endsection
