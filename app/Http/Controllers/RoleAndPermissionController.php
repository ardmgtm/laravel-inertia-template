<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleAndPermissionController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('User/UserRolePermissionManageView');
    }
}
