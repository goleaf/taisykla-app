<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemSetting;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommunicationComplianceSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => PermissionCatalog::SETTINGS_VIEW, 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => PermissionCatalog::SETTINGS_MANAGE, 'guard_name' => 'web']);
    }

    public function test_can_update_notification_settings()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::SETTINGS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('notificationSettings.email_on_work_order_created', false)
            ->call('updateNotificationSettings');

        $this->assertDatabaseHas('system_settings', [
            'group' => 'notification',
            'key' => 'email_on_work_order_created',
            'value' => 'false',
        ]);
    }

    public function test_can_update_compliance_settings()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::SETTINGS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('complianceSettings.gdpr_enabled', true)
            ->set('complianceSettings.privacy_policy_url', 'https://example.com/privacy')
            ->call('updateComplianceSettings');

        $this->assertDatabaseHas('system_settings', [
            'group' => 'compliance',
            'key' => 'gdpr_enabled',
            'value' => 'true',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'group' => 'compliance',
            'key' => 'privacy_policy_url',
            'value' => '"https:\/\/example.com\/privacy"',
        ]);
    }

    public function test_can_initiate_personal_data_export()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::SETTINGS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->call('exportPersonalData')
            ->assertSee('Personal data export initiated');
    }
}
