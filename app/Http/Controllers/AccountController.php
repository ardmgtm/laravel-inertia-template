<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateInformationRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Responses\JsonResponse;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $data = $request->validated();
        $profilePicture = $request->hasFile('profile_picture') ? $request->file('profile_picture') : null;
        $result = $this->accountService->updateInformation($this->user(), $data, $profilePicture);

        return $result['success']
            ? JsonResponse::success($result['message'], ['user' => $result['user']])
            : JsonResponse::failed($result['message']);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->logActivity('Change user password');
        $data = $request->validated();
        $result = $this->accountService->changePassword($this->user(), $data['old_password'], $data['new_password']);

        return $result['success']
            ? JsonResponse::success($result['message'])
            : JsonResponse::failed($result['message']);
    }
}
