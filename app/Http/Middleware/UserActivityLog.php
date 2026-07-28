<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use App\Services\SensitiveDataMasker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserActivityLog
{
    public function __construct(
        protected SensitiveDataMasker $masker
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Store start time in request attributes (persists across middleware lifecycle)
        $request->attributes->set('activity_start_time', microtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $request->files->replace([]);
            if ($request->boolean('record_activity')) {
                $this->recordActivity($request, $response);
            }
        } catch (\Throwable) {
            return;
        }
    }

    protected function recordActivity(Request $request, Response $response): void
    {
        $user = Auth::user();
        
        // Calculate duration from request attribute
        $startTime = $request->attributes->get('activity_start_time');
        $durationMs = $startTime
            ? round((microtime(true) - $startTime) * 1000)
            : null;

        // Get response content
        $responseContent = null;
        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            if ($content && $this->isJson($content)) {
                $responseContent = json_decode($content, true);
            }
        }

        // Get error message from session flash or request
        $errorMessage = session('error_message') ?? $request->input('error_message');

        // Get request payload (exclude sensitive data)
        $requestPayload = $request->except([
            'record_activity',
            'activity_description',
            'error_message',
        ]);

        // Determine status: true = success, false = failed
        $statusCode = $response->getStatusCode();
        $status = null;
        if ($errorMessage || $statusCode >= 400) {
            $status = false; // Failed: has error message or 4xx/5xx status
        } elseif ($statusCode < 400) {
            $status = true; // Success: 2xx or 3xx status without error
        }

        UserActivity::create([
            'timestamp' => now(),
            'user_id' => $user?->id,
            'method' => $request->method(),
            'status_code' => $statusCode,
            'route_name' => $request->route()?->getName(),
            'route' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'description' => $request['activity_description'] ?? '-',
            'request_payload' => $this->masker->mask($requestPayload),
            'response' => $responseContent,
            'duration_ms' => $durationMs,
            'error_message' => $errorMessage,
            'status' => $status,
        ]);
    }

    protected function isJson(string $content): bool
    {
        json_decode($content);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
