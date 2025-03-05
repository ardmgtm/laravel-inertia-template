<?php

namespace App\Traits;

trait UserActivityTrait
{
    public function logActivity(string $description): void
    {
        session()->put('record_activity', true);
        session()->put('activity_description', $description);
    }
}
