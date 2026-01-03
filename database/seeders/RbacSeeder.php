<?php

namespace Database\Seeders;

use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
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
            $permissions = PermissionCatalog::permissionsForRole($roleName);
            $role->syncPermissions($permissions);
        }
    }
}
