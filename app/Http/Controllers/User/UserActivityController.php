<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserActivityController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('User/UserActivityView');
    }
}
