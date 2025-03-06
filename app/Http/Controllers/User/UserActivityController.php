<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Responses\DataTableResponse;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserActivityController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('User/UserActivityView');
    }

    public function dataTable(Request $request)
    {
        $query = UserActivity::with(['user']);
        return DataTableResponse::load($query);
    }
}
