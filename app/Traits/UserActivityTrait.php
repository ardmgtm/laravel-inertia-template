<?php

namespace App\Traits;

trait UserActivityTrait
{
    public function logActivity(string $description): void
    {
        $request = request();
        $request->merge([
            'record_activity' => true,
            'activity_description' => $description,
        ]);
    }
}
