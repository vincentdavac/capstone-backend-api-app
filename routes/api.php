<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Models\Message;
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
use App\Http\Controllers\RainSensorReadingController;
use App\Http\Controllers\WindReadingController;
use App\Http\Controllers\RainGaugeReadingController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\countUser;
use App\Http\Controllers\windController;
use App\Http\Controllers\waterController;
use App\Http\Controllers\batteryController;
use App\Http\Controllers\surroundingController;
use App\Http\Controllers\gpsController;
use App\Http\Controllers\raingaugeController;
use App\Http\Controllers\rainSensorController;
use App\Http\Controllers\fetchAlerts;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\PrototypeFileController;
use App\Http\Controllers\getHistoricalData;
use App\Http\Controllers\getAtmostphericdata;
use App\Http\Controllers\getsurroundingdata;
use App\Http\Controllers\gethumiditydata;
use App\Http\Controllers\getwaterdata;
use App\Http\Controllers\getwaterTemp;
use App\Http\Controllers\getWind;
use App\Http\Controllers\getRaingauge;
use App\Http\Controllers\getRainSensor;
use App\Http\Controllers\alertController;
use App\Http\Controllers\currentCondition;
use App\Http\Controllers\DeployedBuoyController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\broadCastController;
use App\Http\Controllers\alertMonitoring;
use App\Http\Controllers\alertNotif;
use App\Http\Controllers\currentConditionv2;
use App\Http\Controllers\HotlinesController;
use App\Http\Controllers\SystemNotificationsController;
use App\Http\Controllers\updateProfile;
use App\Http\Controllers\updateProfilePic;
use App\Http\Controllers\fetchUserInfo;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BarangayDashboardController;
use App\Http\Controllers\getNotifCount;
use App\Http\Controllers\BuoyMonitoringController;
use App\Http\Controllers\BME280DataController;
use App\Http\Controllers\MS5837DataController;


//  Route::get('/user/hotlines', [HotlinesController::class, 'userHotlines']);
Route::middleware('auth:sanctum')->post('/update-img', [updateProfilePic::class, 'updateProfileImage']);
Route::middleware('auth:sanctum')->post('/update-information', [updateProfile::class, 'updateProfile']);
Route::middleware('auth:sanctum')->get('/get-information', [fetchUserInfo::class, 'getUserInfo']);

Route::get('/get-current-conditionV2', [currentConditionv2::class, 'getCurrentCondition']);


// Route::get('/alert-notif', [alertNotif::class, 'getAlertNotif']);
// Route::middleware('auth:sanctum')->get('/alert-count', [alertNotif::class, 'getCount']);
Route::middleware('auth:sanctum')->get('/alert-notif', [alertNotif::class, 'getAlertNotif']);
Route::middleware('auth:sanctum')->post('/all-set-alerts', [alertController::class, 'allAlerts']);
Route::middleware('auth:sanctum')->post('/alert-read', [alertNotif::class, 'isShown']);
Route::post('/mark-shown', [alertMonitoring::class, 'markAlertAsShown']);
// Route::get('/{buoyCode}/status', [alertMonitoring::class, 'checkAlertStatus']);
Route::middleware('auth:sanctum')->post('/broadcast-monitoring', [alertMonitoring::class, 'sendAlert']);
Route::middleware('auth:sanctum')->post('/reset-relay-modal', [alertMonitoring::class, 'resetRelayModal']);
Route::middleware('auth:sanctum')->post('/broadcast-alert', [broadCastController::class, 'sendAlert']);
Route::middleware('auth:sanctum')->post('/reset-relay', [broadCastController::class, 'resetRelay']);

Route::get('/get-current-condition', [currentCondition::class, 'getCurrentCondition']);
Route::get('/get-rain-sensor', [getRainSensor::class, 'getrainsensorChart']);
Route::get('/get-raingauge', [getRaingauge::class, 'getraingaugeChart']);
Route::get('/get-wind', [getWind::class, 'getwindChart']);
Route::get('/get-water-temp', [getwaterTemp::class, 'getwatertempChart']);

Route::get('/get-water', [getwaterdata::class, 'getwaterChart']);
Route::get('/get-humidity', [gethumiditydata::class, 'gethumidityChart']);
Route::get('/get-surrounding', [getsurroundingdata::class, 'getsurroundingChart']);
Route::get('/get-atmospheric', [getAtmostphericdata::class, 'getAtmosphericChart']);
Route::get('/get-sensor-monitoring', [getHistoricalData::class, 'fetchHistorical']);


// Route::get('/testingConnection', [getReceiverStatus::class, 'getStatus']); test lang to
Route::get('/countUsers', [countUser::class, 'countUsers']);
Route::get('/wind', [windController::class, 'getWindData']);
Route::get('/water-temperature', [waterController::class, 'getWaterTemp']);
Route::get('/surrounding-temperature', [surroundingController::class, 'getSurroundingData']);
Route::get('/battery', [batteryController::class, 'getBatteryData']);
Route::get('/gps-data', [gpsController::class, 'getGpsData']);
Route::get('/rain-gauge', [raingaugeController::class, 'getRainGauge']);
Route::get('/rain-sensor', [rainSensorController::class, 'getRainSensor']);

