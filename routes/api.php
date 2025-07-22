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

// Public auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Homepage CRUD (public access for now)
Route::apiResource('/homepage-sliders', HomepageSliderController::class);
Route::apiResource('/homepage-abouts', HomepageAboutController::class);
Route::apiResource('/homepage-prototypes', HomepagePrototypeController::class);
Route::apiResource('/homepage-faqs', HomepageFaqController::class);
Route::apiResource('/homepage-teams', HomepageTeamController::class);
Route::apiResource('/homepage-feedbacks', HomepageFeedbackController::class);
Route::apiResource('/homepage-footers', HomepageFooterController::class);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('/vegetable', VegetableController::class);
});
