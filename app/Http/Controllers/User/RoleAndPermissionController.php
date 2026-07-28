<?php

namespace App\Http\Controllers\User;

use App\Exceptions\RestrictActionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Http\Responses\InertiaFailedResponse;
use App\Http\Responses\InertiaSuccessResponse;
use App\Services\RoleAndPermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Throwable;

class RoleAndPermissionController extends Controller
{
    public function __construct(
        private RoleAndPermissionService $roleService
    ) {
    }

    public function index(Request $request)
    {
        $selectedRoleId = $request->query('role_id') ? (int) $request->query('role_id') : null;
        $selectedRole = $selectedRoleId ? Role::find($selectedRoleId) : null;

        $data = [
            'roles' => fn() => $this->roleService->getAllRoles(),
            'selectedRoleId' => $selectedRoleId,
            'rolePermissions' => $selectedRole
                ? fn() => $this->roleService->getRolePermissions($selectedRole)
                : null,
            'roleUsers' => $selectedRole
                ? fn() => $this->roleService->getRoleUsers($selectedRole)
                : null,
        ];

        return Inertia::render('User/UserRolePermissionManageView', $data);
    }

    public function create(RoleRequest $request)
    {
        $this->logActivity('Create new role');

        try {
            $validated = $request->validated();
            $this->roleService->createRole($validated);

            return InertiaSuccessResponse::redirectBack('Success to create role');
        } catch (Throwable $e) {
            return InertiaFailedResponse::redirectBack('Failed to create role', $e);
        }
    }

    public function update(RoleRequest $request, Role $role)
    {
        $this->logActivity('Update role (id: ' . $role->id . ', name: ' . $role->name . ')');

        try {
            $validated = $request->validated();
            $this->roleService->updateRole($role, $validated);

            return InertiaSuccessResponse::redirectBack('Success to update role');
        } catch (Throwable $e) {
            return InertiaFailedResponse::redirectBack('Failed to update role', $e);
        }
    }

    public function delete(Request $request, Role $role)
    {
        $this->logActivity('Delete role (id: ' . $role->id . ', name: ' . $role->name . ')');

        try {
            $this->roleService->deleteRole($role);

            return InertiaSuccessResponse::redirectBack('Success to delete role');
        } catch (RestrictActionException $e) {
            return InertiaFailedResponse::redirectBack($e->getMessage(), $e);
        } catch (Throwable $e) {
            return InertiaFailedResponse::redirectBack('Failed to delete role', $e);
        }
    }

    public function switchPermission(Request $request, Role $role)
    {
        try {
            $validated = $request->validate([
                'id_permission' => 'required|integer|exists:permissions,id',
                'value' => 'required|boolean',
            ]);

            $this->roleService->switchPermission($role, $validated['id_permission'], $validated['value']);

            return InertiaSuccessResponse::redirectBack('Success to update role permissions');
        } catch (Throwable $e) {
            return InertiaFailedResponse::redirectBack('Failed to update role permissions', $e);
        }
    }

    public function batchSwitchPermission(Request $request, Role $role)
    {
        $this->logActivity('Batch update role permissions (id: ' . $role->id . ', name: ' . $role->name . ')');

        try {
            $validated = $request->validate([
                'permissions' => 'required|array|min:1',
                'permissions.*.id_permission' => 'required|integer|exists:permissions,id',
                'permissions.*.value' => 'required|boolean',
            ]);

            $result = $this->roleService->batchSwitchPermissions($role, $validated['permissions']);

            if ($result['failed_count'] > 0) {
                $message = "Updated {$result['success_count']} permissions, {$result['failed_count']} failed";

                return InertiaSuccessResponse::redirectBack($message);
            }

            return InertiaSuccessResponse::redirectBack("Successfully updated {$result['success_count']} permissions");
        } catch (Throwable $e) {
            return InertiaFailedResponse::redirectBack('Failed to update role permissions', $e);
        }
    }
}
