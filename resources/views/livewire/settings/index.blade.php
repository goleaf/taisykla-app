<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Settings & Administration</h1>
            <p class="text-sm text-gray-500">Manage users, policies, and templates.</p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Users</h2>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="$toggle('showUserCreate')">Add User</button>
            </div>

            @if ($showUserCreate)
                <form wire:submit.prevent="createUser" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="text-xs text-gray-500">Name</label>
                        <input wire:model="newUser.name" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Email</label>
                        <input wire:model="newUser.email" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Role</label>
                        <select wire:model="newUser.role" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Organization</label>
                        <select wire:model="newUser.organization_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">None</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4 flex items-center gap-3">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Create</button>
                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="resetNewUser">Reset</button>
                    </div>
                </form>
            @endif

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->getRoleNames()->first() ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->organization?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Service Agreements</h2>
                    <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showAgreementCreate')">Add</button>
                </div>
                @if ($showAgreementCreate)
                    <form wire:submit.prevent="createAgreement" class="grid grid-cols-1 gap-3 mb-4">
                        <input wire:model="newAgreement.name" class="rounded-md border-gray-300" placeholder="Name" />
                        <input wire:model="newAgreement.agreement_type" class="rounded-md border-gray-300" placeholder="Type" />
                        <input type="number" step="0.01" wire:model="newAgreement.monthly_fee" class="rounded-md border-gray-300" placeholder="Monthly Fee" />
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                    </form>
                @endif
                <div class="space-y-2 text-sm">
                    @foreach ($agreements as $agreement)
                        <div class="flex items-center justify-between">
                            <span>{{ $agreement->name }}</span>
                            <span class="text-gray-500">${{ number_format($agreement->monthly_fee, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Work Order Categories</h2>
                    <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showCategoryCreate')">Add</button>
                </div>
                @if ($showCategoryCreate)
                    <form wire:submit.prevent="createCategory" class="grid grid-cols-1 gap-3 mb-4">
                        <input wire:model="newCategory.name" class="rounded-md border-gray-300" placeholder="Name" />
                        <input type="number" wire:model="newCategory.default_estimated_minutes" class="rounded-md border-gray-300" placeholder="Estimated minutes" />
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                    </form>
                @endif
                <div class="space-y-2 text-sm">
                    @foreach ($categories as $category)
                        <div class="flex items-center justify-between">
                            <span>{{ $category->name }}</span>
                            <span class="text-gray-500">{{ $category->default_estimated_minutes ?? '—' }} mins</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Company Profile</h2>
                </div>
                <form wire:submit.prevent="updateCompanyProfile" class="grid grid-cols-1 gap-3">
                    <input wire:model="companyProfile.name" class="rounded-md border-gray-300" placeholder="Company name" />
                    <input wire:model="companyProfile.address" class="rounded-md border-gray-300" placeholder="Primary address" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input wire:model="companyProfile.support_email" class="rounded-md border-gray-300" placeholder="Support email" />
                        <input wire:model="companyProfile.support_phone" class="rounded-md border-gray-300" placeholder="Support phone" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input wire:model="companyProfile.website" class="rounded-md border-gray-300" placeholder="Website" />
                        <input wire:model="companyProfile.hours" class="rounded-md border-gray-300" placeholder="Business hours" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input wire:model="companyProfile.logo_url" class="rounded-md border-gray-300" placeholder="Logo URL" />
                        <input wire:model="companyProfile.primary_color" class="rounded-md border-gray-300" placeholder="Primary color" />
                    </div>
                    <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save Company Profile</button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">System Status</h2>
                    <span class="text-xs text-gray-500">Operational overview</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Active Users</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $activeUsers }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Open Work Orders</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $openWorkOrders }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Overdue Work Orders</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $overdueWorkOrders }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Open Support Tickets</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $openSupportTickets }}</p>
                    </div>
                </div>
                <div class="mt-6 text-sm text-gray-600">
                    <div class="flex items-center justify-between">
                        <span>Last backup</span>
                        <span>{{ $backupLastRunAt ?? 'Not recorded' }}</span>
                    </div>
                    <button class="mt-3 px-3 py-1 border border-gray-300 rounded-md text-xs" wire:click="markBackupComplete">
                        Mark Backup Complete
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Communication Templates</h2>
                    <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showTemplateCreate')">Add</button>
                </div>
                @if ($showTemplateCreate)
                    <form wire:submit.prevent="createTemplate" class="grid grid-cols-1 gap-3 mb-4">
                        <input wire:model="newTemplate.name" class="rounded-md border-gray-300" placeholder="Name" />
                        <input wire:model="newTemplate.channel" class="rounded-md border-gray-300" placeholder="Channel" />
                        <input wire:model="newTemplate.subject" class="rounded-md border-gray-300" placeholder="Subject" />
                        <textarea wire:model="newTemplate.body" class="rounded-md border-gray-300" rows="2" placeholder="Body"></textarea>
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                    </form>
                @endif
                <div class="space-y-2 text-sm">
                    @foreach ($templates as $template)
                        <div class="flex items-center justify-between">
                            <span>{{ $template->name }}</span>
                            <span class="text-gray-500">{{ $template->channel }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Inventory Locations</h2>
                    <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showLocationCreate')">Add</button>
                </div>
                @if ($showLocationCreate)
                    <form wire:submit.prevent="createLocation" class="grid grid-cols-1 gap-3 mb-4">
                        <input wire:model="newLocation.name" class="rounded-md border-gray-300" placeholder="Location name" />
                        <input wire:model="newLocation.address" class="rounded-md border-gray-300" placeholder="Address" />
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                    </form>
                @endif
                <div class="space-y-2 text-sm">
                    @foreach ($locations as $location)
                        <div class="flex items-center justify-between">
                            <span>{{ $location->name }}</span>
                            <span class="text-gray-500">{{ $location->address ?? '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">System Settings</h2>
                <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showSettingCreate')">Add</button>
            </div>

            @if ($showSettingCreate)
                <form wire:submit.prevent="createSetting" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                    <div>
                        <label class="text-xs text-gray-500">Group</label>
                        <input wire:model="newSetting.group" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Key</label>
                        <input wire:model="newSetting.key" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Value (JSON or text)</label>
                        <input wire:model="newSetting.value" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-4 flex items-center gap-3">
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                        <button type="button" class="px-3 py-2 border border-gray-300 rounded-md" wire:click="resetNewSetting">Reset</button>
                    </div>
                </form>
            @endif

            <div class="space-y-4 text-sm">
                @forelse ($systemSettings as $setting)
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-start border border-gray-100 rounded-md p-3">
                        <div class="text-gray-500">{{ $setting->group }}</div>
                        <div class="text-gray-900 font-medium">{{ $setting->key }}</div>
                        <div class="md:col-span-2">
                            <textarea wire:model="settingValues.{{ $setting->id }}" class="w-full rounded-md border-gray-300" rows="2"></textarea>
                        </div>
                        <div class="flex items-center">
                            <button class="px-3 py-1 border border-gray-300 rounded-md text-xs" wire:click="updateSetting({{ $setting->id }})">Update</button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No settings saved yet.</p>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Equipment Categories</h2>
                    <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showEquipmentCategoryCreate')">Add</button>
                </div>
                @if ($showEquipmentCategoryCreate)
                    <form wire:submit.prevent="createEquipmentCategory" class="grid grid-cols-1 gap-3 mb-4">
                        <input wire:model="newEquipmentCategory.name" class="rounded-md border-gray-300" placeholder="Name" />
                        <textarea wire:model="newEquipmentCategory.description" class="rounded-md border-gray-300" rows="2" placeholder="Description"></textarea>
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                    </form>
                @endif
                <div class="space-y-2 text-sm">
                    @foreach ($equipmentCategories as $category)
                        <div class="flex items-center justify-between">
                            <span>{{ $category->name }}</span>
                            <span class="text-gray-500">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Automation Rules</h2>
                    <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showAutomationCreate')">Add</button>
                </div>
                @if ($showAutomationCreate)
                    <form wire:submit.prevent="createAutomationRule" class="grid grid-cols-1 gap-3 mb-4">
                        <input wire:model="newAutomation.name" class="rounded-md border-gray-300" placeholder="Name" />
                        <input wire:model="newAutomation.trigger" class="rounded-md border-gray-300" placeholder="Trigger" />
                        <textarea wire:model="newAutomation.conditions" class="rounded-md border-gray-300" rows="2" placeholder="Conditions (JSON)"></textarea>
                        <textarea wire:model="newAutomation.actions" class="rounded-md border-gray-300" rows="2" placeholder="Actions (JSON)"></textarea>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <input type="checkbox" wire:model="newAutomation.is_active" class="rounded border-gray-300" />
                            <span>Active</span>
                        </div>
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                    </form>
                @endif
                <div class="space-y-2 text-sm">
                    @foreach ($automationRules as $rule)
                        <div class="flex items-center justify-between">
                            <span>{{ $rule->name }}</span>
                            <span class="text-gray-500">{{ $rule->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Integration Settings</h2>
                <button class="px-3 py-1 border border-gray-300 rounded-md" wire:click="$toggle('showIntegrationCreate')">Add</button>
            </div>
            @if ($showIntegrationCreate)
                <form wire:submit.prevent="createIntegrationSetting" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                    <input wire:model="newIntegration.provider" class="rounded-md border-gray-300" placeholder="Provider" />
                    <input wire:model="newIntegration.name" class="rounded-md border-gray-300" placeholder="Name" />
                    <input wire:model="newIntegration.config" class="rounded-md border-gray-300" placeholder="Config (JSON)" />
                    <div class="md:col-span-3 flex items-center gap-2 text-xs text-gray-500">
                        <input type="checkbox" wire:model="newIntegration.is_active" class="rounded border-gray-300" />
                        <span>Active</span>
                    </div>
                    <div class="md:col-span-3">
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                    </div>
                </form>
            @endif
            <div class="space-y-2 text-sm">
                @foreach ($integrationSettings as $integration)
                    <div class="flex items-center justify-between">
                        <span>{{ $integration->provider }} {{ $integration->name ? '(' . $integration->name . ')' : '' }}</span>
                        <span class="text-gray-500">{{ $integration->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Audit Log</h2>
                <span class="text-xs text-gray-500">Recent activity</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($auditLogs as $log)
                            <tr>
                                <td class="px-3 py-2 text-gray-700">{{ $log->created_at?->format('M d, H:i') }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $log->action }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $log->description ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-2 text-gray-500" colspan="5">No audit entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
