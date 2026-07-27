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
    protected float $startTime;

    public function __construct(
        protected SensitiveDataMasker $masker
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->startTime = microtime(true);

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
        $durationMs = isset($this->startTime)
            ? round((microtime(true) - $this->startTime) * 1000)
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

        UserActivity::create([
            'timestamp' => now(),
            'user_id' => $user?->id,
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'route_name' => $request->route()?->getName(),
            'route' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'description' => $request['activity_description'] ?? '-',
            'request_payload' => $this->masker->mask($requestPayload),
            'response' => $responseContent,
            'duration_ms' => $durationMs,
            'error_message' => $errorMessage,
        ]);
    }

    protected function isJson(string $content): bool
    {
        json_decode($content);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
