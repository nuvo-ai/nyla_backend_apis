<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\AI\DoctorAIAssistanceController;
use App\Http\Controllers\Api\AI\PatientAIAssistanceController;
use App\Http\Controllers\Api\AI\PharmacyAIAssistanceController;
use App\Http\Controllers\Api\Hospital\Appointment\AppointmentController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\VerificationController;
// use App\Http\Controllers\Api\Finance\Plan\PlanController;
use App\Http\Controllers\Api\Settings\SettingsController;
use App\Http\Controllers\Api\Hospital\Home\HomeController;
use App\Http\Controllers\Api\Billing\Plan\PlanController;
use App\Http\Controllers\Api\Billing\Plan\PlanFeatureController;
use App\Http\Controllers\Api\Billing\Subscription\SubscriptionController;
use App\Http\Controllers\Api\Billing\Webhook\WebhookController;
use App\Http\Controllers\Api\Hospital\Analytic\AnalyticController;
use App\Http\Controllers\Api\Hospital\Doctor\DoctorController;
use App\Http\Controllers\Api\Hospital\Frontdesk\FrontdeskController;
use App\Http\Controllers\Api\Hospital\HospitalRegistrationController;
use App\Http\Controllers\Api\Hospital\HospitalUsersController;
use App\Http\Controllers\Api\Hospital\Patient\PatientController;
use App\Http\Controllers\Api\Hospital\Tracker\PeriodCycleController;
use App\Http\Controllers\Api\Pharmacy\PharmacyRegistrationController;
use App\Http\Controllers\Api\User\ModulePreferenceController;
use App\Http\Middleware\ApiEnsureFrontendRequestsAreStateful;

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

Route::post('/webhook/deploy', function () {
    include base_path('deploy.php');
});


