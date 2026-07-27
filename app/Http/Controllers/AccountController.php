<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateInformationRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Responses\InertiaFailedResponse;
use App\Http\Responses\InertiaSuccessResponse;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Account/AccountView');
    }

    public function updateInformation(UpdateInformationRequest $request)
    {
        $this->logActivity('Updated user information');

        try {
            $data = $request->validated();
            $profilePicture = $request->hasFile('profile_picture') ? $request->file('profile_picture') : null;
            $user = $this->accountService->updateInformation($this->user(), $data, $profilePicture);

            return InertiaSuccessResponse::redirectBack('Success to update your information');
        } catch (Throwable $e) {
            return InertiaFailedResponse::redirectBack('Failed to update your information', $e);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->logActivity('Change user password');

        try {
            $data = $request->validated();
            $this->accountService->changePassword($this->user(), $data['old_password'], $data['new_password']);

            return InertiaSuccessResponse::redirectBack('Successfully changed your password');
        } catch (Throwable $e) {
            return InertiaFailedResponse::redirectBack('Failed to change your password', $e);
        }
    }
}
