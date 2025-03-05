<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserActivityLog
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (isset($request['record_activity']) && $request['record_activity']) {
            $this->recordActivity($request, $response);
        }
    }

    protected function recordActivity(Request $request, Response $response): void
    {
        $user = Auth::user();
        UserActivity::create([
            'timestamp'     => now(),
            'user_id'       => $user?->id,
            'method'        => $request->method(),
            'status_code'   => $response->getStatusCode(),
            'route_name'    => $request->route()?->getName(),
            'route'         => $request->path(),
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->header('User-Agent'),
            'description'   => $request['activity_description'] ?? '-',
        ]);
    }
}
