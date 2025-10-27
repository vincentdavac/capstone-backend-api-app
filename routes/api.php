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
use App\Http\Controllers\BuoyStatusController;
use App\Http\Controllers\BuoyController; // ✅ Add this line
use App\Http\Controllers\GpsReadingController;
use App\Http\Controllers\VoltageReadingController;
use App\Http\Controllers\RelayStatusController;
use App\Http\Controllers\Bme280TemperatureReadingController;
use App\Http\Controllers\Bme280HumidityReadingController;
use App\Http\Controllers\Bme280AtmosphericReadingController;
use App\Http\Controllers\WaterTemperatureReadingController;
use App\Http\Controllers\Mpu6050ReadingController;
use App\Http\Controllers\RainReadingController;
use App\Http\Controllers\DepthReadingController;
use App\Http\Controllers\WindReadingController;
use App\Http\Controllers\RainGaugeReadingController;
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

    // Buoy Routes
Route::get('/buoys', [BuoyController::class, 'index']);
Route::post('/buoys', [BuoyController::class, 'store']);
Route::get('/buoys/{buoy}', [BuoyController::class, 'show']);
Route::patch('/buoys/{buoy}', [BuoyController::class, 'update']);
Route::delete('/buoys/{buoy}', [BuoyController::class, 'destroy']);

    // Buoy Status Routes
Route::get('/buoy-status', [BuoyStatusController::class, 'index']);
Route::post('/buoy-status', [BuoyStatusController::class, 'store']);
Route::get('/buoy-status/{buoyStatus}', [BuoyStatusController::class, 'show']);
Route::patch('/buoy-status/{buoyStatus}', [BuoyStatusController::class, 'update']);
Route::delete('/buoy-status/{buoyStatus}', [BuoyStatusController::class, 'destroy']);

    // GPS Reading Routes
Route::get('/gps-readings', [GpsReadingController::class, 'index']);
Route::post('/gps-readings', [GpsReadingController::class, 'store']);
Route::get('/gps-readings/{gpsReading}', [GpsReadingController::class, 'show']);
Route::patch('/gps-readings/{gpsReading}', [GpsReadingController::class, 'update']);
Route::delete('/gps-readings/{gpsReading}', [GpsReadingController::class, 'destroy']);

// Voltage Reading Routes
Route::get('/voltage-readings', [VoltageReadingController::class, 'index']);
Route::post('/voltage-readings', [VoltageReadingController::class, 'store']);
Route::get('/voltage-readings/{voltageReading}', [VoltageReadingController::class, 'show']);
Route::patch('/voltage-readings/{voltageReading}', [VoltageReadingController::class, 'update']);
Route::delete('/voltage-readings/{voltageReading}', [VoltageReadingController::class, 'destroy']);

// Relay Status Routes
Route::get('/relay-status', [RelayStatusController::class, 'index']);
Route::post('/relay-status', [RelayStatusController::class, 'store']);
Route::get('/relay-status/{relayStatus}', [RelayStatusController::class, 'show']);
Route::patch('/relay-status/{relayStatus}', [RelayStatusController::class, 'update']);
Route::delete('/relay-status/{relayStatus}', [RelayStatusController::class, 'destroy']);

// Temperature Readings
Route::get('/bme280-temperature-readings', [Bme280TemperatureReadingController::class, 'index']);
Route::post('/bme280-temperature-readings', [Bme280TemperatureReadingController::class, 'store']);
Route::get('/bme280-temperature-readings/{bme280TemperatureReading}', [Bme280TemperatureReadingController::class, 'show']);
Route::patch('/bme280-temperature-readings/{bme280TemperatureReading}', [Bme280TemperatureReadingController::class, 'update']);
Route::delete('/bme280-temperature-readings/{bme280TemperatureReading}', [Bme280TemperatureReadingController::class, 'destroy']);

// Humidity Readings
Route::get('/bme280-humidity-readings', [Bme280HumidityReadingController::class, 'index']);
Route::post('/bme280-humidity-readings', [Bme280HumidityReadingController::class, 'store']);
Route::get('/bme280-humidity-readings/{bme280HumidityReading}', [Bme280HumidityReadingController::class, 'show']);
Route::patch('/bme280-humidity-readings/{bme280HumidityReading}', [Bme280HumidityReadingController::class, 'update']);
Route::delete('/bme280-humidity-readings/{bme280HumidityReading}', [Bme280HumidityReadingController::class, 'destroy']);

