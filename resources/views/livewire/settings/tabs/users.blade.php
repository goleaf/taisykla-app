<div class="space-y-6">
    {{-- Security Settings --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Security Settings</h2>
        <form wire:submit.prevent="updateSecuritySettings" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Minimum Password Length</label>
                <input type="number" wire:model="securitySettings.password_min_length"
                    class="mt-1 w-full rounded-md border-gray-300" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Session Timeout (Minutes)</label>
                <input type="number" wire:model="securitySettings.session_timeout_minutes"
                    class="mt-1 w-full rounded-md border-gray-300" />
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="securitySettings.require_special_chars"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label class="text-sm text-gray-700">Require Special Characters</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="securitySettings.mfa_enforced"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label class="text-sm text-gray-700">Enforce MFA for All Users</label>
            </div>
            <div class="col-span-1 md:col-span-2 flex justify-end">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save Security
                    Settings</button>
            </div>
        </form>
    </div>
    {{-- Roles Section --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Roles & Permissions</h2>
                <p class="text-sm text-gray-500">Manage system access levels.</p>
            </div>
            <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                wire:click="$toggle('showRoleCreate')">
                Add Role
            </button>
        </div>

        @if($showRoleCreate)
            <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <form wire:submit.prevent="createRole" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 uppercase">Role Name</label>
                        <input wire:model="newRole.name" class="mt-1 w-full rounded-md border-gray-300"
                            placeholder="e.g. Supervisor" />
                    </div>
                    <div class="col-span-2 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showRoleCreate', false)"
                            class="px-3 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Create
                            Role</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Name</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Guard</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Permissions</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($roles as $role)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $role->name }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $role->guard_name }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $role->permissions_count }}</td>
                            <td class="px-3 py-2 text-right">
                                {{-- Placeholder for edit --}}
                                <button class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Users Section --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Users</h2>
                <p class="text-sm text-gray-500">Manage user accounts and organization assignment.</p>
            </div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                wire:click="$toggle('showUserCreate')">Add User</button>
        </div>

        @if ($showUserCreate)
            <div class="mb-6 p-4 bg-gray-50 rounded-md border border-gray-200">
                <form wire:submit.prevent="createUser" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Name</label>
                        <input wire:model="newUser.name" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Email</label>
                        <input wire:model="newUser.email" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Roles</label>
                        <select wire:model="newUser.roles" multiple size="3" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ \App\Support\RoleCatalog::label($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Organization</label>
                        <select wire:model="newUser.organization_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">None</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Phone</label>
                        <input wire:model="newUser.phone" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Job Title</label>
                        <input wire:model="newUser.job_title" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium uppercase">Employee ID</label>
                        <input wire:model="newUser.employee_id" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>

                    <div class="md:col-span-4 flex items-center justify-end gap-3 mt-2">
                        <button type="button" class="px-4 py-2 text-gray-600 hover:text-gray-900"
                            wire:click="resetNewUser">Reset</button>
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Create
                            User</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $user->name }}
                                @if(!$user->is_active)
                                    <span
                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $user->getRoleNames()->map(fn($role) => \App\Support\RoleCatalog::label($role))->implode(', ') ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $user->organization?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 border-t border-gray-100 pt-4">{{ $users->links() }}</div>
    </div>
</div>