<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Responses\DataTableResponse;
use App\Http\Responses\JsonResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        $this->logActivity('Create new user');
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = User::create($data);
            $user->assignRole($data['roles']);
            DB::commit();
            return JsonResponse::success('Success to create user');
        } catch (ValidationException $e) {
            DB::rollBack();
            return JsonResponse::failed('Failed to create user', $e->errors(), 422);
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
        } catch (ValidationException $e) {
            DB::rollBack();
            return JsonResponse::failed('Failed to update user', $e->errors(), 422);
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

    public function dataTable(Request $request)
    {
        $query = User::query()->with(['roles']);
        return DataTableResponse::load($query);
    }
}
