@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Role Management</h1>
                <p class="text-gray-500 dark:text-gray-400">Assign roles to users</p>
            </div>
            <a href="{{ route('admin.permissions.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Back to Permissions
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            User</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Email</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Current Roles</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 flex-shrink-0 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $roleLabels[$role->name] ?? $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-gray-400">No roles</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button type="button"
                                    onclick="openUserRoleModal({{ $user->id }}, '{{ $user->name }}', {{ json_encode($user->roles->pluck('name')) }})"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm">
                                    Edit Roles
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    {{-- Edit User Roles Modal --}}
    <div id="userRoleModal" x-data="{ open: false, userId: null, userName: '', userRoles: [] }" x-show="open" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="open = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        x-text="'Edit Roles for ' + userName"></h3>
                    <form :action="`/admin/permissions/users/${userId}/roles`" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($roles as $role)
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                        :checked="userRoles.includes('{{ $role->name }}')"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <span
                                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $roleLabels[$role->name] ?? $role->name }}</span>
                                        <p class="text-xs text-gray-500">{{ $role->permissions->count() }} permissions</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="open = false"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Save
                                Roles</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openUserRoleModal(userId, userName, userRoles) {
            const modal = document.getElementById('userRoleModal').__x.$data;
            modal.userId = userId;
            modal.userName = userName;
            modal.userRoles = userRoles;
            modal.open = true;
        }
    </script>
@endsection