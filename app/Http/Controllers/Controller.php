<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\UserActivityTrait;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    use UserActivityTrait;

    protected function user(): ?User
    {
        $user = Auth::user();
        $user = $user instanceof User ? $user : null;
        return $user;
    }
}
