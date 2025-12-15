<?php

use Illuminate\Http\Request;
use Kreait\Firebase\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controllers\AuthController;



Route::get('/firebase-debug', function () {
    $auth = app(Auth::class);

    return [
        'status' => 'ok',
        'project_id' => $auth->getApiClient()->getProjectId(),
    ];
});
Route::post('/auth/firebase-login', [AuthController::class, 'firebaseLogin'])
    ->middleware('firebase.auth');
