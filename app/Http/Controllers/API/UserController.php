<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwitchStatusRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Responses\DataTableResponse;
use App\Http\Responses\JsonResponse;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

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

            return JsonResponse::success('Success to create user');
        } catch (Throwable $e) {
            DB::rollBack();

            return JsonResponse::failed('Failed to create user');
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->logActivity('Update user (id: '.$user->id.')');

        try {
            $data = $request->validated();
            $this->userService->updateUser($user, $data);

            return JsonResponse::success('Success to update user');
        } catch (Throwable $e) {
            DB::rollBack();

            return JsonResponse::failed('Failed to update user');
        }
    }

    public function delete(Request $request, User $user)
    {
        $this->logActivity('Delete user (id: '.$user->id.')');

        try {
            $this->userService->deleteUser($user);

            return JsonResponse::success('Success to delete user');
        } catch (Throwable $e) {
            DB::rollBack();

            return JsonResponse::failed('Failed to delete user');
        }
    }

    public function switchStatus(SwitchStatusRequest $request)
    {
        $data = $request->validated();
        $this->logActivity('Update user status (ids: '.json_encode($data['ids']).')');

        try {
            $this->userService->switchStatus($data['ids'], $data['status']);

            return JsonResponse::success('Success to update status');
        } catch (Throwable $e) {
            DB::rollBack();

            return JsonResponse::failed('Failed to update status');
        }
    }
}
