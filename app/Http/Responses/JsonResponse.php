<?php

namespace App\Http\Responses;

class JsonResponse
{
    public static function success(string $message, array $data = [], int $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
    public static function failed(string $message, array $errors = [], int $status = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