// atmospheric-readings
Route::get('/bme280-atmospheric-readings', [Bme280AtmosphericReadingController::class, 'index']);
Route::post('/bme280-atmospheric-readings', [Bme280AtmosphericReadingController::class, 'store']);
Route::get('/bme280-atmospheric-readings/{bme280AtmosphericReading}', [Bme280AtmosphericReadingController::class, 'show']);
Route::patch('/bme280-atmospheric-readings/{bme280AtmosphericReading}', [Bme280AtmosphericReadingController::class, 'update']);
Route::delete('/bme280-atmospheric-readings/{bme280AtmosphericReading}', [Bme280AtmosphericReadingController::class, 'destroy']);

// temperature-readings
Route::get('/water-temperature-readings', [WaterTemperatureReadingController::class, 'index']);
Route::post('/water-temperature-readings', [WaterTemperatureReadingController::class, 'store']);
Route::get('/water-temperature-readings/{waterTemperatureReading}', [WaterTemperatureReadingController::class, 'show']);
Route::patch('/water-temperature-readings/{waterTemperatureReading}', [WaterTemperatureReadingController::class, 'update']);
Route::delete('/water-temperature-readings/{waterTemperatureReading}', [WaterTemperatureReadingController::class, 'destroy']);

// mpu6050-readings
Route::get('/mpu6050-readings', [Mpu6050ReadingController::class, 'index']);
Route::post('/mpu6050-readings', [Mpu6050ReadingController::class, 'store']);
Route::get('/mpu6050-readings/{mpu6050Reading}', [Mpu6050ReadingController::class, 'show']);
Route::patch('/mpu6050-readings/{mpu6050Reading}', [Mpu6050ReadingController::class, 'update']);
Route::delete('/mpu6050-readings/{mpu6050Reading}', [Mpu6050ReadingController::class, 'destroy']);

// Rain Reading Routes
Route::get('/rain-readings', [RainReadingController::class, 'index']);
Route::post('/rain-readings', [RainReadingController::class, 'store']);
Route::get('/rain-readings/{rainReading}', [RainReadingController::class, 'show']);
Route::patch('/rain-readings/{rainReading}', [RainReadingController::class, 'update']);
Route::delete('/rain-readings/{rainReading}', [RainReadingController::class, 'destroy']);

// Depth Reading Routes
Route::get('/depth-readings', [DepthReadingController::class, 'index']);
Route::post('/depth-readings', [DepthReadingController::class, 'store']);
Route::get('/depth-readings/{depthReading}', [DepthReadingController::class, 'show']);
Route::patch('/depth-readings/{depthReading}', [DepthReadingController::class, 'update']);
Route::delete('/depth-readings/{depthReading}', [DepthReadingController::class, 'destroy']);

// Wind Reading Routes
Route::get('/wind-readings', [WindReadingController::class, 'index']);
Route::post('/wind-readings', [WindReadingController::class, 'store']);
Route::get('/wind-readings/{windReading}', [WindReadingController::class, 'show']);
Route::patch('/wind-readings/{windReading}', [WindReadingController::class, 'update']);
Route::delete('/wind-readings/{windReading}', [WindReadingController::class, 'destroy']);


Route::get('/rain-gauge-readings', [RainGaugeReadingController::class, 'index']);
Route::post('/rain-gauge-readings', [RainGaugeReadingController::class, 'store']);
Route::get('/rain-gauge-readings/{rainGaugeReading}', [RainGaugeReadingController::class, 'show']);
Route::patch('/rain-gauge-readings/{rainGaugeReading}', [RainGaugeReadingController::class, 'update']);
Route::delete('/rain-gauge-readings/{rainGaugeReading}', [RainGaugeReadingController::class, 'destroy']);

    Route::resource('/vegetable', VegetableController::class);
    Route::apiResource('/slider', HomepageSliderController::class);
    Route::apiResource('/homepage-about', HomepageAboutController::class);
    Route::apiResource('/homepage-prototype', HomepagePrototypeController::class);
    Route::apiResource('/homepage-faq', HomepageFaqController::class);
    Route::apiResource('/homepage-team', HomepageTeamController::class);
    Route::apiResource('/homepage-feedback', HomepageFeedbackController::class);
    Route::apiResource('/homepage-footer', HomepageFooterController::class);
});
