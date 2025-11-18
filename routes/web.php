<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth:sanctum')->group(function () {
    Broadcast::routes();
});


// ✅ Email verification route (using controller, no auth required)
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed']) // only check if the link is valid, no login needed
    ->name('verification.verify');

// ✅ Verification result pages
Route::get('/verify-success', function () {
    return view('verify-success');
})->name('verify.success');

Route::get('/verify-failed', function () {
    return view('verify-failed');
})->name('verify.failed');