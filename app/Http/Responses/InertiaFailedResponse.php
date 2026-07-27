<?php
namespace App\Http\Responses;

use Throwable;

class InertiaFailedResponse
{
    public static function redirectBack(string $message, Throwable | null $e = null)
    {
        if ($e) {
            logger()->error($e->getMessage(), ['exception' => $e]);
            request()->merge(['error_message' => $e->getMessage()]);
        }

        return redirect()->back()->withErrors(['message' => $message]);
    }
}
