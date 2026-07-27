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
        $requestPayload = $request->except([
            'record_activity',
            'activity_description',
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
            'request_payload' => $this->maskingSensitiveData($requestPayload),
            'response' => $responseContent,
            'duration_ms' => $durationMs,
        ]);
    }

    protected function maskingSensitiveData(array $data): array
    {
        $sensitiveKeys = config('sensitive-data.keys', []);
        $maskValue = config('sensitive-data.mask_value', '<information hidden>');
        $caseSensitive = config('sensitive-data.case_sensitive', false);

        return $this->maskRecursive($data, $sensitiveKeys, $maskValue, $caseSensitive);
    }

    /**
     * Recursively mask sensitive data in nested arrays
     */
    protected function maskRecursive(array $data, array $sensitiveKeys, string $maskValue, bool $caseSensitive): array
    {
        foreach ($data as $key => $value) {
            // Check if current key is sensitive
            $isSensitive = $caseSensitive
                ? in_array($key, $sensitiveKeys, true)
                : in_array(strtolower($key), array_map('strtolower', $sensitiveKeys), true);

            if ($isSensitive) {
                $data[$key] = $maskValue;
            } elseif (is_array($value)) {
                // Recursively mask nested arrays
                $data[$key] = $this->maskRecursive($value, $sensitiveKeys, $maskValue, $caseSensitive);
            }
        }

        return $data;
    }

    protected function isJson(string $content): bool
    {
        json_decode($content);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
