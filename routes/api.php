<?php

use App\Http\Controllers\Api\Hospital\Appointment\AppointmentController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\Finance\Plan\PlanController;
use App\Http\Controllers\Api\Hospital\Analytic\AnalyticController;
use App\Http\Controllers\Api\Hospital\Doctor\DoctorController;
use App\Http\Controllers\Api\Hospital\Frontdesk\FrontdeskController;
use App\Http\Controllers\Api\Hospital\Home\HomeController;
use App\Http\Controllers\Api\Hospital\HospitalRegistrationController;
use App\Http\Controllers\Api\Hospital\HospitalUsersController;
use App\Http\Controllers\Api\Hospital\Patient\PatientController;
use App\Http\Controllers\Api\Settings\SettingsController;
use App\Http\Controllers\Api\Pharmacy\PharmacyRegistrationController;
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

Route::prefix("password")->as("password.")->group(function () {
    Route::post('/forgot', [PasswordController::class, 'forgotPassword'])->name("forgot_password");
    Route::post("/reset", [PasswordController::class, "resetPassword"])->name("reset_password");
});
Route::prefix("otp")->as("otp.")->group(function () {
    Route::post('/request', [VerificationController::class, 'request'])->name("request");
    Route::post("/verify", [VerificationController::class, "verify"])->name("verify");
});
Route::prefix("auth")->as("auth.")->group(function () {
    Route::post("/register", [RegisterController::class, "register"])->name("register");
    Route::post("/oauth-login", [LoginController::class, "oauthLogin"]);
    Route::post("/login", [LoginController::class, "login"])->name("login");
    Route::post("/apple/signin", [LoginController::class, "signInWithApple"])->name("signInWithApple");
});

// Authenticated routes
Route::middleware([ApiEnsureFrontendRequestsAreStateful::class, "auth:sanctum"])->group(function () {
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/me', [UserController::class, 'me'])->name('me');
        Route::put('/update', [UserController::class, 'update'])->name('update');
        Route::delete('/delete', [UserController::class, 'delete'])->name('delete');
    });

    Route::prefix('hospital')->as('hospital.')->group(function () {

        Route::get('overview/data', [HomeController::class, 'home'])->name('overview.data');


        Route::put('/update/{id}', [HospitalRegistrationController::class, 'updateHospital'])->name('update');
        Route::get('list', [HospitalRegistrationController::class, 'list'])->name('list');
        Route::get('/{uuid}/details', [HospitalRegistrationController::class, 'getHospital'])->name('details');

        Route::prefix('users')->as('users.')->group(function () {
            Route::post('/create', [HospitalUsersController::class, 'create'])->name('create');
            Route::get('/list', [HospitalUsersController::class, 'list'])->name('list');
            Route::delete('/delete/{id}', [HospitalUsersController::class, 'delete'])->name('delete');
            Route::patch('/assign-role/{id}', [HospitalUsersController::class, 'assignRole'])->name('assign-role');
        });
        Route::prefix('appointments')->as('appointments.')->group(function () {
            Route::post('/book', [AppointmentController::class, 'bookAppointment'])->name('book');
            Route::put('/update/{appointment}', [AppointmentController::class, 'updateAppointment'])->name('update');
            Route::patch('/update-status/{id}', [AppointmentController::class, 'updateStatus'])->name('update-status');
            Route::get('/list', [AppointmentController::class, 'listAppointments'])->name('list');
            Route::get('/{id}/details', [AppointmentController::class, 'getAppointment'])->name('details');
            Route::get('/doctor/{doctorId}/appointments', [AppointmentController::class, 'getDoctorAppointments'])->name('doctor.appointments');
            Route::get('/patient/{patientId}/appointments', [AppointmentController::class, 'getPatientAppointments'])->name('patient.appointments');
        });

        Route::resource('patients', PatientController::class);
        Route::resource('doctors', DoctorController::class);
        Route::resource('frontdesks', FrontdeskController::class);

        Route::patch('patient/discharge/{patient}', [PatientController::class, 'discharge'])->name('patient.discharge');
        Route::patch('patient/assign-doctor/{patient}', [PatientController::class, 'assign'])->name('patient.assign-doctor');
        Route::get('patients-stat', [PatientController::class, 'stat'])->name('patients-stat');

        Route::get('analytics', [AnalyticController::class, 'getAnalytics'])->name('analytics');
    });
    Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
        Route::put('/update/{id}', [PharmacyRegistrationController::class, 'updatePharmacy'])->name('update');
        Route::get('list', [PharmacyRegistrationController::class, 'list'])->name('list');
        Route::get('/{uuid}/details', [PharmacyRegistrationController::class, 'getPharmacy'])->name('details');
    });

    Route::prefix('settings')->as('settings.')->group(function () {
        Route::get('/all', [SettingsController::class, 'getSettings'])->name('all');
        Route::patch('/notifications/set-preferences', [SettingsController::class, 'setPreferences'])->name('notifications.set-preferences');
        Route::patch('/system', [SettingsController::class, 'updateSystemSettings'])->name('system.update');
        Route::patch('/preferences', [SettingsController::class, 'updatePreferences'])->name('preferences.update');
        Route::patch('/password/update', [SettingsController::class, 'changePassword'])->name('password.update');
    });

    Route::prefix('billings')->as('billings.')->group(function () {
        Route::post('plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::get('plans/{plan_code}/details', [PlanController::class, 'getPlan'])->name('plans.details');
        Route::put('plans/{plan_code}/update', [PlanController::class, 'update'])->name('plans.update');
        Route::get('plans/list', [PlanController::class, 'list'])->name('plans.list');
        Route::delete('plans/{plan_code}/delete', [PlanController::class, 'delete'])->name('plans.delete');
    });
});

Route::prefix('hospital')->as('hospital.')->group(function () {
    Route::post('/register', [HospitalRegistrationController::class, 'registerHospital'])->name('register');
});
Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
    Route::post('/register', [PharmacyRegistrationController::class, 'registerPharmacy'])->name('register');
});
