<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;

use App\Http\Controllers\HomepageSliderController;
use App\Http\Controllers\HomepageAboutController;
use App\Http\Controllers\HomepagePrototypeController;
use App\Http\Controllers\HomepageFaqController;
use App\Http\Controllers\HomepageTeamController;
use App\Http\Controllers\HomepageFeedbackController;
use App\Http\Controllers\HomepageFooterController;
use App\Http\Controllers\BuoyController;
use App\Http\Controllers\GpsReadingController;
use App\Http\Controllers\BatteryHealthController;
use App\Http\Controllers\RelayStatusController;
use App\Http\Controllers\Bme280TemperatureReadingController;
use App\Http\Controllers\Bme280HumidityReadingController;
use App\Http\Controllers\Bme280AtmosphericReadingController;
use App\Http\Controllers\WaterTemperatureReadingController;
use App\Http\Controllers\RainSensorReadingController;
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
use App\Http\Controllers\surroundingtemperatureAlertController;
use App\Http\Controllers\humidityAlertController;
use App\Http\Controllers\atmosphericAlertController;
use App\Http\Controllers\windAlertController;
use App\Http\Controllers\rainAlertController;
use App\Http\Controllers\waterTemperatureAlert;
use App\Http\Controllers\fetchAlerts;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\waterPressureController;
use App\Http\Controllers\PrototypeFileController;
use App\Http\Controllers\insertSensorReadings;
use App\Http\Controllers\getHistoricalData;


Route::post('/add-historical', [insertSensorReadings::class, 'insertSensorData']);
Route::get('/get-sensor-monitoring', [getHistoricalData::class, 'fetchHistorical']);

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


Route::post('/set-alert-surrounding', [surroundingtemperatureAlertController::class, 'setTemperatureAlert']);
Route::post('/set-alert-water-temp', [waterTemperatureAlert::class, 'setWaterTemperatureAlert']);
Route::post('/set-alert-humidity', [humidityAlertController::class, 'setHumidityAlert']);
Route::post('/set-alert-atmospheric', [atmosphericAlertController::class, 'setAtmosphericAlert']);
Route::post('/set-alert-wind', [windAlertController::class, 'setWindAlert']);
Route::post('/set-alert-rain', [rainAlertController::class, 'setRainPercentageAlert']);
Route::post('/set-alert-water-pressure', [waterPressureController::class, 'setWaterPressure']);


Route::get('/get-all-alerts', [fetchAlerts::class, 'getAlerts']);
Route::post('/broadcast-alert', [NotificationController::class, 'broadCastAlerts']);

// USER INFORMATION
Route::middleware('auth:sanctum')->get('/information/user', [AuthController::class, 'me']);

// USER AUTHENTICATION ROUTES
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:3,1');
Route::post('/register', [AuthController::class, 'register']);

// ADMIN AUTHENTICATION ROUTES
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);
Route::post('/admin/register', [AuthController::class, 'registerAdmin']);

Route::post('/email/resend', [VerificationController::class, 'resend']);

