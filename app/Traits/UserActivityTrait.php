<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait UserActivityTrait
{
    public function logActivity(Request $request,string $description): void
    {
        $request['record_activity'] = true;
        $request['activity_description'] = $description;
    }
}
