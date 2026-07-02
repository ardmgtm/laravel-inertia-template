<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function updateInformation(User $user, array $data, ?UploadedFile $profilePicture = null): User
    {
        DB::beginTransaction();
        $user->update($data);
        if ($profilePicture && $profilePicture->isValid()) {
            $user->addMedia($profilePicture)
                ->toMediaCollection('profile_picture');
        }
        DB::commit();

        return $user->fresh();
    }

    public function changePassword(User $user, string $oldPassword, string $newPassword): void
    {
        if (! password_verify($oldPassword, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => 'The provided password does not match your current password',
            ]);
        }

        $user->password = bcrypt($newPassword);
        $user->save();
    }
}
