<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\DataTableResponse;
use App\Services\UserActivityService;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function __construct(
        private UserActivityService $userActivityService
    ) {}

    public function dataTable(Request $request)
    {
        $query = $this->userActivityService->getUserActivityQuery();

        return DataTableResponse::load($query);
    }
}
