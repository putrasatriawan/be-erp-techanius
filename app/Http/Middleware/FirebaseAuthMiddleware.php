<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use App\Models\User;
use Kreait\Firebase\Auth;

class FirebaseAuthMiddleware
{
    public function __construct(private Auth $auth) {}

    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Firebase token missing'], 401);
        }

        $token = str_replace('Bearer ', '', $header);

        try {
            $verified = $this->auth->verifyIdToken($token);

            $request->merge([
                'firebase_uid' => $verified->claims()->get('sub'),
                'firebase_email' => $verified->claims()->get('email'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid Firebase token'], 401);
        }

        return $next($request);
    }
}
