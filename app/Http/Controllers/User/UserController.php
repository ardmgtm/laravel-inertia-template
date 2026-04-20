<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwitchStatusRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Responses\DataTableResponse;
use App\Http\Responses\JsonResponse;
use App\Models\User;
use App\Services\RoleAndPermissionService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private RoleAndPermissionService $roleService
    ) {}

    public function index(Request $request)
    {
        $data = [
            'roles' => $this->roleService->getAllRoles(),
        ];

        return Inertia::render('User/UserManageView', $data);
    }

    public function dataTable(Request $request)
    {
        $query = $this->userService->getUserQuery();

        return DataTableResponse::load($query);
    }

    public function create(CreateUserRequest $request)
    {
        $this->logActivity('Create new user');
        $data = $request->validated();
        $result = $this->userService->createUser($data);

        return $result['success']
            ? JsonResponse::success($result['message'])
            : JsonResponse::failed($result['message']);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->logActivity('Update user (id: '.$user->id.')');
        $data = $request->validated();
        $result = $this->userService->updateUser($user, $data);

        return $result['success']
            ? JsonResponse::success($result['message'])
            : JsonResponse::failed($result['message']);
    }

    public function delete(Request $request, User $user)
    {
        $this->logActivity('Delete user (id: '.$user->id.')');
        $result = $this->userService->deleteUser($user);

        return $result['success']
            ? JsonResponse::success($result['message'])
            : JsonResponse::failed($result['message']);
    }

    public function switchStatus(SwitchStatusRequest $request)
    {
        $data = $request->validated();
        $this->logActivity('Update user status (ids: '.json_encode($data['ids']).')');
        $result = $this->userService->switchStatus($data['ids'], $data['status']);

        return $result['success']
            ? JsonResponse::success($result['message'])
            : JsonResponse::failed($result['message']);
    }
}
