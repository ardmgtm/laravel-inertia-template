<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class AccountService
{
    public function updateInformation(User $user, array $data, ?UploadedFile $profilePicture = null): array
    {
        DB::beginTransaction();
        try {
            $user->update($data);
            if ($profilePicture && $profilePicture->isValid()) {
                $user->addMedia($profilePicture)
                    ->toMediaCollection('profile_picture');
            }
            DB::commit();

            return ['success' => true, 'message' => 'Success to update your information', 'user' => $user];
        } catch (Throwable $e) {
            DB::rollBack();

            return ['success' => false, 'message' => 'Failed to update your information'];
        }
    }

    public function changePassword(User $user, string $oldPassword, string $newPassword): array
    {
        try {
            if (! password_verify($oldPassword, $user->password)) {
                return ['success' => false, 'message' => 'The provided password does not match your current password'];
            }

            $user->password = bcrypt($newPassword);
            $user->save();

            return ['success' => true, 'message' => 'Successfully changed your password'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Failed to change your password'];
        }
    }
}
