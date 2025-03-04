<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Traits\UserActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticationController extends Controller
{
    use UserActivityTrait;

    public function loginPage(Request $request)
    {
        return Inertia::render('LoginView');
    }
    public function login(LoginRequest $request)
    {
        $this->logActivity('User logged in');
        $request->authenticate();
        return redirect()->route('dashboard')->with('flash', ['message' => 'Login successful']);
    }
    public function logout(Request $request)
    {
        $this->logActivity('User logged out');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
