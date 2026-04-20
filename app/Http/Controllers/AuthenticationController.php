<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthenticationController extends Controller
{
    public function __construct(
        private AuthenticationService $authService
    ) {}

    public function loginPage(Request $request)
    {
        return Inertia::render('LoginView');
    }

    public function login(LoginRequest $request)
    {
        $this->logActivity('User logged in');
        $request->authenticate();

        $userData = $this->authService->getUserData($request->user());

        return redirect()->route('dashboard')->with(array_merge(
            ['message' => 'Login successful'],
            $userData
        ));
    }

    public function logout(Request $request)
    {
        $this->logActivity('User logged out');
        $this->authService->logout();

        return redirect()->route('login');
    }
}
