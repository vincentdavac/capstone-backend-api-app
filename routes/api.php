<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VegetableController;

use App\Http\Controllers\HomepageSliderController;
use App\Http\Controllers\HomepageAboutController;
use App\Http\Controllers\HomepagePrototypeController;
use App\Http\Controllers\HomepageFaqController;
use App\Http\Controllers\HomepageTeamController;
use App\Http\Controllers\HomepageFeedbackController;
use App\Http\Controllers\HomepageFooterController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//base url for API localhost http://127.0.0.1:8000/api

Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum','throttle:5|60,1']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('/vegetable', VegetableController::class);
    Route::post('/login', [AuthController::class, 'login']);
    Route::apiResource('/slider', HomepageSliderController::class);
    Route::apiResource('/homepage-about', HomepageAboutController::class);
    Route::apiResource('/homepage-prototype', HomepagePrototypeController::class);
    Route::apiResource('/homepage-faq', HomepageFaqController::class);
    Route::apiResource('/homepage-team', HomepageTeamController::class);
    Route::apiResource('/homepage-feedback', HomepageFeedbackController::class);
    Route::apiResource('/homepage-footer', HomepageFooterController::class);
});