Route::prefix("password")->as("password.")->group(function () {
    Route::post('/forgot', [PasswordController::class, 'forgotPassword'])->name("forgot");
    Route::post("/reset", [PasswordController::class, "resetPassword"])->name("reset");
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

    Route::post('auth/logout', [LoginController::class, 'logout'])->name('auth.logout');

    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/me', [UserController::class, 'me'])->name('me');
        Route::put('/update', [UserController::class, 'update'])->name('update');
        Route::delete('/delete', [UserController::class, 'delete'])->name('delete');
    });

    Route::prefix('users/{user}/preferences')->group(function () {
        Route::get('/show', [ModulePreferenceController::class, 'show'])->name('show');
        Route::post('/save', [ModulePreferenceController::class, 'save'])->name('save');
        Route::delete('/{preference}/destroy', [ModulePreferenceController::class, 'destroy'])->name('destroy');
    });
    Route::get('/preferences', [ModulePreferenceController::class, 'index']);

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

    Route::prefix('users')->as('users.')->group(function () {
        Route::prefix('patient')->as('patient.')->group(function () {
            Route::post('conversations/ask', [PatientAIAssistanceController::class, 'ask'])->name('conversations.ask');
            Route::get('conversations/get', [PatientAIAssistanceController::class, 'listConversations'])->name('conversations.get');
            Route::get('/conversations/{uuid}/chats', [PatientAIAssistanceController::class, 'getConversationWithChats'])->name('conversations.chats');
        });
        Route::prefix('doctor')->as('doctor.')->group(function () {
            Route::post('conversations/ask', [DoctorAIAssistanceController::class, 'ask'])->name('conversations.ask');
            Route::get('conversations/get', [DoctorAIAssistanceController::class, 'getDoctorConversation'])->name('conversations.get');
            Route::get('/conversations/{uuid}/chats', [DoctorAIAssistanceController::class, 'getConversationWithChats'])->name('conversations.chats');
        });
        Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
            Route::post('conversations/ask', [PharmacyAIAssistanceController::class, 'ask'])->name('conversations.ask');
            Route::get('conversations/get', [PharmacyAIAssistanceController::class, 'getPharmacyConversation'])->name('conversations.get');
            Route::get('/conversations/{uuid}/chats', [PharmacyAIAssistanceController::class, 'getConversationWithChats'])->name('conversations.chats');
        });

        Route::prefix('tracker')->as('tracker.')->group(function () {
            Route::post('/period-cycle/store', [PeriodCycleController::class, 'store'])->name('period-cycle.store');
            Route::get('/period-cycle/show', [PeriodCycleController::class, 'show'])->name('period-cycle.show');
        });
    });
    Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
        Route::put('/update/{id}', [PharmacyRegistrationController::class, 'updatePharmacy'])->name('update');
        Route::get('list', [PharmacyRegistrationController::class, 'list'])->name('list');
        Route::get('/{uuid}/details', [PharmacyRegistrationController::class, 'getPharmacy'])->name('details');
        Route::patch('/{id}/toggle-active', [PharmacyRegistrationController::class, 'toggleActive']);
        // Orders
        Route::get('/orders', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'index']);
        Route::post('/orders', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'store']);
        Route::get('/orders/{id}', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'show']);
        Route::put('/orders/{id}', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'update']);
        Route::delete('/orders/{id}', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'destroy']);
        Route::get('/orders/{id}/export', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'export']);
        Route::get('/emr', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'emr']);

        // Patient Order History
        Route::get('/patient/orders', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'patientOrderHistory']);

        // Medications (Inventory)
        Route::get('/medications', [\App\Http\Controllers\Api\Pharmacy\MedicationController::class, 'index']);
        Route::post('/medications', [\App\Http\Controllers\Api\Pharmacy\MedicationController::class, 'store']);
        Route::get('/medications/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationController::class, 'show']);
        Route::put('/medications/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationController::class, 'update']);
        Route::delete('/medications/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationController::class, 'destroy']);

        // Medication Types
        Route::get('/medication-types', [\App\Http\Controllers\Api\Pharmacy\MedicationTypeController::class, 'index']);
        Route::post('/medication-types', [\App\Http\Controllers\Api\Pharmacy\MedicationTypeController::class, 'store']);
        Route::get('/medication-types/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationTypeController::class, 'show']);
        Route::put('/medication-types/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationTypeController::class, 'update']);
        Route::delete('/medication-types/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationTypeController::class, 'destroy']);

        // Medication Dosages
        Route::get('/medication-dosages', [\App\Http\Controllers\Api\Pharmacy\MedicationDosageController::class, 'index']);
        Route::post('/medication-dosages', [\App\Http\Controllers\Api\Pharmacy\MedicationDosageController::class, 'store']);
        Route::get('/medication-dosages/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationDosageController::class, 'show']);
        Route::put('/medication-dosages/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationDosageController::class, 'update']);
        Route::delete('/medication-dosages/{id}', [\App\Http\Controllers\Api\Pharmacy\MedicationDosageController::class, 'destroy']);
        Route::get('/medications/{medicationId}/dosages', [\App\Http\Controllers\Api\Pharmacy\MedicationDosageController::class, 'getDosagesByMedication']);
        Route::get('/medications/{medicationId}/forms', [\App\Http\Controllers\Api\Pharmacy\MedicationDosageController::class, 'getAvailableForms']);
    });
    Route::get('/statistics', [\App\Http\Controllers\Api\Pharmacy\OrderController::class, 'statistics']);
    Route::get('/activities', [\App\Http\Controllers\Api\Pharmacy\PharmacyActivityController::class, 'index']);

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

        Route::prefix('subscriptions')->as('subscriptions.')->group(function () {
            Route::post('/initialize', [SubscriptionController::class, 'initialize'])->name('initialize');
            Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
            Route::get('/{subscription_code}/details', [SubscriptionController::class, 'getSubscription'])->name('details');
            Route::put('/{subscription_code}/update', [SubscriptionController::class, 'update'])->name('update');
            Route::delete('/{subscription_code}/delete', [SubscriptionController::class, 'delete'])->name('delete');
            Route::get('/list', [SubscriptionController::class, 'list'])->name('list');
        });
        Route::get('/callback', [SubscriptionController::class, 'handleCallback'])->name('callback');
        Route::post('/webhook/paystack', [WebhookController::class, 'handle'])->name('webhook.paystack');
    });
});

Route::prefix('hospital')->as('hospital.')->group(function () {
    Route::post('/register', [HospitalRegistrationController::class, 'registerHospital'])->name('register');
});
Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
    Route::post('/register', [PharmacyRegistrationController::class, 'registerPharmacy'])->name('register');
});
