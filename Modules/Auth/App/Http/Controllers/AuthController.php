<?php

namespace Modules\Auth\App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function firebaseLogin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name'  => 'required|string',
        ]);

        $auth = Firebase::auth();

        // ===============================
        // 1️⃣ CEK / CREATE USER FIREBASE
        // ===============================
        try {
            $firebaseUser = $auth->getUserByEmail($data['email']);
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {

            $firebaseUser = $auth->createUser([
                'email' => $data['email'],
                'password' => str()->random(16),
                'displayName' => $data['name'],
                'emailVerified' => false,
            ]);
        }

        $firebaseUid = $firebaseUser->uid;

        // ===============================
        // 2️⃣ CEK / CREATE USER DB
        // ===============================
        $user = User::where('firebase_uid', $firebaseUid)->first();

        if (!$user) {
            $user = User::create([
                'name'         => $data['name'],
                'email'        => $data['email'],
                'password'     => Hash::make(str()->random(32)),
                'firebase_uid' => $firebaseUid,
            ]);

            // role default (Spatie)
            $user->assignRole('staff');
        }

        // ===============================
        // 3️⃣ JWT TOKEN
        // ===============================
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'type'  => 'bearer',
            'user'  => [
                'id' => $user->id,
                'firebase_uid' => $firebaseUid,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
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
