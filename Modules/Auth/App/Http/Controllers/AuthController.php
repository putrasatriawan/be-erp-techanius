<?php

namespace Modules\Auth\App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = auth('api')->attempt($data)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        return $this->tokenResponse($token);
    }

    /**
     * Firebase login (opsional): client kirim firebase_uid,
     * lalu server mapping ke user dan issue JWT.
     */
    public function firebaseLogin(Request $request)
    {
        // firebase_uid & email DIAMBIL DARI MIDDLEWARE
        $firebaseUid = $request->firebase_uid;
        $email       = $request->firebase_email;

        $user = User::where('firebase_uid', $firebaseUid)->first();

        if (!$user) {
            $user = User::create([
                'name' => $email ?? 'User',
                'email' => $email ?? ('firebase_' . $firebaseUid . '@local.test'),
                'password' => Hash::make(Str::random(32)),
                'firebase_uid' => $firebaseUid,
            ]);

            // default role dari DB
            $user->assignRole('staff');
        }

        $token = JWTAuth::fromUser($user);

        return $this->tokenResponse($token);
    }


    public function me()
    {
        $user = auth('api')->user();
        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    public function refresh()
    {
        $token = auth('api')->refresh();
        return $this->tokenResponse($token);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['status' => true, 'message' => 'Logged out']);
    }

    private function tokenResponse(string $token)
    {
        return response()->json([
            'status' => true,
            'token_type' => 'bearer',
            'access_token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
