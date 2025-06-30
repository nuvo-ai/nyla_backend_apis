<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\Hospital\Registration\HospitalRegistrationController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Middleware\ApiEnsureFrontendRequestsAreStateful;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-email', function () {
    $details = [
        'title' => 'Test Email from Laravel',
        'body' => 'This is a test email to verify SMTP configuration.'
    ];

    Mail::to('topeolotu75@gmail.com')->send(new \App\Mail\TestMail($details));

    return response()->json([
        'message' => 'Email sent successfully!'
    ]);
});


Route::prefix("auth")->as("auth.")->group(function () {
    Route::post("/register", [RegisterController::class, "register"])->name("register");
    Route::post("/oauth-login", [LoginController::class, "oauthLogin"]);
    Route::post("/login", [LoginController::class, "login"])->name("login");
    Route::post("/apple/signin", [LoginController::class, "signInWithApple"])->name("signInWithApple");


    Route::prefix("password")->as("password.")->group(function () {
        Route::post('/forgot', [PasswordController::class, 'forgotPassword'])->name("forgot_password");
        Route::post("/reset", [PasswordController::class, "resetPassword"])->name("reset_password");
    });
    Route::prefix("otp")->as("otp.")->group(function () {
        Route::post('/request', [VerificationController::class, 'request'])->name("request");
        Route::post("/verify", [VerificationController::class, "verify"])->name("verify");
    });
});

// Authenticated routes
Route::middleware([ApiEnsureFrontendRequestsAreStateful::class, "auth:sanctum"])->group(function () {
    Route::get("/me", [UserController::class, "me"])->name("me");
});

Route::prefix('hospital')->as('hospital.')->group(function () {
    Route::post('/register', [HospitalRegistrationController::class, 'registerHospital'])->name('register');
});
