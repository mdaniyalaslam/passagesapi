<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, UserController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/user', [UserController::class, 'store']);
Route::post('/verify', [UserController::class, 'verify']);

Route::middleware('auth:api')->group(function () {
    Route::get('/current-user', [AuthController::class, 'currentUser']);
    Route::post('/profile-update', [AuthController::class, 'profileUpdate']);

    Route::apiResources([
        'user' => UserController::class,
    ], [
        'except' => ['store']
    ]);
});
