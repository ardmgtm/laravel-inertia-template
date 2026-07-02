<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\RoleAndPermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private RoleAndPermissionService $roleService
    ) {}

    public function index(Request $request)
    {
        $data = [
            'roles' => $this->roleService->getAllRoles(),
        ];

        return Inertia::render('User/UserManageView', $data);
    }
}
