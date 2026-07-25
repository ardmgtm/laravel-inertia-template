<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserActivityLog
{
    protected float $startTime;

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

        // Get request payload (exclude sensitive data)
        $requestPayload = $this->sanitizeRequestPayload($request->except([
            'record_activity',
            'activity_description',
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
        ]));

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
            'request_payload' => json_encode($requestPayload),
            'response' =>   json_encode($responseContent),
            'duration_ms' => $durationMs,
        ]);
    }

    protected function sanitizeRequestPayload(array $payload): ?array
    {
        if (empty($payload)) {
            return null;
        }

        // Limit payload size to prevent database bloat
        $jsonPayload = json_encode($payload);
        if (strlen($jsonPayload) > 65535) { // 64KB limit
            return ['_truncated' => 'Payload too large'];
        }

        return $payload;
    }

    protected function isJson(string $content): bool
    {
        json_decode($content);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function getErrorMessage(Response $response): ?string
    {
        $statusCode = $response->getStatusCode();
        
        // Only capture error messages for 4xx and 5xx responses
        if ($statusCode < 400) {
            return null;
        }

        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            if ($this->isJson($content)) {
                $data = json_decode($content, true);
                return $data['message'] ?? $data['error'] ?? null;
            }
        }

        return null;
    }
}