Route::get('/get-all-alerts', [fetchAlerts::class, 'getAlerts']);
// USER INFORMATION
Route::middleware('auth:sanctum')->get('/information/user', [AuthController::class, 'me']);

// USER AUTHENTICATION ROUTES
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:3,1');
Route::post('/register', [AuthController::class, 'register']);

// ADMIN AUTHENTICATION ROUTES
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);
Route::post('/admin/register', [AuthController::class, 'registerAdmin']);

// BARANGAY AUTHENTICATION ROUTES
Route::post('/barangay/login', [AuthController::class, 'loginBarangay']);
Route::post('/barangay/register', [AuthController::class, 'registerBarangay']);


Route::post('/email/resend', [VerificationController::class, 'resend']);

// Forgot / Reset password routes
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Email Verification
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth:sanctum', 'signed'])
    ->name('verification.verify');

// Don't Include in the Protected routes -> It was used in the Register route
Route::get('/barangays', [BarangayController::class, 'index']);


// HOMEPAGE CONTENT
Route::get('/public-sliders', [HomepageSliderController::class, 'publicSliders']);
Route::get('/public-abouts', [HomepageAboutController::class, 'publicAbouts']);
Route::get('/public-about-cards-active', [HomepageAboutController::class, 'publicGetActiveCards']);
Route::get('/public-prototypes/left', [HomepagePrototypeController::class, 'publicFetchLeftPrototypes']);
Route::get('/public-prototypes/right', [HomepagePrototypeController::class, 'publicFetchRightPrototypes']);
Route::get('/public-active-teams', [HomepageTeamController::class, 'publicActiveTeams']);
Route::get('/public-active-faqs', [HomepageFaqController::class, 'publicActiveFaqs']);
Route::get('/public-active-feedbacks', [HomepageFeedbackController::class, 'publicActiveFeedbacks']);
Route::get('/public-footers', [HomepageFooterController::class, 'publicFooters']);


