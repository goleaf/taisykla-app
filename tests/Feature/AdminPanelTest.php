<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PriorityLevel;
use App\Models\SystemSetting;
use App\Models\Organization;
use App\Support\RoleCatalog;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create necessary permissions
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => PermissionCatalog::SETTINGS_VIEW, 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => PermissionCatalog::SETTINGS_MANAGE, 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => PermissionCatalog::USERS_MANAGE, 'guard_name' => 'web']);
    }

    public function test_admin_can_view_settings_page()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Settings & Administration');
    }

    public function test_can_create_priority_level()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::SETTINGS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('activeTab', 'services')
            ->set('showPriorityCreate', true)
            ->set('newPriority.name', 'Critical')
            ->set('newPriority.color', '#ff0000')
            ->set('newPriority.response_time_minutes', 60)
            ->set('newPriority.resolution_time_minutes', 240)
            ->set('newPriority.description', 'Highest priority')
            ->call('createPriorityLevel')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('priority_levels', [
            'name' => 'Critical',
            'color' => '#ff0000',
            'response_time_minutes' => 60,
        ]);
    }

    public function test_can_create_role()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::USERS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('activeTab', 'users')
            ->set('showRoleCreate', true)
            ->set('newRole.name', 'Supervisor')
            ->call('createRole')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', [
            'name' => 'Supervisor',
            'guard_name' => 'web',
        ]);
    }

    public function test_can_update_company_profile()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);
        $user->givePermissionTo(PermissionCatalog::SETTINGS_MANAGE);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('activeTab', 'general')
            ->set('companyProfile.name', 'New Acme Corp')
            ->set('companyProfile.website', 'https://newacme.com')
            ->call('updateCompanyProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('system_settings', [
            'group' => 'company',
            'key' => 'name',
            'value' => json_encode('New Acme Corp'),
        ]);
    }

    public function test_can_switch_tabs()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::SETTINGS_VIEW);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\Index::class)
            ->set('activeTab', 'users')
            ->assertSet('activeTab', 'users')
            ->assertSeeHtml('Roles & Permissions'); // Content from users tab
    }
}
