<div class="space-y-6">

    {{-- Privacy Settings --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Privacy & Consent</h2>
        <form wire:submit.prevent="updateComplianceSettings" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Privacy Policy URL</label>
                    <input wire:model="complianceSettings.privacy_policy_url" class="w-full rounded-md border-gray-300"
                        placeholder="https://..." />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Terms of Service URL</label>
                    <input wire:model="complianceSettings.terms_service_url" class="w-full rounded-md border-gray-300"
                        placeholder="https://..." />
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="complianceSettings.gdpr_enabled"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4" />
                    <div>
                        <span class="text-sm font-medium text-gray-900">Enable GDPR Compliance Mode</span>
                        <p class="text-xs text-gray-500">Enforces stricter data handling and consent requirements.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="complianceSettings.cookie_consent_enabled"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4" />
                    <div>
                        <span class="text-sm font-medium text-gray-900">Show Cookie Consent Banner</span>
                        <p class="text-xs text-gray-500">Displays a banner requiring user acceptance of cookies.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save Compliance
                    Settings</button>
            </div>
        </form>
    </div>

    {{-- Data Subject Requests --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Data Subject Requests</h2>
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="font-medium text-gray-900 text-sm">Personal Data Export</h3>
            <p class="text-xs text-gray-500 mt-1 mb-3">Export all personal data associated with your account in a
                machine-readable format.</p>
            <button wire:click="exportPersonalData"
                class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded text-sm hover:bg-gray-50">
                Export My Data
            </button>
        </div>
    </div>
</div>