// ✅ Forgot / Reset password routes
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Email Verification
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth:sanctum', 'signed'])
    ->name('verification.verify');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum', 'throttle:5|60,1']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Barangay Routes
    Route::get('/barangays', [BarangayController::class, 'index']);
    Route::post('/barangays', [BarangayController::class, 'store']);
    Route::get('/barangays/{barangay}', [BarangayController::class, 'show']);
    Route::patch('/barangays/{barangay}', [BarangayController::class, 'update']);
    Route::delete('/barangays/{barangay}', [BarangayController::class, 'destroy']);

    // Buoy Routes
    Route::get('/buoys', [BuoyController::class, 'index']);
    Route::post('/buoys', [BuoyController::class, 'store']);
    Route::get('/buoys/{buoy}', [BuoyController::class, 'show']);
    Route::patch('/buoys/{buoy}', [BuoyController::class, 'update']);
    Route::delete('/buoys/{buoy}', [BuoyController::class, 'destroy']);


    // GPS Reading Routes
    Route::get('/gps-readings', [GpsReadingController::class, 'index']);
    Route::post('/gps-readings', [GpsReadingController::class, 'store']);
    Route::get('/gps-readings/{gpsReading}', [GpsReadingController::class, 'show']);
    Route::patch('/gps-readings/{gpsReading}', [GpsReadingController::class, 'update']);
    Route::delete('/gps-readings/{gpsReading}', [GpsReadingController::class, 'destroy']);

    // Voltage Reading Routes
    Route::get('/voltage-readings', [BatteryHealthController::class, 'index']);
    Route::post('/voltage-readings', [BatteryHealthController::class, 'store']);
    Route::get('/voltage-readings/{voltageReading}', [BatteryHealthController::class, 'show']);
    Route::patch('/voltage-readings/{voltageReading}', [BatteryHealthController::class, 'update']);
    Route::delete('/voltage-readings/{voltageReading}', [BatteryHealthController::class, 'destroy']);

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

    // Rain Reading Routes
    Route::get('/rain-readings', [RainSensorReadingController::class, 'index']);
    Route::post('/rain-readings', [RainSensorReadingController::class, 'store']);
    Route::get('/rain-readings/{rainReading}', [RainSensorReadingController::class, 'show']);
    Route::patch('/rain-readings/{rainReading}', [RainSensorReadingController::class, 'update']);
    Route::delete('/rain-readings/{rainReading}', [RainSensorReadingController::class, 'destroy']);

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


    // HOMEPAGE CONTENT MANAGEMENT ROUTES

    // Slider Routes
    Route::get('/sliders', [HomepageSliderController::class, 'index']);
    Route::post('/sliders', [HomepageSliderController::class, 'store']);
    Route::get('/active-sliders', [HomepageSliderController::class, 'activeSliders']);
    Route::get('/archived-sliders', [HomepageSliderController::class, 'archivedSliders']);
    Route::get('/sliders/{slider}', [HomepageSliderController::class, 'show']);
    Route::patch('/sliders/{slider}', [HomepageSliderController::class, 'update']);
    Route::delete('/sliders/{slider}', [HomepageSliderController::class, 'destroy']);


    // About Routes
    Route::get('/abouts', [HomepageAboutController::class, 'index']);
    Route::post('/abouts', [HomepageAboutController::class, 'store']);
    Route::get('/active-abouts', [HomepageAboutController::class, 'activeAbouts']);
    Route::get('/archived-abouts', [HomepageAboutController::class, 'archivedAbouts']);
    Route::get('/abouts/{about}', [HomepageAboutController::class, 'show']);
    Route::patch('/abouts/{about}', [HomepageAboutController::class, 'update']);
    Route::delete('/abouts/{about}', [HomepageAboutController::class, 'destroy']);

    Route::post('about-cards', [HomepageAboutController::class, 'storeCard']);
    Route::patch('about-cards/{card}', [HomepageAboutController::class, 'updateCard']);
    Route::delete('about-cards/{card}', [HomepageAboutController::class, 'destroyCard']);


    // Prototype Routes
    Route::get('/prototypes', [HomepagePrototypeController::class, 'index']);
    Route::post('/prototypes', [HomepagePrototypeController::class, 'store']);
    Route::get('/active-prototypes', [HomepagePrototypeController::class, 'activePrototypes']);
    Route::get('/archived-prototypes', [HomepagePrototypeController::class, 'archivedPrototypes']);
    Route::get('/prototypes/{prototype}', [HomepagePrototypeController::class, 'show']);
    Route::patch('/prototypes/{prototype}', [HomepagePrototypeController::class, 'update']);
    Route::delete('/prototypes/{prototype}', [HomepagePrototypeController::class, 'destroy']);

    // Prototype-file Routes - Not working since the 3D file is too large
    Route::get('/prototype-file', [PrototypeFileController::class, 'index']);
    Route::post('/prototype-file', [PrototypeFileController::class, 'store']);
    Route::get('/active-prototype-file', [PrototypeFileController::class, 'activePrototypeFile']);
    Route::get('/archived-prototype-file', [PrototypeFileController::class, 'archivedPrototypeFile']);
    Route::get('/prototype-file/{prototype}', [PrototypeFileController::class, 'show']);
    Route::patch('/prototype-file/{prototype}', [PrototypeFileController::class, 'update']);
    Route::delete('/prototype-file/{prototype}', [PrototypeFileController::class, 'destroy']);

    // Team Routes
    Route::get('/teams', [HomepageTeamController::class, 'index']);
    Route::get('/active-teams', [HomepageTeamController::class, 'activeTeams']);
    Route::get('/archived-teams', [HomepageTeamController::class, 'archivedTeams']);
    Route::post('/teams', [HomepageTeamController::class, 'store']);
    Route::get('/teams/{team}', [HomepageTeamController::class, 'show']);
    Route::patch('/teams/{team}', [HomepageTeamController::class, 'update']);
    Route::delete('/teams/{team}', [HomepageTeamController::class, 'destroy']);

    // FAQS Routes
    Route::get('/faqs', [HomepageFaqController::class, 'index']);
    Route::get('/active-faqs', [HomepageFaqController::class, 'activeFaqs']);
    Route::get('/archived-faqs', [HomepageFaqController::class, 'archivedFaqs']);
    Route::post('/faqs', [HomepageFaqController::class, 'store']);
    Route::get('/faqs/{faq}', [HomepageFaqController::class, 'show']);
    Route::patch('/faqs/{faq}', [HomepageFaqController::class, 'update']);
    Route::delete('/faqs/{faq}', [HomepageFaqController::class, 'destroy']);

    Route::apiResource('/homepage-feedback', HomepageFeedbackController::class);
    Route::apiResource('/homepage-footer', HomepageFooterController::class);
});
