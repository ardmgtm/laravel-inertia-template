<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwitchStatusRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Responses\DataTableResponse;
use App\Http\Responses\JsonResponse;
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

    public function dataTable(Request $request)
    {
        $query = User::query()->with(['roles']);
        return DataTableResponse::load($query);
    }

    public function create(CreateUserRequest $request)
    {
        $this->logActivity('Create new user');
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = User::create($data);
            $user->assignRole($data['roles']);
            DB::commit();
            return JsonResponse::success('Success to create user');
        } catch (Throwable $e) {
            DB::rollBack();
            return JsonResponse::failed('Failed to create user');
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->logActivity('Update user (id: ' . $user->id . ')');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $user->update($data);
            $user->syncRoles($data['roles']);
            DB::commit();
            return JsonResponse::success('Success to update user');
        } catch (Throwable $e) {
            DB::rollBack();
            return JsonResponse::failed('Failed to update user');
        }
    }

    public function delete(Request $request, User $user)
    {
        $this->logActivity('Delete user (id: ' . $user->id . ')');
        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();
            return JsonResponse::success('Success to delete user');
        } catch (Throwable $e) {
            DB::rollBack();
            return JsonResponse::failed('Failed to delete user');
        }
    }

    public function switchStatus(SwitchStatusRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $this->logActivity('Update user status (ids: ' . json_encode($data['ids']) . ')');
            User::whereIn('id', $data['ids'])->update(['is_active' => $data['status']]);
            DB::commit();
            return JsonResponse::success('Success to update status');
        } catch (Throwable $e) {
            DB::rollBack();
            return JsonResponse::failed('Failed to update status');
        }
    }
}
