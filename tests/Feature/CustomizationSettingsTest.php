<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CustomField;
use App\Models\CustomStatus;
use App\Models\LabelOverride;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomizationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => PermissionCatalog::SETTINGS_VIEW, 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => PermissionCatalog::SETTINGS_MANAGE, 'guard_name' => 'web']);
    }

    public function test_can_create_custom_field()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::SETTINGS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('activeTab', 'customization')
            ->set('showCustomFieldForm', true)
            ->set('customFieldForm.entity_type', 'work_order')
            ->set('customFieldForm.type', 'text')
            ->set('customFieldForm.label', 'Device Serial')
            ->set('customFieldForm.key', 'device_serial')
            ->call('createCustomField')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('custom_fields', [
            'entity_type' => 'work_order',
            'key' => 'device_serial',
            'label' => 'Device Serial',
        ]);
    }

    public function test_can_create_custom_status()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::SETTINGS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('activeTab', 'customization')
            ->set('showCustomStatusForm', true)
            ->set('customStatusForm.context', 'work_order')
            ->set('customStatusForm.state', 'in_progress')
            ->set('customStatusForm.label', 'Waiting for Parts')
            ->set('customStatusForm.key', 'waiting_parts')
            ->set('customStatusForm.color', '#ff9900')
            ->call('createCustomStatus')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('custom_statuses', [
            'context' => 'work_order',
            'key' => 'waiting_parts',
            'label' => 'Waiting for Parts',
            'color' => '#ff9900',
        ]);
    }

    public function test_can_create_label_override()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::SETTINGS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('activeTab', 'customization')
            ->set('showLabelForm', true)
            ->set('labelForm.group', 'menu')
            ->set('labelForm.key', 'work_orders')
            ->set('labelForm.value', 'Service Requests')
            ->call('createLabelOverride')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('label_overrides', [
            'group' => 'menu',
            'key' => 'work_orders',
            'value' => 'Service Requests',
        ]);
    }
}
