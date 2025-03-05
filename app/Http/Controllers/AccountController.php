<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateInformationRequest;
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
        $data = $request->validated();
        DB::beginTransaction();
        try{
            $user = Auth::user();
            $user = $user instanceof User ? $user : null;
            $user->update($data);
            if(isset($data['profile_picture'])){
                $user->addMedia($data['profile_picture'])
                    ->usingFileName(Str::random(20) . '.' . $data['profile_picture']->getClientOriginalExtension())
                    ->toMediaCollection('profile_picture');
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
