<?php

use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        if (! $tableNames) {
            return;
        }

        $rolesTable = $tableNames['roles'] ?? 'roles';
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';
        $rolePermissionsTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';

        if (! Schema::hasTable($rolesTable)
            || ! Schema::hasTable($permissionsTable)
            || ! Schema::hasTable($rolePermissionsTable)) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        foreach (PermissionCatalog::all() as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        foreach (RoleCatalog::all() as $roleName) {
            Role::findOrCreate($roleName, $guard);
        }

        foreach (RoleCatalog::all() as $roleName) {
            $role = Role::findByName($roleName, $guard);
            $role->syncPermissions(PermissionCatalog::permissionsForRole($roleName));
        }
    }

    public function down(): void
    {
    }
};
