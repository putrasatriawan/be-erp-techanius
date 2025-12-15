<?php

namespace App\Http\Middleware;

use Closure;

use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['message' => 'Firebase token missing'], 401);
        }

        try {
            $verifiedToken = Firebase::auth()->verifyIdToken($idToken);
            $request->firebase_uid = $verifiedToken->claims()->get('sub');
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid Firebase token'], 401);
        }

        return $next($request);
    }
}
