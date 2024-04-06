<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    MessageController,
    ContactController,
    EventController,
    GiftController,
    HelpController,
    LogController,
    NotificationController,
    OcassionController,
    PaymentController,
    PolicyController,
    ScreenTextController,
    SocialLoginController,
    TermsController,
    ToneController,
    UserController
};

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
Route::post('/reset-password-phone', [AuthController::class, 'resetPasswordPhone']);
Route::post('/email-phone-check', [AuthController::class, 'emailPhoneCheck']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/user', [UserController::class, 'store']);
Route::post('/verify', [UserController::class, 'verify']);
Route::get('/screen', [ScreenTextController::class, 'allText']);
Route::get('/privacy-policy', [PolicyController::class, 'index']);
Route::get('/term-condition', [TermsController::class, 'index']);
Route::post('/upload-photo', [UserController::class, 'uploadPhoto']);
Route::post('/token-send', [UserController::class, 'tokenSend']);

Route::middleware('auth:api')->group(function () {

    //Auth
    Route::get('/current-user', [AuthController::class, 'currentUser']);
    Route::post('/profile-update', [AuthController::class, 'profileUpdate']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/verify-password', [AuthController::class, 'verifyPassword']);

    //Screen Text
    Route::get('/screen-text', [ScreenTextController::class, 'index']);
    Route::post('/screen-text', [ScreenTextController::class, 'store_or_update']);

    //Privacy Policy
    Route::post('/privacy-policy', [PolicyController::class, 'store_or_update']);

    // Term Condition
    Route::post('/term-condition', [TermsController::class, 'store_or_update']);

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
        'help' => HelpController::class,
    ]);

    //Gift Payment
    Route::post('/gift-payment', [PaymentController::class, 'gift_payment']);

    //Message
    Route::get('/message-read/{id}', [MessageController::class, 'read']);
    Route::get('/all-messages', [MessageController::class, 'all_messages']);
    Route::post('/message-sent', [MessageController::class, 'message_sent']);

    //Notification
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notification-read', [NotificationController::class, 'read']);
    Route::get('/notification-count', [NotificationController::class, 'count']);


});
// SOCIAL lOGIN
Route::post('google/login', [SocialLoginController::class, "googleLogin"]);
Route::post('facebook/login', [SocialLoginController::class, "facebookLogin"]);
Route::post('apple/login', [SocialLoginController::class, "appleLogin"]);
