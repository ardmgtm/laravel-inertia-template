<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('User/UserManageView');
    }

    public function store(CreateUserRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            User::create($data);
            DB::commit();
            return redirect()->back()->with('message', 'Success to create user');
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'message' => 'Failed to create user'
            ]);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user->update($data);
            DB::commit();
            return redirect()->back()->with('message', 'Success to update user');
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'message' => 'Failed to update user'
            ]);
        }
    }

    public function destroy(Request $request, User $user)
    {
        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();
            return redirect()->back()->with('message', 'Success to delete user');
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'message' => 'Failed to delete user'
            ]);
        }
    }
}
