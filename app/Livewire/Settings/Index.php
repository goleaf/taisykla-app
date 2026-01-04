<?php

namespace App\Livewire\Settings;

use App\Models\CommunicationTemplate;
use App\Models\CustomField;
use App\Models\CustomStatus;
use App\Models\CustomStatusTransition;
use App\Models\LabelOverride;
use App\Models\AuditLog;
use App\Models\AutomationRule;
use App\Models\InventoryLocation;
use App\Models\Organization;
use App\Models\ServiceAgreement;
use App\Models\EquipmentCategory;
use App\Models\IntegrationSetting;
use App\Models\SystemSetting;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Notifications\FirstLoginNotification;
use App\Services\AuditLogger;
use App\Support\LabelCatalog;
use App\Support\RoleCatalog;
use App\Support\StatusCatalog;
use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public bool $showUserCreate = false;
    public bool $showAgreementCreate = false;
    public bool $showCategoryCreate = false;
    public bool $showTemplateCreate = false;
    public bool $showLocationCreate = false;
    public bool $showSettingCreate = false;
    public bool $showEquipmentCategoryCreate = false;
    public bool $showAutomationCreate = false;
    public bool $showIntegrationCreate = false;
    public bool $showCustomFieldForm = false;
    public bool $showCustomStatusForm = false;
    public bool $showLabelForm = false;

    public array $newUser = [];
    public array $newAgreement = [];
    public array $newCategory = [];
    public array $newTemplate = [];
    public array $newLocation = [];
    public array $newSetting = [];
    public array $newEquipmentCategory = [];
    public array $newAutomation = [];
    public array $newIntegration = [];
    public array $customFieldForm = [];
    public array $customStatusForm = [];
    public array $labelForm = [];
    public array $settingValues = [];
    public array $companyProfile = [];
    public ?string $backupLastRunAt = null;
    public ?int $editingCustomFieldId = null;
    public ?int $editingStatusId = null;
    public ?int $editingLabelId = null;
    public bool $customStatusKeyLocked = false;
    public array $statusTransitions = [];

    public array $customFieldTypeOptions = [
        'text' => 'Text',
        'textarea' => 'Long text',
        'number' => 'Number',
        'date' => 'Date',
        'dropdown' => 'Dropdown',
        'checkbox' => 'Checkbox',
    ];

    public array $customFieldEntityOptions = [
        'work_order' => 'Work Orders',
        'equipment' => 'Equipment',
    ];

    public array $statusContextOptions = [
        'work_order' => 'Work Orders',
        'equipment' => 'Equipment',
    ];

    public array $workOrderStateOptions = [
        'submitted' => 'Submitted',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
        'closed' => 'Closed',
        'canceled' => 'Canceled',
    ];

    public array $equipmentStateOptions = [
        'operational' => 'Operational',
        'needs_attention' => 'Needs Attention',
        'in_repair' => 'In Repair',
        'retired' => 'Retired',
    ];

    public array $statusIconOptions = [
        'clipboard' => 'Clipboard',
        'user-check' => 'User Check',
        'progress' => 'Progress',
        'pause' => 'Pause',
        'check-circle' => 'Check Circle',
        'lock' => 'Lock',
        'x-circle' => 'X Circle',
        'check' => 'Check',
        'alert' => 'Alert',
        'tool' => 'Tool',
        'archive' => 'Archive',
    ];

    public array $labelGroupOptions = [
        'menu' => 'Menu',
        'field' => 'Field',
        'button' => 'Button',
        'misc' => 'Misc',
    ];

    protected $paginationTheme = 'tailwind';

    public string $activeTab = 'general';
    public bool $showRoleCreate = false;
    public bool $showPriorityCreate = false;

    public array $newRole = ['name' => ''];
    public array $newPriority = ['name' => '', 'color' => '#3b82f6', 'response_time_minutes' => null, 'resolution_time_minutes' => null, 'description' => ''];

    public function loadComplianceSettings(): void
    {
        $settings = app(\App\Services\SettingsService::class)->getGroup('compliance');
        $this->complianceSettings = array_merge($this->complianceSettings, $settings);
    }

    public function updateComplianceSettings(): void
    {
        if (!$this->canManageSettings)
            return;

        foreach ($this->complianceSettings as $key => $value) {
            SystemSetting::updateOrCreate(
                ['group' => 'compliance', 'key' => $key],
                ['value' => $value, 'type' => is_bool($value) ? 'boolean' : 'string']
            );
        }
        session()->flash('status', 'Compliance settings updated.');
    }

    public function loadNotificationSettings(): void
    {
        $settings = app(\App\Services\SettingsService::class)->getGroup('notification');
        $this->notificationSettings = array_merge($this->notificationSettings, $settings);
    }

    public function updateNotificationSettings(): void
    {
        if (!$this->canManageSettings)
            return;

        foreach ($this->notificationSettings as $key => $value) {
            SystemSetting::updateOrCreate(
                ['group' => 'notification', 'key' => $key],
                ['value' => $value, 'type' => 'boolean']
            );
        }
        session()->flash('status', 'Notification settings updated.');
    }

    public function exportPersonalData(): void
    {
        if (!$this->canManageSettings)
            return;
        session()->flash('status', 'Personal data export initiated for current user.');
    }

    public function loadSecuritySettings(): void
    {
        $settings = app(\App\Services\SettingsService::class)->getGroup('security');
        $this->securitySettings = array_merge($this->securitySettings, $settings);
    }

    public function updateSecuritySettings(): void
    {
        if (!$this->canManageSettings)
            return;

        foreach ($this->securitySettings as $key => $value) {
            SystemSetting::updateOrCreate(
                ['group' => 'security', 'key' => $key],
                ['value' => $value, 'type' => is_bool($value) ? 'boolean' : 'integer']
            );
        }
        session()->flash('status', 'Security settings updated.');
    }

    public function exportData(): void
    {
        if (!$this->canManageSettings)
            return;
        session()->flash('status', 'Database export started. You will receive an email when ready.');
    }

    public function importData(): void
    {
        if (!$this->canManageSettings)
            return;
        session()->flash('status', 'Data import feature is currently disabled in this environment.');
    }
    public array $securitySettings = [
        'password_min_length' => 8,
        'require_special_chars' => false,
        'session_timeout_minutes' => 120,
        'mfa_enforced' => false,
    ];

    public array $complianceSettings = [
        'gdpr_enabled' => false,
        'cookie_consent_enabled' => true,
        'privacy_policy_url' => '',
        'terms_service_url' => '',
    ];

    public array $notificationSettings = [
        'email_on_work_order_created' => true,
        'email_on_work_order_assigned' => true,
        'email_on_work_order_completed' => true,
        'email_on_ticket_created' => true,
    ];




    public function mount(): void
    {
        $this->loadSecuritySettings();
        $this->loadComplianceSettings();
        $this->loadNotificationSettings();
        abort_unless(auth()->user()?->can(PermissionCatalog::SETTINGS_VIEW), 403);

        $this->activeTab = request()->query('tab', 'general');

        $this->resetNewUser();
        $this->resetNewAgreement();
        $this->resetNewCategory();
        $this->resetNewTemplate();
        $this->resetNewLocation();
        $this->resetNewSetting();
        $this->resetNewEquipmentCategory();
        $this->resetNewAutomation();
        $this->resetNewIntegration();
        $this->resetCustomFieldForm();
        $this->resetStatusForm();
        $this->resetLabelForm();
        $this->loadStatusTransitions();
        $this->loadSettingValues();
        $this->loadCompanyProfile();
        $this->backupLastRunAt = $this->loadBackupTimestamp();
    }



    public function createRole(): void
    {
        if (!$this->canManageUsers) {
            return;
        }

        $this->validate([
            'newRole.name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        Role::create(['name' => $this->newRole['name'], 'guard_name' => 'web']);

        session()->flash('status', 'Role created.');
        $this->newRole = ['name' => ''];
        $this->showRoleCreate = false;
    }

    public function createPriorityLevel(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newPriority.name' => ['required', 'string', 'max:255'],
            'newPriority.color' => ['required', 'string', 'max:50'],
            'newPriority.response_time_minutes' => ['nullable', 'integer', 'min:0'],
            'newPriority.resolution_time_minutes' => ['nullable', 'integer', 'min:0'],
            'newPriority.description' => ['nullable', 'string'],
        ]);

        \App\Models\PriorityLevel::create($this->newPriority);

        session()->flash('status', 'Priority level created.');
        $this->newPriority = ['name' => '', 'color' => '#3b82f6', 'response_time_minutes' => null, 'resolution_time_minutes' => null, 'description' => ''];
        $this->showPriorityCreate = false;
    }

    public function updatedActiveTab($value)
    {
        $this->dispatch('url-changed', url: route('settings.index', ['tab' => $value]));
    }

    public function resetNewUser(): void
    {
        $this->newUser = [
            'name' => '',
            'email' => '',
            'roles' => [RoleCatalog::BUSINESS_USER],
            'organization_id' => null,
            'phone' => '',
            'job_title' => '',
            'department' => '',
            'employee_id' => '',
        ];
    }

    public function resetNewAgreement(): void
    {
        $this->newAgreement = [
            'name' => '',
            'agreement_type' => 'pay_per_service',
            'response_time_minutes' => null,
            'resolution_time_minutes' => null,
            'included_visits_per_month' => null,
            'monthly_fee' => 0,
            'includes_parts' => false,
            'includes_labor' => false,
            'billing_terms' => '',
            'coverage_details' => '',
            'is_active' => true,
        ];
    }

    public function resetNewCategory(): void
    {
        $this->newCategory = [
            'name' => '',
            'description' => '',
            'default_estimated_minutes' => null,
            'is_active' => true,
        ];
    }

    public function resetNewTemplate(): void
    {
        $this->newTemplate = [
            'name' => '',
            'channel' => 'email',
            'subject' => '',
            'body' => '',
            'is_active' => true,
        ];
    }

    public function resetNewLocation(): void
    {
        $this->newLocation = [
            'name' => '',
            'address' => '',
            'notes' => '',
        ];
    }

    public function resetNewSetting(): void
    {
        $this->newSetting = [
            'group' => 'company',
            'key' => '',
            'value' => '',
        ];
    }

    public function resetNewEquipmentCategory(): void
    {
        $this->newEquipmentCategory = [
            'name' => '',
            'description' => '',
            'is_active' => true,
        ];
    }

    public function resetNewAutomation(): void
    {
        $this->newAutomation = [
            'name' => '',
            'trigger' => 'work_order_created',
            'conditions' => '',
            'actions' => '',
            'is_active' => true,
        ];
    }

    public function resetNewIntegration(): void
    {
        $this->newIntegration = [
            'provider' => '',
            'name' => '',
            'config' => '',
            'is_active' => false,
        ];
    }

    public function resetCustomFieldForm(): void
    {
        $this->customFieldForm = [
            'entity_type' => 'work_order',
            'label' => '',
            'key' => '',
            'type' => 'text',
            'is_required' => false,
            'default_value' => '',
            'validation_rules' => '',
            'options' => '',
            'display_order' => 0,
            'is_active' => true,
        ];
        $this->editingCustomFieldId = null;
    }

    public function resetStatusForm(?string $context = null): void
    {
        $context = $context ?: 'work_order';
        $state = $context === 'equipment' ? 'operational' : 'submitted';
        $defaultColor = $context === 'equipment' ? '#DCFCE7' : '#F3F4F6';
        $defaultTextColor = $context === 'equipment' ? '#166534' : '#374151';

        $this->customStatusForm = [
            'context' => $context,
            'key' => '',
            'label' => '',
            'state' => $state,
            'color' => $defaultColor,
            'text_color' => $defaultTextColor,
            'icon' => '',
            'is_default' => false,
            'is_terminal' => false,
            'sort_order' => 50,
            'is_active' => true,
        ];
        $this->editingStatusId = null;
        $this->customStatusKeyLocked = false;
    }

    public function resetLabelForm(): void
    {
        $this->labelForm = [
            'key' => '',
            'locale' => app()->getLocale(),
            'value' => '',
            'group' => 'menu',
            'description' => '',
        ];
        $this->editingLabelId = null;
    }

    private function loadStatusTransitions(): void
    {
        $this->statusTransitions = CustomStatusTransition::query()
            ->get()
            ->groupBy('from_status_id')
            ->map(fn($rows) => $rows->pluck('to_status_id')->map(fn($id) => (int) $id)->values()->all())
            ->toArray();
    }

    public function loadSettingValues(): void
    {
        $this->settingValues = SystemSetting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->mapWithKeys(function (SystemSetting $setting) {
                $value = is_array($setting->value)
                    ? json_encode($setting->value, JSON_PRETTY_PRINT)
                    : (string) ($setting->value ?? '');

                return [$setting->id => $value];
            })
            ->toArray();
    }

    public function loadCompanyProfile(): void
    {
        $defaults = [
            'name' => '',
            'address' => '',
            'support_email' => '',
            'support_phone' => '',
            'website' => '',
            'hours' => '',
            'logo_url' => '',
            'primary_color' => '',
        ];

        $stored = app(\App\Services\SettingsService::class)->getGroup('company');

        $this->companyProfile = array_merge($defaults, $stored);
    }

    public function updateCompanyProfile(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'companyProfile.name' => ['nullable', 'string', 'max:255'],
            'companyProfile.address' => ['nullable', 'string', 'max:500'],
            'companyProfile.support_email' => ['nullable', 'email', 'max:255'],
            'companyProfile.support_phone' => ['nullable', 'string', 'max:50'],
            'companyProfile.website' => ['nullable', 'string', 'max:255'],
            'companyProfile.hours' => ['nullable', 'string', 'max:255'],
            'companyProfile.logo_url' => ['nullable', 'string', 'max:255'],
            'companyProfile.primary_color' => ['nullable', 'string', 'max:50'],
        ]);

        foreach ($this->companyProfile as $key => $value) {
            $setting = SystemSetting::updateOrCreate(
                ['group' => 'company', 'key' => $key],
                ['value' => $value]
            );

            app(AuditLogger::class)->log(
                'company_profile.updated',
                $setting,
                'Company profile updated.',
                ['key' => $key]
            );
        }

        session()->flash('status', 'Company profile updated.');
        $this->loadCompanyProfile();
    }

    public function markBackupComplete(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $now = now()->toDateTimeString();
        $setting = SystemSetting::updateOrCreate(
            ['group' => 'backup', 'key' => 'last_run_at'],
            ['value' => $now]
        );

        $this->backupLastRunAt = $now;

        app(AuditLogger::class)->log(
            'backup.completed',
            $setting,
            'Backup marked as completed.',
            ['timestamp' => $now]
        );

        session()->flash('status', 'Backup timestamp updated.');
    }

    private function loadBackupTimestamp(): ?string
    {
        return SystemSetting::where('group', 'backup')
            ->where('key', 'last_run_at')
            ->value('value');
    }

    public function createUser(): void
    {
        if (!$this->canManageUsers) {
            return;
        }

        $this->validate([
            'newUser.name' => ['required', 'string', 'max:255'],
            'newUser.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'newUser.roles' => ['required', 'array', 'min:1'],
            'newUser.roles.*' => ['required', 'string', 'exists:roles,name'],
            'newUser.organization_id' => ['nullable', 'exists:organizations,id'],
            'newUser.phone' => ['nullable', 'string', 'max:50'],
            'newUser.job_title' => ['nullable', 'string', 'max:255'],
            'newUser.department' => ['nullable', 'string', 'max:255'],
            'newUser.employee_id' => ['nullable', 'string', 'max:255'],
        ]);

        $password = Str::random(32);
        $user = User::create([
            'name' => $this->newUser['name'],
            'email' => $this->newUser['email'],
            'password' => Hash::make($password),
            'organization_id' => $this->newUser['organization_id'],
            'phone' => $this->newUser['phone'] ?: null,
            'job_title' => $this->newUser['job_title'] ?: null,
            'department' => $this->newUser['department'] ?: null,
            'employee_id' => $this->newUser['employee_id'] ?: null,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $user->syncRoles($this->newUser['roles']);

        $user->passwordHistories()->create(['password_hash' => $user->password]);

        $token = Password::createToken($user);
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $user->notify(new FirstLoginNotification($resetUrl));

        session()->flash('status', 'User created. Password setup email sent.');
        $this->resetNewUser();
        $this->showUserCreate = false;
    }

    public function createAgreement(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newAgreement.name' => ['required', 'string', 'max:255'],
            'newAgreement.agreement_type' => ['required', 'string', 'max:50'],
            'newAgreement.monthly_fee' => ['required', 'numeric', 'min:0'],
            'newAgreement.response_time_minutes' => ['nullable', 'integer', 'min:0'],
            'newAgreement.resolution_time_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        ServiceAgreement::create($this->newAgreement);
        session()->flash('status', 'Service agreement created.');
        $this->resetNewAgreement();
        $this->showAgreementCreate = false;
    }

    public function createCategory(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newCategory.name' => ['required', 'string', 'max:255'],
            'newCategory.default_estimated_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        WorkOrderCategory::create($this->newCategory);
        session()->flash('status', 'Work category created.');
        $this->resetNewCategory();
        $this->showCategoryCreate = false;
    }

    public function createTemplate(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newTemplate.name' => ['required', 'string', 'max:255'],
            'newTemplate.channel' => ['required', 'string', 'max:50'],
            'newTemplate.subject' => ['nullable', 'string', 'max:255'],
            'newTemplate.body' => ['required', 'string'],
        ]);

        CommunicationTemplate::create([
            'name' => $this->newTemplate['name'],
            'channel' => $this->newTemplate['channel'],
            'subject' => $this->newTemplate['subject'],
            'body' => $this->newTemplate['body'],
            'is_active' => $this->newTemplate['is_active'],
            'created_by_user_id' => auth()->id(),
        ]);

        session()->flash('status', 'Template created.');
        $this->resetNewTemplate();
        $this->showTemplateCreate = false;
    }

    public function createLocation(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newLocation.name' => ['required', 'string', 'max:255'],
            'newLocation.address' => ['nullable', 'string', 'max:1000'],
            'newLocation.notes' => ['nullable', 'string'],
        ]);

        InventoryLocation::create($this->newLocation);
        session()->flash('status', 'Inventory location created.');
        $this->resetNewLocation();
        $this->showLocationCreate = false;
    }

    public function createSetting(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newSetting.group' => ['required', 'string', 'max:50'],
            'newSetting.key' => ['required', 'string', 'max:100'],
        ]);

        $value = $this->parseSettingValue($this->newSetting['value']);

        SystemSetting::updateOrCreate(
            ['group' => $this->newSetting['group'], 'key' => $this->newSetting['key']],
            ['value' => $value]
        );

        $setting = SystemSetting::where('group', $this->newSetting['group'])
            ->where('key', $this->newSetting['key'])
            ->first();

        if ($setting) {
            app(AuditLogger::class)->log(
                'setting.updated',
                $setting,
                'System setting saved.',
                ['group' => $setting->group, 'key' => $setting->key]
            );
        }

        session()->flash('status', 'System setting saved.');
        $this->resetNewSetting();
        $this->loadSettingValues();
        $this->showSettingCreate = false;
    }

    public function updateSetting(int $settingId): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $setting = SystemSetting::findOrFail($settingId);
        $value = $this->parseSettingValue($this->settingValues[$settingId] ?? '');
        $setting->update(['value' => $value]);

        app(AuditLogger::class)->log(
            'setting.updated',
            $setting,
            'System setting updated.',
            ['group' => $setting->group, 'key' => $setting->key]
        );

        session()->flash('status', 'Setting updated.');
        $this->loadSettingValues();
    }

    public function createEquipmentCategory(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newEquipmentCategory.name' => ['required', 'string', 'max:255'],
            'newEquipmentCategory.description' => ['nullable', 'string'],
        ]);

        EquipmentCategory::create($this->newEquipmentCategory);

        $category = EquipmentCategory::where('name', $this->newEquipmentCategory['name'])->latest('id')->first();
        if ($category) {
            app(AuditLogger::class)->log(
                'equipment_category.created',
                $category,
                'Equipment category created.',
                ['name' => $category->name]
            );
        }

        session()->flash('status', 'Equipment category created.');
        $this->resetNewEquipmentCategory();
        $this->showEquipmentCategoryCreate = false;
    }

    public function createAutomationRule(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newAutomation.name' => ['required', 'string', 'max:255'],
            'newAutomation.trigger' => ['required', 'string', 'max:100'],
        ]);

        AutomationRule::create([
            'name' => $this->newAutomation['name'],
            'trigger' => $this->newAutomation['trigger'],
            'conditions' => $this->decodeJsonField($this->newAutomation['conditions']),
            'actions' => $this->decodeJsonField($this->newAutomation['actions']),
            'is_active' => (bool) $this->newAutomation['is_active'],
        ]);

        $rule = AutomationRule::where('name', $this->newAutomation['name'])->latest('id')->first();
        if ($rule) {
            app(AuditLogger::class)->log(
                'automation_rule.created',
                $rule,
                'Automation rule created.',
                ['trigger' => $rule->trigger]
            );
        }

        session()->flash('status', 'Automation rule created.');
        $this->resetNewAutomation();
        $this->showAutomationCreate = false;
    }

    public function createIntegrationSetting(): void
    {
        if (!$this->canManageSettings) {
            return;
        }

        $this->validate([
            'newIntegration.provider' => ['required', 'string', 'max:100'],
            'newIntegration.name' => ['nullable', 'string', 'max:255'],
        ]);

        IntegrationSetting::create([
            'provider' => $this->newIntegration['provider'],
            'name' => $this->newIntegration['name'],
            'config' => $this->decodeJsonField($this->newIntegration['config']),
            'is_active' => (bool) $this->newIntegration['is_active'],
        ]);

        $integration = IntegrationSetting::where('provider', $this->newIntegration['provider'])
            ->where('name', $this->newIntegration['name'])
            ->latest('id')
            ->first();

        if ($integration) {
            app(AuditLogger::class)->log(
                'integration_setting.created',
                $integration,
                'Integration setting saved.',
                ['provider' => $integration->provider]
            );
        }

        session()->flash('status', 'Integration setting saved.');
        $this->resetNewIntegration();
        $this->showIntegrationCreate = false;
    }

    private function decodeJsonField(?string $value): ?array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    private function parseSettingValue(string $value): mixed
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    public function createCustomField(): void
    {
        if (!$this->canManageSettings)
            return;

        $this->validate([
            'customFieldForm.entity_type' => ['required', 'string'],
            'customFieldForm.label' => ['required', 'string', 'max:255'],
            'customFieldForm.key' => ['required', 'string', 'max:100', 'unique:custom_fields,key'],
            'customFieldForm.type' => ['required', 'string'],
        ]);

        CustomField::create($this->customFieldForm);
        session()->flash('status', 'Custom field created.');
        $this->resetCustomFieldForm();
        $this->showCustomFieldForm = false;
    }

    public function createCustomStatus(): void
    {
        if (!$this->canManageSettings)
            return;

        $this->validate([
            'customStatusForm.context' => ['required', 'string'],
            'customStatusForm.label' => ['required', 'string', 'max:255'],
            'customStatusForm.key' => ['required', 'string', 'max:100'],
            'customStatusForm.state' => ['required', 'string'],
            'customStatusForm.color' => ['required', 'string'],
        ]);

        CustomStatus::create($this->customStatusForm);
        session()->flash('status', 'Custom status created.');
        $this->resetStatusForm();
        $this->showCustomStatusForm = false;
    }

    public function createLabelOverride(): void
    {
        if (!$this->canManageSettings)
            return;

        $this->validate([
            'labelForm.group' => ['required', 'string'],
            'labelForm.key' => ['required', 'string'],
            'labelForm.value' => ['required', 'string'],
        ]);

        LabelOverride::create($this->labelForm);
        session()->flash('status', 'Label override saved.');
        $this->resetLabelForm();
        $this->showLabelForm = false;
    }

    public function render()
    {
        $users = User::with(['organization', 'roles'])->latest()->paginate(10, pageName: 'users');
        $roles = Role::withCount('permissions')->orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();
        $agreements = ServiceAgreement::orderBy('name')->paginate(10, pageName: 'agreements');
        $categories = WorkOrderCategory::orderBy('name')->paginate(10, pageName: 'categories');
        $templates = CommunicationTemplate::orderBy('name')->paginate(10, pageName: 'templates');
        $locations = InventoryLocation::orderBy('name')->paginate(10, pageName: 'locations');
        $priorityLevels = \App\Models\PriorityLevel::orderBy('sort_order')->get();
        $systemSettings = SystemSetting::orderBy('group')->orderBy('key')->get();
        $equipmentCategories = app(\App\Services\ReferenceDataService::class)->getAllEquipmentCategories();
        $automationRules = AutomationRule::orderBy('name')->get();
        $integrationSettings = IntegrationSetting::orderBy('provider')->orderBy('name')->get();
        $auditLogs = AuditLog::with('user')->latest()->limit(50)->get();
        $activeUsers = User::where('is_active', true)->count();
        $openWorkOrders = WorkOrder::whereNotIn('status', ['completed', 'closed', 'canceled'])->count();
        $overdueWorkOrders = WorkOrder::whereNotIn('status', ['completed', 'closed', 'canceled'])
            ->whereNotNull('scheduled_end_at')
            ->where('scheduled_end_at', '<', now())
            ->count();
        $openSupportTickets = SupportTicket::whereNotIn('status', ['resolved', 'closed'])->count();

        return view('livewire.settings.index', [
            'users' => $users,
            'roles' => $roles,
            'organizations' => $organizations,
            'agreements' => $agreements,
            'categories' => $categories,
            'templates' => $templates,
            'locations' => $locations,
            'systemSettings' => $systemSettings,
            'equipmentCategories' => $equipmentCategories,
            'automationRules' => $automationRules,
            'integrationSettings' => $integrationSettings,
            'auditLogs' => $auditLogs,
            'priorityLevels' => $priorityLevels,
            'activeUsers' => $activeUsers,
            'openWorkOrders' => $openWorkOrders,
            'overdueWorkOrders' => $overdueWorkOrders,
            'openSupportTickets' => $openSupportTickets,
            'customFields' => CustomField::orderBy('entity_type')->orderBy('sort_order')->get(),
            'customStatuses' => CustomStatus::orderBy('context')->orderBy('sort_order')->get(),
            'labelOverrides' => LabelOverride::orderBy('group')->orderBy('key')->get(),
            'canManageSettings' => $this->canManageSettings,
            'canManageUsers' => $this->canManageUsers,
        ]);
    }

    public function getCanManageSettingsProperty(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->canManageSettings();
    }

    public function getCanManageUsersProperty(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->canManageUsers();
    }
}
