<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;

Route::get('/', function () {
    return view('welcome');
});

// ✅ Email verification route (using controller, no auth required)
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed']) // only check if the link is valid, no login needed
    ->name('verification.verify');
