<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Communication Settings</h2>
    </div>
</div>

{{-- Notifications --}}
<div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification Triggers</h2>
    <form wire:submit.prevent="updateNotificationSettings" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-3 p-3 border border-gray-100 rounded bg-gray-50">
                <input type="checkbox" wire:model="notificationSettings.email_on_work_order_created"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm text-gray-700">Work Order Created</span>
            </div>
            <div class="flex items-center gap-3 p-3 border border-gray-100 rounded bg-gray-50">
                <input type="checkbox" wire:model="notificationSettings.email_on_work_order_assigned"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm text-gray-700">Work Order Assigned</span>
            </div>
            <div class="flex items-center gap-3 p-3 border border-gray-100 rounded bg-gray-50">
                <input type="checkbox" wire:model="notificationSettings.email_on_work_order_completed"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm text-gray-700">Work Order Completed</span>
            </div>
            <div class="flex items-center gap-3 p-3 border border-gray-100 rounded bg-gray-50">
                <input type="checkbox" wire:model="notificationSettings.email_on_ticket_created"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm text-gray-700">Support Ticket Created</span>
            </div>
        </div>
        <div class="flex justify-end mt-2">
            <button class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Save
                Triggers</button>
        </div>
    </form>
</div>

{{-- Templates --}}
<div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Templates</h2>
            <p class="text-sm text-gray-500">Standardize email and message content.</p>
        </div>
        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
            wire:click="$toggle('showTemplateCreate')">Add Template</button>
    </div>

    @if ($showTemplateCreate)
        <div class="mb-4 p-4 bg-gray-50 rounded-md border border-gray-200">
            <form wire:submit.prevent="createTemplate" class="grid grid-cols-1 gap-3">
                <input wire:model="newTemplate.name" class="rounded-md border-gray-300" placeholder="Template Name" />
                <input wire:model="newTemplate.channel" class="rounded-md border-gray-300"
                    placeholder="Channel (email, sms)" />
                <input wire:model="newTemplate.subject" class="rounded-md border-gray-300" placeholder="Subject" />
                <textarea wire:model="newTemplate.body" class="rounded-md border-gray-300" rows="3"
                    placeholder="Message body... use @{{ name }} variables"></textarea>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showTemplateCreate', false)"
                        class="px-3 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
                    <button class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save
                        Template</button>
                </div>
            </form>
        </div>
    @endif

    <div class="space-y-2 text-sm">
        @foreach ($templates as $template)
            <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-50 text-indigo-700 p-1.5 rounded">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">{{ $template->name }}</div>
                        <div class="text-xs text-gray-500">{{ $template->subject ?? 'No subject' }}</div>
                    </div>
                </div>
                <span
                    class="text-xs font-medium text-gray-500 uppercase tracking-wide px-2 py-0.5 bg-gray-100 rounded">{{ $template->channel }}</span>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $templates->links() }}</div>
</div>
</div>