<?php

namespace Database\Seeders;

use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Create every permission, then the roles, then wire the
     * role -> permission matrix. Safe to run repeatedly.
     */
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        // Clear any stale cache before writing.
        $registrar->forgetCachedPermissions();

        foreach (Permissions::ALL as $permission) {
            Permission::findOrCreate($permission, config('auth.defaults.guard'));
        }

        // Rebuild so the role sync below sees the just-created rows.
        // (Model events may be disabled while seeding, in which case
        // Laravel-permission cannot refresh its cache automatically.)
        $registrar->forgetCachedPermissions();

        foreach (Roles::ALL as $roleName) {
            $role = Role::findOrCreate($roleName, config('auth.defaults.guard'));

            $role->syncPermissions(
                Permissions::rolePermissions()[$roleName] ?? []
            );
        }

        // Rebuild once more so later lookups in this request see the
        // final role -> permission matrix.
        $registrar->forgetCachedPermissions();
    }
}