// Protected routes
Route::group(['middleware' => ['auth:sanctum', 'throttle:5|60,1']], function () {
    Route::get('/{buoyId}/status', [alertMonitoring::class, 'checkAlertStatus']);
    Route::get('/{buoyCode}/active', [alertMonitoring::class, 'getActiveAlerts']);
    Route::get('/get-all-count', [getNotifCount::class, 'allCount']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/update-user/{user}', [AuthController::class, 'updateUser']);

    Route::get('/active-users', [AuthController::class, 'activeUsers']);
    Route::get('/archived-users', [AuthController::class, 'archivedUsers']);

    Route::patch('/barangay/archived-user/{id}', [AuthController::class, 'archiveUser']);
    Route::patch('/barangay/restore-user/{id}', [AuthController::class, 'restoreUser']);

    Route::patch('/admin/archived-barangay/{id}', [AuthController::class, 'archiveBarangay']);
    Route::patch('/admin/restore-barangay/{id}', [AuthController::class, 'restoreBarangay']);

    // Barangay Routes
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

    // Rain Reading Routes
    Route::get('/rain-readings', [RainSensorReadingController::class, 'index']);
    Route::get('/rain-readings/{rainReading}', [RainSensorReadingController::class, 'show']);
    Route::patch('/rain-readings/{rainReading}', [RainSensorReadingController::class, 'update']);
    Route::delete('/rain-readings/{rainReading}', [RainSensorReadingController::class, 'destroy']);


    // Wind Reading Routes
    Route::get('/wind-readings', [WindReadingController::class, 'index']);

    // Rain Gauge Reading Routes
    Route::get('/rain-gauge-readings', [RainGaugeReadingController::class, 'index']);



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

    Route::get('about-cards', [HomepageAboutController::class, 'getAllCards']);
    Route::get('about-cards-active', [HomepageAboutController::class, 'getActiveCards']);
    Route::post('about-cards', [HomepageAboutController::class, 'storeCard']);
    Route::patch('about-cards/{card}', [HomepageAboutController::class, 'updateCard']);
    Route::delete('about-cards/{card}', [HomepageAboutController::class, 'destroyCard']);


    // Prototype Routes
    Route::get('/prototypes', [HomepagePrototypeController::class, 'index']);
    Route::get('/prototypes/left', [HomepagePrototypeController::class, 'fetchLeftPrototypes']);
    Route::get('/prototypes/right', [HomepagePrototypeController::class, 'fetchRightPrototypes']);
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

    // Feedbacks Routes
    Route::get('/feedbacks', [HomepageFeedbackController::class, 'index']);
    Route::get('/active-feedbacks', [HomepageFeedbackController::class, 'activeFeedbacks']);
    Route::get('/archived-feedbacks', [HomepageFeedbackController::class, 'archivedFeedbacks']);
    Route::post('/feedbacks', [HomepageFeedbackController::class, 'store']);
    Route::get('/feedbacks/{feedback}', [HomepageFeedbackController::class, 'show']);
    Route::patch('/feedbacks/{feedback}', [HomepageFeedbackController::class, 'update']);
    Route::delete('/feedbacks/{feedback}', [HomepageFeedbackController::class, 'destroy']);
    Route::post('/submit-feedback', [HomepageFeedbackController::class, 'submitFeedback']);

    // Footers Routes
    Route::get('/footers', [HomepageFooterController::class, 'index']);
    Route::post('/footers', [HomepageFooterController::class, 'store']);
    Route::patch('/footers/{footer}', [HomepageFooterController::class, 'update']);
    Route::delete('/footers/{footer}', [HomepageFooterController::class, 'destroy']);

    // Deployed Buoy Routes
    Route::get('/deployed-buoy/{buoyCode}', [DeployedBuoyController::class, 'show']);
    Route::get('/get-all-data/{buoyCode}', [DeployedBuoyController::class, 'getAllData']);

    // Hotlines
    Route::get('/hotlines/archived', [HotlinesController::class, 'archived']);
    Route::get('/hotlines', [HotlinesController::class, 'index']);
    Route::post('/hotlines', [HotlinesController::class, 'store']);
    Route::get('/hotlines/{hotline}', [HotlinesController::class, 'show']);
    Route::patch('/hotlines/{hotline}', [HotlinesController::class, 'update']);
    Route::patch('/hotlines/archive/{hotline}', [HotlinesController::class, 'archive']);
    Route::patch('/hotlines/restore/{hotline}', [HotlinesController::class, 'restoreArchive']);


    // User-specific hotlines
    Route::get('/user/hotlines', [HotlinesController::class, 'userHotlines']);

    // System Notifications Routes for Admin
    Route::get('unread/admin', [SystemNotificationsController::class, 'unreadNotificationsAdmin']);
    Route::patch('read/admin/{id}', [SystemNotificationsController::class, 'markAsReadAdmin']);
    Route::patch('read-all/admin', [SystemNotificationsController::class, 'markAllAsReadAdmin']);

    // System Notifications Routes for Barangay
    Route::get('unread/barangay', [SystemNotificationsController::class, 'unreadByRole']);
    Route::patch('read/barangay/{id}', [SystemNotificationsController::class, 'markAsRead']);
    Route::patch('read-all/barangay', [SystemNotificationsController::class, 'markAllAsRead']);

    // System Notifications Routes for User
    Route::get('unread/user', [SystemNotificationsController::class, 'unreadByRole']);
    Route::patch('read/user/{id}', [SystemNotificationsController::class, 'markAsRead']);
    Route::patch('read-all/user', [SystemNotificationsController::class, 'markAllAsRead']);
    Route::get('unread-user', [SystemNotificationsController::class, 'notifUser']);
    // Admin Dashboard Routes
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'dashboardStats']);

    // Barangay Dashboard – User Statistics
    Route::get('/barangay/dashboard', [BarangayDashboardController::class, 'dashboardStats']);

    // Buoy Monitoring Routes
    Route::get('/deployment-point/{buoy}', [BuoyMonitoringController::class, 'show']);

    // Relay Switch Route
    Route::post('/relay/switch', [RelayStatusController::class, 'relaySwitch']);

    // GPS Report Route
    Route::get('/gps-report', [GpsReadingController::class, 'generateReport']);
    Route::get('/gps-readings', [GpsReadingController::class, 'fetchAllReadings']);
});


// ESP32 - No Auth, No Throttle
Route::post('/gps/store', [GpsReadingController::class, 'store'])->withoutMiddleware(['auth:sanctum', 'throttle:api',]);
Route::post('/battery-health/store', [BatteryHealthController::class, 'store'])->withoutMiddleware(['auth:sanctum', 'throttle:api',]);
Route::post('/bme280-data', [BME280DataController::class, 'store'])->withoutMiddleware(['auth:sanctum', 'throttle:api',]);
Route::post('/rain-readings', [RainSensorReadingController::class, 'store'])->withoutMiddleware(['auth:sanctum', 'throttle:api',]);
Route::post('/ms5837-data', [MS5837DataController::class, 'store'])->withoutMiddleware(['auth:sanctum', 'throttle:api',]);
Route::post('/wind-readings', [WindReadingController::class, 'store'])->withoutMiddleware(['auth:sanctum', 'throttle:api',]);
Route::post('/rain-gauge-readings', [RainGaugeReadingController::class, 'store']);



// CHAT ROUTES: No throttle
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/messages/send', [MessageController::class, 'send']);
    Route::get('/chat/{chatId}', [MessageController::class, 'getChat']);
    Route::patch('/chat/{id}/read', [MessageController::class, 'markChatAsRead']);
    Route::get('/admin/chats/barangays', [MessageController::class, 'getAllBarangayChats']);
    Route::get('/barangay/chats/users-admins', [MessageController::class, 'getAllUserAndAdminChats']);

    // User Side: Send message to Barangay
    Route::post('/user/message/send', [MessageController::class, 'sendMessageUserToBarangay']);
    // User Side: Get all Barangay Chats
    Route::get('/user/chats/barangays', [MessageController::class, 'getChatUserToBarangay']);
    // Count unread chats
    Route::get('/chats/unread/count', [MessageController::class, 'countUnreadChats']);
});
