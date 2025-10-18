<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VegetableController;
use App\Http\Controllers\ForgotPasswordController;

use App\Http\Controllers\HomepageSliderController;
use App\Http\Controllers\HomepageAboutController;
use App\Http\Controllers\HomepagePrototypeController;
use App\Http\Controllers\HomepageFaqController;
use App\Http\Controllers\HomepageTeamController;
use App\Http\Controllers\HomepageFeedbackController;
use App\Http\Controllers\HomepageFooterController;

use App\Http\Controllers\VerificationController;
use App\Http\Controllers\testingWeather;
use App\Http\Controllers\getReceiverStatus;

// -------------------
// AUTH ROUTES
// -------------------

Route::get('/weather', [testingWeather::class, 'getWeather']);
Route::get('/testingConnection', [getReceiverStatus::class, 'getStatus']);
// Public auth routes
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:3,1');
Route::post('/register', [AuthController::class, 'register']);

// Email verification resend
Route::post('/email/resend', [VerificationController::class, 'resend']);

// ✅ Forgot / Reset password routes
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Get current user (needs token)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Email Verification
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth:sanctum', 'signed'])
    ->name('verification.verify');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum', 'throttle:5|60,1']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('/vegetable', VegetableController::class);
    Route::apiResource('/slider', HomepageSliderController::class);
    Route::apiResource('/homepage-about', HomepageAboutController::class);
    Route::apiResource('/homepage-prototype', HomepagePrototypeController::class);
    Route::apiResource('/homepage-faq', HomepageFaqController::class);
    Route::apiResource('/homepage-team', HomepageTeamController::class);
    Route::apiResource('/homepage-feedback', HomepageFeedbackController::class);
    Route::apiResource('/homepage-footer', HomepageFooterController::class);
});
