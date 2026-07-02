<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Http\Requests\Role\UpdateRolePermissionRequest;
use App\Http\Responses\JsonResponse;
use App\Services\RoleAndPermissionService;
use Exception;
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

            return redirect()->back()->with('message', 'Success to create role');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['message' => 'Failed to create role']);
        }
    }

    public function update(RoleRequest $request, Role $role)
    {
        $this->logActivity('Update role (id: '.$role->id.')');

        try {
            $validated = $request->validated();
            $this->roleService->updateRole($role, $validated);

            return redirect()->back()->with('message', 'Success to update role');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['message' => 'Failed to update role']);
        }
    }

    public function delete(Request $request, Role $role)
    {
        $this->logActivity('Delete role (id: '.$role->id.')');

        try {
            $this->roleService->deleteRole($role);

            return redirect()->back()->with('message', 'Success to delete role');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['message' => 'Failed to delete role']);
        }
    }

    public function getRolePermission(Request $request, Role $role)
    {
        try {
            $data = $this->roleService->getRolePermissions($role);

            return JsonResponse::success('Success to get permission list', $data);
        } catch (Throwable $e) {
            return JsonResponse::failed('Failed to get permission list');
        }
    }

    public function getRoleUser(Request $request, Role $role)
    {
        try {
            $data = $this->roleService->getRoleUsers($role);

            return JsonResponse::success('Success to get user list', $data);
        } catch (Throwable $e) {
            return JsonResponse::failed('Failed to get user list');
        }
    }

    public function switchPermission(UpdateRolePermissionRequest $request, Role $role)
    {
        try {
            $validated = $request->validated();
            $this->roleService->switchPermission($role, $validated['id_permission'], $validated['value']);

            return JsonResponse::success('Success to update role permissions');
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            return JsonResponse::failed($e->getMessage(), [], $statusCode);
        } catch (Throwable $e) {
            return JsonResponse::failed('Failed to update role permissions');
        }
    }
}
