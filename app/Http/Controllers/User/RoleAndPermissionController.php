<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Http\Requests\Role\UpdateRolePermissionRequest;
use App\Http\Responses\JsonResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionController extends Controller
{
    private const SUPERADMIN_ROLE_ID = 1;

    public function index(Request $request)
    {
        $data = [
            'roles' => fn() => Role::all(),
        ];
        return Inertia::render('User/UserRolePermissionManageView', $data);
    }

    public function create(RoleRequest $request)
    {
        $this->logActivity('Create new role');
        $validated = $request->validated();
        Role::create($validated);
        return redirect()->back()->with('message', 'Success to create role');
    }

    public function update(RoleRequest $request, Role $role)
    {
        $this->logActivity('Update role (id: ' . $role->id . ')');
        $validated = $request->validated();
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            return redirect()->back()->withErrors(['message' => 'Superadmin cannot be changed']);
        }
        $role->update($validated);
        return redirect()->back()->with('message', 'Success to update role');
    }

    public function delete(Request $request, Role $role)
    {
        $this->logActivity('Delete role (id: ' . $role->id . ')');
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            return redirect()->back()->withErrors(['message' => 'Superadmin cannot be deleted']);
        }

        if ($role->users()->count() > 0) {
            return redirect()->back()->withErrors(['message' => 'Role cannot be deleted because it has users']);
        }

        $role->delete();
        return redirect()->back()->with('message', 'Success to delete role');
    }

    public function getRolePermission(Request $request, Role $role)
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
            $data = [
                'permissions' => $permissionsGrouped,
                'total_assigned_permission' => $permissions->where('role_has_permission', 1)->count(),
            ];
            return JsonResponse::success('Success to get permission list', $data);
        } catch (\Throwable $th) {
            return JsonResponse::failed('Failed to get permission list');
        }
    }

    public function getRoleUser(Request $request, Role $role)
    {
        try {
            $roleId = $role->id;
            $users = User::with(['roles'])
                ->whereHas('roles', fn($q) => $q->where('id', '=', $roleId))
                ->get();
            $data = [
                'users' => $users,
                'user_count' => $users->count(),
            ];
            return JsonResponse::success('Success to get user list', $data);
        } catch (\Throwable $th) {
            return JsonResponse::failed('Failed to get user list');
        }
    }

    public function switchPermission(UpdateRolePermissionRequest $request, Role $role)
    {
        $validated = $request->validated();
        if ($role->id == self::SUPERADMIN_ROLE_ID) {
            return JsonResponse::failed('Superadmin cannot be changed', [], 403);
        }
        try {
            $permission = Permission::find($validated['id_permission']);
            if ($validated['value']) {
                $role->givePermissionTo($permission);
            } else {
                $role->revokePermissionTo($permission);
            }
            return JsonResponse::success('Success to update role permissions');
        } catch (\Throwable $th) {
            return JsonResponse::failed('Failed to update role permissions');
        }
    }
}
