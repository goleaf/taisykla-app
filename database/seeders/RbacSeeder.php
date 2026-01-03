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

        foreach (PermissionCatalog::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (RoleCatalog::all() as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        foreach (RoleCatalog::all() as $roleName) {
            $role = Role::findByName($roleName, 'web');
            $permissions = PermissionCatalog::permissionsForRole($roleName);
            $role->syncPermissions($permissions);
        }
    }
}
