<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateInformationRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Account/AccountView');
    }
    public function updateInformation(UpdateInformationRequest $request)
    {
        $this->logActivity('Updated user information');
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = $this->user();
            $user->update($data);
            if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
                $user->addMedia($request->file('profile_picture'))
                    ->toMediaCollection('profile_picture');
            }
            DB::commit();
            return JsonResponse::success('Success to update your information', ['user' => $user]);
        } catch (\Exception $e) {
            DB::rollBack();
            return JsonResponse::failed('Failed to update your information');
        }
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        $this->logActivity('Change user password');
        $data = $request->validated();
        try {
            $user = $this->user();
            if (!password_verify($data['old_password'], $user->password)) {
                return JsonResponse::failed('The provided password does not match your current password');
            }

            $user->password = bcrypt($data['new_password']);
            $user->save();

            return JsonResponse::success('Successfully changed your password');
        } catch (\Exception $e) {
            return JsonResponse::failed('Failed to change your password');
        }
    }
}
