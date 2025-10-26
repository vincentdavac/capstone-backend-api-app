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
use App\Http\Controllers\countUser;
use App\Http\Controllers\windController;
use App\Http\Controllers\waterController;
use App\Http\Controllers\batteryController;
use App\Http\Controllers\surroundingController;
use App\Http\Controllers\gpsController;
use App\Http\Controllers\raingaugeController;
use App\Http\Controllers\rainSensorController;

// -------------------
// AUTH ROUTES
// -------------------

Route::get('/weather', [testingWeather::class, 'getWeather']);
Route::get('/weatherHourly', [testingWeather::class, 'getHourlyTemperature']);
// Route::get('/testingConnection', [getReceiverStatus::class, 'getStatus']); test lang to
Route::get('/countUsers', [countUser::class, 'countUsers']);
Route::get('/wind', [windController::class, 'getWindData']);
Route::get('/water-temperature', [waterController::class, 'getWaterTemp']);
Route::get('/surrounding-temperature', [surroundingController::class, 'getSurroundingData']);
Route::get('/battery', [batteryController::class, 'getBatteryData']);
Route::get('/gps-data', [gpsController::class, 'getGpsData']);
Route::get('/rain-gauge', [raingaugeController::class, 'getRainGauge']);
Route::get('/rain-sensor', [rainSensorController::class, 'getRainSensor']);

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
