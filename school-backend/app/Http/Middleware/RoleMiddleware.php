<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles) 
{
    if (!$request->user()) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    // ឆែកមើលថា user មាន role ណាមួយក្នុងចំណោម roles ដែលបានផ្ញើមកឬអត់
    if (!in_array($request->user()->role, $roles)) {
        return response()->json(['message' => 'Unauthorized.'], 403);
    }

    return $next($request);
}
}