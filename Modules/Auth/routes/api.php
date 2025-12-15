<?php

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controllers\AuthController;



Route::get('/firebase-debug', function () {
    $auth = Firebase::auth();

    return response()->json([
        'status' => 'ok',
        'project' => config('firebase.default'),
        'credential' => config('firebase.projects.app.credentials'),
    ]);
});
Route::post('/auth/firebase-login', [AuthController::class, 'firebaseLogin']);
