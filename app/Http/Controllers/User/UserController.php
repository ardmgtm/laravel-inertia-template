<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Responses\DataTableResponse;
use App\Http\Responses\InertiaFailedResponse;
use App\Http\Responses\InertiaSuccessResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->logActivity($request, 'View user management page');
        return Inertia::render('User/UserManageView');
    }

    public function store(CreateUserRequest $request)
    {
        $this->logActivity($request, 'Create new user');
        $data = $request->validated();
        DB::beginTransaction();
        try {
            User::create($data);
            DB::commit();
            return InertiaSuccessResponse::redirectBack('Success to create user');
        } catch (Throwable $e) {
            DB::rollBack();
            return InertiaFailedResponse::redirectBack('Failed to create user');
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->logActivity($request, 'Update user (id: ' . $user->id . ')');
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user->update($data);
            DB::commit();
            return InertiaSuccessResponse::redirectBack('Success to update user');
        } catch (Throwable $e) {
            DB::rollBack();
            return InertiaFailedResponse::redirectBack('Failed to update user');
        }
    }

    public function destroy(Request $request, User $user)
    {
        $this->logActivity($request, 'Delete user (id: ' . $user->id . ')');
        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();
            return InertiaSuccessResponse::redirectBack('Success to delete user');
        } catch (Throwable $e) {
            DB::rollBack();
            return InertiaFailedResponse::redirectBack('Failed to delete user');
        }
    }

    public function dataTable(Request $request)
    {
        $query = User::query();
        return DataTableResponse::load($request, $query);
    }
}
