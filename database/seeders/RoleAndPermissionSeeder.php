<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsName = $this->getPermissionList();

        $newPermissions = [];

        foreach ($permissionsName as $permissionGroup) {
            $groupName = $permissionGroup['group_name'];
            foreach ($permissionGroup['permissions'] as $permission) {
                $permissionName = $groupName . '.' . $permission;
                Permission::updateOrCreate([
                    'name' => $permissionName,
                ], [
                    'guard_name' => 'web'
                ]);
                $newPermissions[] = $permissionName;
            }
        }

        Permission::whereNotIn('name', $newPermissions)->delete();

        $superadminRole = Role::updateOrCreate([
            'name' => 'Superadmin'
        ], [
            'guard_name' => 'web'
        ]);

        $superadminRole->givePermissionTo(Permission::all());
        $superadminUser = User::where('username', 'admin')->first();
        $superadminUser->assignRole('Superadmin');

        Role::updateOrCreate([
            'name' => 'Viewer'
        ], [
            'guard_name' => 'web'
        ]);
    }

    private function getPermissionList()
    {
        return [
            [
                'group_name' => 'user',
                'permissions' => [
                    'browse',
                    'create',
                    'update',
                    'delete',
                    'assign_role',
                ]
            ],
            [
                'group_name' => 'role',
                'permissions' => [
                    'browse',
                    'create',
                    'update',
                    'delete',
                    'assign_permission',
                ]
            ],
            [
                'group_name' => 'user_activity',
                'permissions' => [
                    'browse',
                ]
            ],
        ];
    }
}
