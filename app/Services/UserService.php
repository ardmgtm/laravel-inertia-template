<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserService
{
    public function getUserQuery(): Builder
    {
        return User::query()->with(['roles']);
    }

    public function createUser(array $data): array
    {
        DB::beginTransaction();
        try {
            $user = User::create($data);
            $user->assignRole($data['roles']);
            DB::commit();

            return ['success' => true, 'message' => 'Success to create user'];
        } catch (Throwable $e) {
            DB::rollBack();

            return ['success' => false, 'message' => 'Failed to create user'];
        }
    }

    public function updateUser(User $user, array $data): array
    {
        DB::beginTransaction();
        try {
            $user->update($data);
            $user->syncRoles($data['roles']);
            DB::commit();

            return ['success' => true, 'message' => 'Success to update user'];
        } catch (Throwable $e) {
            DB::rollBack();

            return ['success' => false, 'message' => 'Failed to update user'];
        }
    }

    public function deleteUser(User $user): array
    {
        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();

            return ['success' => true, 'message' => 'Success to delete user'];
        } catch (Throwable $e) {
            DB::rollBack();

            return ['success' => false, 'message' => 'Failed to delete user'];
        }
    }

    public function switchStatus(array $ids, bool $status): array
    {
        DB::beginTransaction();
        try {
            User::whereIn('id', $ids)->update(['is_active' => $status]);
            DB::commit();

            return ['success' => true, 'message' => 'Success to update status'];
        } catch (Throwable $e) {
            DB::rollBack();

            return ['success' => false, 'message' => 'Failed to update status'];
        }
    }
}
