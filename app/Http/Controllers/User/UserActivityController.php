<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Responses\DataTableResponse;
use App\Services\UserActivityService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserActivityController extends Controller
{
    public function __construct(
        private UserActivityService $userActivityService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('User/UserActivityView');
    }

    public function dataTable(Request $request)
    {
        $query = $this->userActivityService->getUserActivityQuery();

        return DataTableResponse::load($query);
    }
}
