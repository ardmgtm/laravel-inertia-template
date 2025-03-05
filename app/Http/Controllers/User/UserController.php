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
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'roles' => Role::all(),
        ];
        return Inertia::render('User/UserManageView', $data);
    }

    public function create(CreateUserRequest $request)
    {
        $this->logActivity($request, 'Create new user');
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = User::create($data);
            $user->assignRole($data['roles']);
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
            $user->syncRoles($data['roles']);
            DB::commit();
            return InertiaSuccessResponse::redirectBack('Success to update user');
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
            return InertiaFailedResponse::redirectBack('Failed to update user');
        }
    }

    public function delete(Request $request, User $user)
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
        $query = User::query()->with(['roles']);
        return DataTableResponse::load($request, $query);
    }
}
