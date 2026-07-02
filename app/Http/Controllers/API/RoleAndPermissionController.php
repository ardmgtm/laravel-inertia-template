<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\UpdateRolePermissionRequest;
use App\Http\Responses\JsonResponse;
use App\Services\RoleAndPermissionService;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Throwable;

class RoleAndPermissionController extends Controller
{
    public function __construct(
        private RoleAndPermissionService $roleService
    ) {}

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
