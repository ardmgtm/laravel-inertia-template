<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticationController extends Controller
{
    public function loginPage(Request $request)
    {
        return Inertia::render('LoginView');
    }
    public function login(LoginRequest $request)
    {
        $this->logActivity('User logged in');
        $request->authenticate();
        return redirect()->route('dashboard')->with([
            'message' => 'Login successful',
            'user' => [
                'id' => $request->user()?->id,
                'name' => $request->user()?->name,
                'username' => $request->user()?->username,
                'email' => $request->user()?->email,
                'profile_picture' => $request->user()?->profile_picture,
            ],
            'roles' => $request->user()?->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            }),
            'permissions' => $request->user()?->getAllPermissions()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            }),
        ]);
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
