<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsName = $this->getPermissionList();

        foreach ($permissionsName as $permissionGroup) {
            $groupName = $permissionGroup['group_name'];
            foreach ($permissionGroup['permissions'] as $permission) {
                Permission::updateOrCreate([
                    'name' => $groupName . '.' . $permission,
                ], [
                    'guard_name' => 'web'
                ]);
            }
        }

        $superadminRole = Role::updateOrCreate([
            'name' => 'Superadmin'
        ], [
            'guard_name' => 'web'
        ]);

        $superadminRole->givePermissionTo(Permission::all());
        $superadminUser = User::where('username','admin')->first();
        $superadminUser->assignRole('superadmin');
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
                'group_name' => 'user_log',
                'permissions' => [
                    'browse',
                ]
            ],
        ];
    }
}
