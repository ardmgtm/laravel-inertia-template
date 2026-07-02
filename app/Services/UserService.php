<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function getUserQuery(): Builder
    {
        return User::query()->with(['roles']);
    }

    public function createUser(array $data): User
    {
        DB::beginTransaction();
        $user = User::create($data);
        $user->assignRole($data['roles']);
        DB::commit();

        return $user->fresh(['roles']);
    }

    public function updateUser(User $user, array $data): User
    {
        DB::beginTransaction();
        $user->update($data);
        $user->syncRoles($data['roles']);
        DB::commit();

        return $user->fresh(['roles']);
    }

    public function deleteUser(User $user): void
    {
        DB::beginTransaction();
        $user->delete();
        DB::commit();
    }

    public function switchStatus(array $ids, bool $status): void
    {
        DB::beginTransaction();
        User::whereIn('id', $ids)->update(['is_active' => $status]);
        DB::commit();
    }
}
