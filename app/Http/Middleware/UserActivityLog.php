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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response) : void
    {
        $user = Auth::user();
        $method = $request->method();
        $statusCode = $response->getStatusCode();
        $routeName = $request->route()->getName();
        $route = $request->path();
        $ipClient = $request->ip();
        $userAgent = $request->header('User-Agent');

        $activityRecord = [
            'timestamp' => now(),
            'user_id' => $user?->id,
            'method' => $method,
            'status_code' => $statusCode,
            'route_name' => $routeName,
            'route' => $route,
            'ip_address' => $ipClient,
            'user_agent' => $userAgent,
        ];
        UserActivity::create($activityRecord);
    }
}
