<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:' . PermissionCatalog::USERS_MANAGE);
    }

    /**
     * Display the permission matrix.
     */
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $roleLabels = RoleCatalog::labels();

        // Group permissions by module
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('admin.permissions.index', compact('roles', 'permissions', 'groupedPermissions', 'roleLabels'));
    }

    /**
     * Update role permissions.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Don't allow modifying admin role
        if ($role->name === RoleCatalog::ADMIN) {
            return back()->withErrors(['error' => 'Cannot modify admin role permissions.']);
        }

        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', "Permissions updated for {$role->name}.");
    }

    /**
     * Show users with role management.
     */
    public function users()
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->paginate(25);

        $roles = Role::orderBy('name')->get();
        $roleLabels = RoleCatalog::labels();

        return view('admin.permissions.users', compact('users', 'roles', 'roleLabels'));
    }

    /**
     * Update user roles.
     */
    public function updateUserRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($request->roles ?? []);

        return back()->with('success', "Roles updated for {$user->name}.");
    }

    /**
     * Sync permissions from catalog.
     */
    public function syncPermissions()
    {
        $catalogPermissions = PermissionCatalog::all();
        $created = 0;

        foreach ($catalogPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            if ($permission->wasRecentlyCreated) {
                $created++;
            }
        }

        // Sync role permissions
        foreach (PermissionCatalog::rolePermissions() as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        return back()->with('success', "Synced {$created} permissions from catalog.");
    }
}
