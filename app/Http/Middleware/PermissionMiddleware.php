<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission = null)
    {
        if (!$permission) {
            return $next($request);
        }

        $user = $request->get('auth_user');

        if (!$user || !$user->hasPermission($permission)) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
