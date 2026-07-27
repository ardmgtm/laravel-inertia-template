<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateInformationRequest;
use App\Http\Requests\ChangePasswordRequest;
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

            return redirect()->back()->with('message', 'Success to update your information');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['message' => 'Failed to update your information']);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->logActivity('Change user password');

        try {
            $data = $request->validated();
            $this->accountService->changePassword($this->user(), $data['old_password'], $data['new_password']);

            return redirect()->back()->with('message', 'Successfully changed your password');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['message' => 'Failed to change your password']);
        }
    }
}
