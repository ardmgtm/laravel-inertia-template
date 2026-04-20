<?php

namespace App\Services;

use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Builder;

class UserActivityService
{
    public function getUserActivityQuery(): Builder
    {
        return UserActivity::query()->with(['user']);
    }
}
