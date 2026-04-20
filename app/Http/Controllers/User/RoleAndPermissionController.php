<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Http\Requests\Role\UpdateRolePermissionRequest;
use App\Http\Responses\JsonResponse;
use App\Services\RoleAndPermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

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
        $validated = $request->validated();
        $result = $this->roleService->createRole($validated);

        return $result['success']
            ? redirect()->back()->with('message', $result['message'])
            : redirect()->back()->withErrors(['message' => $result['message']]);
    }

    public function update(RoleRequest $request, Role $role)
    {
        $this->logActivity('Update role (id: '.$role->id.')');
        $validated = $request->validated();
        $result = $this->roleService->updateRole($role, $validated);

        return $result['success']
            ? redirect()->back()->with('message', $result['message'])
            : redirect()->back()->withErrors(['message' => $result['message']]);
    }

    public function delete(Request $request, Role $role)
    {
        $this->logActivity('Delete role (id: '.$role->id.')');
        $result = $this->roleService->deleteRole($role);

        return $result['success']
            ? redirect()->back()->with('message', $result['message'])
            : redirect()->back()->withErrors(['message' => $result['message']]);
    }

    public function getRolePermission(Request $request, Role $role)
    {
        $result = $this->roleService->getRolePermissions($role);

        return $result['success']
            ? JsonResponse::success('Success to get permission list', $result['data'])
            : JsonResponse::failed($result['message']);
    }

    public function getRoleUser(Request $request, Role $role)
    {
        $result = $this->roleService->getRoleUsers($role);

        return $result['success']
            ? JsonResponse::success('Success to get user list', $result['data'])
            : JsonResponse::failed($result['message']);
    }

    public function switchPermission(UpdateRolePermissionRequest $request, Role $role)
    {
        $validated = $request->validated();
        $result = $this->roleService->switchPermission($role, $validated['id_permission'], $validated['value']);

        if (! $result['success'] && isset($result['code'])) {
            return JsonResponse::failed($result['message'], [], $result['code']);
        }

        return $result['success']
            ? JsonResponse::success($result['message'])
            : JsonResponse::failed($result['message']);
    }
}
