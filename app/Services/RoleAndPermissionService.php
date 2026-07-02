<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionService
{
    private const SUPERADMIN_ROLE_ID = 1;

    public function getAllRoles()
    {
        return Role::all();
    }

    public function createRole(array $data): Role
    {
        return Role::create($data);
    }

    public function updateRole(Role $role, array $data): Role
    {
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            throw new Exception('Superadmin cannot be changed');
        }

        $role->update($data);

        return $role->fresh();
    }

    public function deleteRole(Role $role): void
    {
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            throw new Exception('Superadmin cannot be deleted');
        }

        if ($role->users()->count() > 0) {
            throw new Exception('Role cannot be deleted because it has users');
        }

        $role->delete();
    }

    public function getRolePermissions(Role $role): array
    {
        $roleId = $role->id;
        $permissions = Permission::with(['roles' => function ($query) use ($roleId) {
            $query->where('role_id', $roleId);
        }])
            ->get()
            ->map(function ($permission) use ($roleId) {
                $permission->role_has_permission = $permission->roles->contains('id', $roleId);

                return $permission;
            });
        $permissions = $permissions->map(function ($p) {
            $p->role_has_permission = boolval($p->role_has_permission);

            return $p;
        });
        $permissionsGrouped = $permissions->groupBy(function ($item) {
            return explode('.', $item['name'])[0];
        });

        return [
            'permissions' => $permissionsGrouped,
            'total_assigned_permission' => $permissions->where('role_has_permission', 1)->count(),
        ];
    }

    public function getRoleUsers(Role $role): array
    {
        $roleId = $role->id;
        $users = User::with(['roles'])
            ->whereHas('roles', fn ($q) => $q->where('id', '=', $roleId))
            ->get();

        return [
            'users' => $users,
            'user_count' => $users->count(),
        ];
    }

    public function switchPermission(Role $role, int $permissionId, bool $value): void
    {
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            throw new Exception('Superadmin cannot be changed', 403);
        }

        $permission = Permission::find($permissionId);
        if ($value) {
            $role->givePermissionTo($permission);
        } else {
            $role->revokePermissionTo($permission);
        }
    }
}
