<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, MessageController, ContactController, EventController, GiftController, LogController, OcassionController, PaymentController, ScreenTextController, ToneController, UserController};

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
Route::post('/user', [UserController::class, 'store']);
Route::post('/verify', [UserController::class, 'verify']);
Route::get('/screen', [ScreenTextController::class, 'allText']);

Route::middleware('auth:api')->group(function () {

    //Auth
    Route::get('/current-user', [AuthController::class, 'currentUser']);
    Route::post('/profile-update', [AuthController::class, 'profileUpdate']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/verify-password', [AuthController::class, 'verifyPassword']);

    //Screen Text
    Route::get('/screen-text', [ScreenTextController::class, 'index']);
    Route::post('/screen-text', [ScreenTextController::class, 'store_or_update']);

    //user
    Route::get('/status/{id}', [UserController::class, 'status_change']);

    Route::apiResources([
        'user' => UserController::class,
    ], [
        'except' => ['store']
    ]);

    Route::apiResources([
        'log' => LogController::class,
        'contact' => ContactController::class,
        'event' => EventController::class,
        'ocassion' => OcassionController::class,
        'tone' => ToneController::class,
        'gift' => GiftController::class,
        'message' => MessageController::class,
    ]);

    //Gift Payment
    Route::post('/gift-payment', [PaymentController::class, 'gift_payment']);

    //Message
    Route::get('/message-read/{id}', [MessageController::class, 'read']);
});
