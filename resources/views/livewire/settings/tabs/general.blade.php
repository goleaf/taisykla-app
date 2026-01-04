<div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Company Profile</h2>
            <p class="text-sm text-gray-500">Manage your company's public information.</p>
        </div>
        <form wire:submit.prevent="updateCompanyProfile" class="grid grid-cols-1 gap-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                    <input wire:model="companyProfile.name" class="w-full rounded-md border-gray-300"
                        placeholder="e.g. Acme Services" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input wire:model="companyProfile.website" class="w-full rounded-md border-gray-300"
                        placeholder="https://..." />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Primary Address</label>
                <input wire:model="companyProfile.address" class="w-full rounded-md border-gray-300"
                    placeholder="123 Main St..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input wire:model="companyProfile.support_email" class="w-full rounded-md border-gray-300"
                        placeholder="support@acme.com" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                    <input wire:model="companyProfile.support_phone" class="w-full rounded-md border-gray-300"
                        placeholder="+1 (555) 000-0000" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Business Hours</label>
                <textarea wire:model="companyProfile.hours" rows="3" class="w-full rounded-md border-gray-300"
                    placeholder="Mon-Fri: 9am - 5pm..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                    <input wire:model="companyProfile.logo_url" class="w-full rounded-md border-gray-300"
                        placeholder="https://..." />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
                    <div class="flex gap-2">
                        <input type="color" wire:model.live="companyProfile.primary_color"
                            class="h-10 w-20 rounded border border-gray-300 p-1" />
                        <input wire:model="companyProfile.primary_color" class="w-full rounded-md border-gray-300"
                            placeholder="#000000" />
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>