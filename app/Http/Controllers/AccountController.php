<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateInformationRequest;
use App\Http\Responses\InertiaFailedResponse;
use App\Http\Responses\InertiaSuccessResponse;
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
        $data = $request->validated();
        DB::beginTransaction();
        try{
            $user = auth()->user();
            $user->update($data);
            if(isset($data['profilePicture'])){
                $user->addMedia($data['profilePicture'])->toMediaCollection('profile_picture');
            }
            DB::commit();
            return InertiaSuccessResponse::redirectBack('Successfully updated your information');
        }catch(\Exception $e){
            dd($e);
            DB::rollBack();
            return InertiaFailedResponse::redirectBack('Failed to update your information');
        }
    }
}
