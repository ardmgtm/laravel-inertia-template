<?php

namespace App\Http\Responses;

class InertiaSuccessResponse
{
    public static function redirectBack(string $message)
    {
        return redirect()->back()->with('message', $message);
    }
}
