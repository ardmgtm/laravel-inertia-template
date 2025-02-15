<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $superadminRole = Role::updateOrCreate([
            'name' => 'Superadmin'
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
                'group_name' => 'user_log',
                'permissions' => [
                    'browse',
                ]
            ],
        ];
    }
}
