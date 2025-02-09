<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthenticationController extends Controller
{
    public function loginPage(Request $request){
        return Inertia::render('LoginView');
    }
    public function login(LoginRequest $request){
        $request->authenticate();
        return redirect()->route('dashboard')->with('flash', ['message' => 'Login successful']);
    }
    public function logout(Request $request){
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
