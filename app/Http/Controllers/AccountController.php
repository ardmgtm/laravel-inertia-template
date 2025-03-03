<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdateInformationRequest;
use Illuminate\Http\Request;
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
        dd($data);
    }
}
