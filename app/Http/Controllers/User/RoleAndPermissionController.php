<?php

namespace App\Http\Controllers\User;

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
    ) {}

    public function index(Request $request)
    {
        $data = [
            'roles' => fn () => $this->roleService->getAllRoles(),
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
        $this->logActivity('Update role (id: '.$role->id.', name: '.$role->name.')');

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
        $this->logActivity('Delete role (id: '.$role->id.', name: '.$role->name.')');

        try {
            $this->roleService->deleteRole($role);

            return InertiaSuccessResponse::redirectBack('Success to delete role');
        } catch (Throwable $e) {
            return InertiaFailedResponse::redirectBack('Failed to delete role', $e);
        }
    }

    public function getRolePermission(Request $request, Role $role)
    {
        try {
            $data = $this->roleService->getRolePermissions($role);

            return response()->json([
                'success' => true,
                'message' => 'Success to get permission list',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get permission list',
            ], 500);
        }
    }

    public function getRoleUser(Request $request, Role $role)
    {
        try {
            $data = $this->roleService->getRoleUsers($role);

            return response()->json([
                'success' => true,
                'message' => 'Success to get user list',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user list',
            ], 500);
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
}
