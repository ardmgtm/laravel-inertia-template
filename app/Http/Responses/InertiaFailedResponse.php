<?php

namespace App\Http\Responses;

class InertiaFailedResponse
{
    public static function redirectBack(string $message)
    {
        return redirect()->back()->withErrors(['message' => $message]);
    }
}
