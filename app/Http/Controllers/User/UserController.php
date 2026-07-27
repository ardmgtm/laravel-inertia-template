<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwitchStatusRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Responses\DataTableResponse;
use App\Http\Responses\InertiaFailedResponse;
use App\Http\Responses\InertiaSuccessResponse;
use App\Models\User;
use App\Services\RoleAndPermissionService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        private RoleAndPermissionService $roleService,
        private UserService $userService
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

        try {
            $data = $request->validated();
            $this->userService->createUser($data);

            return InertiaSuccessResponse::redirectBack('Success to create user');
        } catch (Throwable $e) {
            DB::rollBack();

            return InertiaFailedResponse::redirectBack('Failed to create user', $e);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->logActivity('Update user (id: '.$user->id.')');

        try {
            $data = $request->validated();
            $this->userService->updateUser($user, $data);

            return InertiaSuccessResponse::redirectBack('Success to update user');
        } catch (Throwable $e) {
            DB::rollBack();

            return InertiaFailedResponse::redirectBack('Failed to update user', $e);
        }
    }

    public function delete(Request $request, User $user)
    {
        $this->logActivity('Delete user (id: '.$user->id.')');

        try {
            $this->userService->deleteUser($user);

            return InertiaSuccessResponse::redirectBack('Success to delete user');
        } catch (Throwable $e) {
            DB::rollBack();

            return InertiaFailedResponse::redirectBack('Failed to delete user', $e);
        }
    }

    public function switchStatus(SwitchStatusRequest $request)
    {
        $data = $request->validated();
        $this->logActivity('Update user status (ids: '.json_encode($data['ids']).')');

        try {
            $this->userService->switchStatus($data['ids'], $data['status']);

            return InertiaSuccessResponse::redirectBack('Success to update status');
        } catch (Throwable $e) {
            DB::rollBack();

            return InertiaFailedResponse::redirectBack('Failed to update status', $e);
        }
    }
}
