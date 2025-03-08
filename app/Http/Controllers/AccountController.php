<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateInformationRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Responses\InertiaFailedResponse;
use App\Http\Responses\InertiaSuccessResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            if (isset($data['profile_picture'])) {
                $user->addMedia($data['profile_picture'])
                    ->usingFileName(Str::random(20) . '.' . $data['profile_picture']->getClientOriginalExtension())
                    ->toMediaCollection('profile_picture');
            }
            DB::commit();
            return InertiaSuccessResponse::redirectBack('Successfully updated your information');
        } catch (\Exception $e) {
            DB::rollBack();
            return InertiaFailedResponse::redirectBack('Failed to update your information');
        }
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        $this->logActivity('Change user password');
        $data = $request->validated();
        try {
            $user = $this->user();
            if (!password_verify($data['old_password'], $user->password)) {
                return InertiaFailedResponse::redirectBack('The provided password does not match your current password');
            }

            $user->password = bcrypt($data['new_password']);
            $user->save();

            return InertiaSuccessResponse::redirectBack('Successfully changed your password');
        } catch (\Exception $e) {
            return InertiaFailedResponse::redirectBack('Failed to change your password');
        }
    }
}
