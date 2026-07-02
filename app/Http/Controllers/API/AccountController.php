<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateInformationRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Responses\JsonResponse;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;
use Throwable;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {}

    public function updateInformation(UpdateInformationRequest $request)
    {
        $this->logActivity('Updated user information');

        try {
            $data = $request->validated();
            $profilePicture = $request->hasFile('profile_picture') ? $request->file('profile_picture') : null;
            $user = $this->accountService->updateInformation($this->user(), $data, $profilePicture);

            return JsonResponse::success('Success to update your information', ['user' => $user]);
        } catch (Throwable $e) {
            DB::rollBack();

            return JsonResponse::failed('Failed to update your information');
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->logActivity('Change user password');

        try {
            $data = $request->validated();
            $this->accountService->changePassword($this->user(), $data['old_password'], $data['new_password']);

            return JsonResponse::success('Successfully changed your password');
        } catch (Throwable $e) {
            return JsonResponse::failed('Failed to change your password');
        }
    }
}
