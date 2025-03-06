<?php

namespace App\Traits;

trait UserActivityTrait
{
    public function logActivity(string $description): void
    {
        $request = request();
        $request['record_activity'] = true;
        $request['activity_description'] = $description;
    }
}
