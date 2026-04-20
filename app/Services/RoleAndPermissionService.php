<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class RoleAndPermissionService
{
    private const SUPERADMIN_ROLE_ID = 1;

    public function getAllRoles()
    {
        return Role::all();
    }

    public function createRole(array $data): array
    {
        try {
            Role::create($data);

            return ['success' => true, 'message' => 'Success to create role'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Failed to create role'];
        }
    }

    public function updateRole(Role $role, array $data): array
    {
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            return ['success' => false, 'message' => 'Superadmin cannot be changed'];
        }

        try {
            $role->update($data);

            return ['success' => true, 'message' => 'Success to update role'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Failed to update role'];
        }
    }

    public function deleteRole(Role $role): array
    {
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            return ['success' => false, 'message' => 'Superadmin cannot be deleted'];
        }

        if ($role->users()->count() > 0) {
            return ['success' => false, 'message' => 'Role cannot be deleted because it has users'];
        }

        try {
            $role->delete();

            return ['success' => true, 'message' => 'Success to delete role'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Failed to delete role'];
        }
    }

    public function getRolePermissions(Role $role): array
    {
        try {
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
                'success' => true,
                'data' => [
                    'permissions' => $permissionsGrouped,
                    'total_assigned_permission' => $permissions->where('role_has_permission', 1)->count(),
                ],
            ];
        } catch (Throwable $th) {
            return ['success' => false, 'message' => 'Failed to get permission list'];
        }
    }

    public function getRoleUsers(Role $role): array
    {
        try {
            $roleId = $role->id;
            $users = User::with(['roles'])
                ->whereHas('roles', fn ($q) => $q->where('id', '=', $roleId))
                ->get();

            return [
                'success' => true,
                'data' => [
                    'users' => $users,
                    'user_count' => $users->count(),
                ],
            ];
        } catch (Throwable $th) {
            return ['success' => false, 'message' => 'Failed to get user list'];
        }
    }

    public function switchPermission(Role $role, int $permissionId, bool $value): array
    {
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            return ['success' => false, 'message' => 'Superadmin cannot be changed', 'code' => 403];
        }

        try {
            $permission = Permission::find($permissionId);
            if ($value) {
                $role->givePermissionTo($permission);
            } else {
                $role->revokePermissionTo($permission);
            }

            return ['success' => true, 'message' => 'Success to update role permissions'];
        } catch (Throwable $th) {
            return ['success' => false, 'message' => 'Failed to update role permissions'];
        }
    }
}
