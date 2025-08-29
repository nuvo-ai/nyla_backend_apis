<?php

use App\Http\Controllers\Api\AI\AssessmentAIAssistanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\AI\DoctorAIAssistanceController;
use App\Http\Controllers\Api\AI\FoodAnalyzerAIAssistanceController;
use App\Http\Controllers\Api\AI\MentalHealthAIAssistanceController;
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
use App\Http\Controllers\Api\Hospital\Prescription\PrescriptionController;
use App\Http\Controllers\Api\Hospital\Tracker\PeriodCycleController;
use App\Http\Controllers\Api\Hospital\VisitNote\VisitNoteController;
use App\Http\Controllers\Api\Pharmacy\PharmacyRegistrationController;
use App\Http\Controllers\Api\User\HealthRecord\HealthRecordController;
use App\Http\Controllers\Api\User\HealthRecord\MedicationReminderController;
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

Route::prefix('billings')->group(function () {
    Route::get('/callback', [SubscriptionController::class, 'handleCallback'])->name('billings.callback');
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

    Route::prefix('admin')->as('admin.')->group(function () {
        Route::get('/app-users', [UserController::class, 'users'])->name('app-users');
        Route::post('/user/restore', [UserController::class, 'restore'])->name('user.restore');
        Route::delete('/user/delete', [UserController::class, 'delete'])->name('user.delete');
    });

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
    Route::prefix('user')->as('user.')->group(function () {
        Route::resource('health-records', HealthRecordController::class);
        Route::resource('medication-reminders', MedicationReminderController::class);
    });
    Route::get('/preferences', [ModulePreferenceController::class, 'index']);

    Route::prefix('hospital')->as('hospital.')->group(function () {

        Route::get('overview/data', [HomeController::class, 'home'])->name('overview.data');


        Route::put('/update/{id}', [HospitalRegistrationController::class, 'updateHospital'])->name('update');
        Route::get('list', [HospitalRegistrationController::class, 'list'])->name('list');
        Route::get('/details', [HospitalRegistrationController::class, 'getHospital'])->name('details');
        Route::patch('{uuid}/aprove', [HospitalRegistrationController::class, 'approve'])->name('approve');
        Route::get('emrs', [PatientController::class, 'hospitalEmrs'])->name('emrs');


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
            Route::delete('/delete/{id}', [AppointmentController::class, 'deleteAppointment'])->name('delete');
        });

        Route::get('doctor/{doctorId}/patients', [DoctorController::class, 'getDoctorPatients'])->name('doctor.patients');

        Route::resource('patients', PatientController::class);
        Route::resource('doctors', DoctorController::class);
        Route::resource('frontdesks', FrontdeskController::class);
        Route::resource('visit-notes', VisitNoteController::class);
        Route::resource('prescriptions', PrescriptionController::class);

        Route::prefix('doctor')->as('doctor.')->group(function () {
            Route::get('/dashboard-data', [DoctorController::class, 'getDashboardData'])->name('dashboard-data');
        });

        Route::get('frontdesk/dashboard-stats', [FrontdeskController::class, 'dashboardStats'])->name('frontdesk.dashboard-stats');

        Route::post('prescription/send-to-frontdesk/{prescription}', [PrescriptionController::class, 'sendToFrontdesk'])->name('prescription.send-to-frontdesk');

        Route::patch('patient/discharge/{patient}', [PatientController::class, 'discharge'])->name('patient.discharge');
        Route::patch('patient/assign-doctor/{patient}', [PatientController::class, 'assign'])->name('patient.assign-doctor');
        Route::get('patients-stat', [PatientController::class, 'stat'])->name('patients-stat');
        Route::patch('patient/update-status/{patient}', [PatientController::class, 'updateStatus'])->name('patient.update-status');

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

        Route::prefix('food-analyzer')->as('food-analyzer.')->group(function () {
            Route::post('conversations/ask', [FoodAnalyzerAIAssistanceController::class, 'ask'])->name('conversations.ask');
            Route::get('conversations/get', [FoodAnalyzerAIAssistanceController::class, 'getPharmacyConversation'])->name('conversations.get');
            Route::get('/conversations/{uuid}/chats', [FoodAnalyzerAIAssistanceController::class, 'getConversationWithChats'])->name('conversations.chats');
        });

        Route::prefix('mental-health')->as('mental-health.')->group(function () {
            Route::post('conversations/ask', [MentalHealthAIAssistanceController::class, 'ask'])->name('conversations.ask');
        });
        Route::prefix('assessment')->as('assessment.')->group(function () {
            Route::post('conversations/ask', [AssessmentAIAssistanceController::class, 'ask'])->name('conversations.ask');
        });


        Route::prefix('tracker')->as('tracker.')->group(function () {
            Route::post('/period-cycle/store', [PeriodCycleController::class, 'store'])->name('period-cycle.store');
            Route::get('/period-cycle/show', [PeriodCycleController::class, 'show'])->name('period-cycle.show');
        });

        Route::get('user/hospital', [UserController::class, 'getUserHospital'])->name('user.hospital');
    });
    Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
        Route::put('/update/{id}', [PharmacyRegistrationController::class, 'updatePharmacy'])->name('update');
        Route::get('list', [PharmacyRegistrationController::class, 'list'])->name('list');
        Route::get('/{uuid}/details', [PharmacyRegistrationController::class, 'getPharmacy'])->name('details');
        Route::patch('/{id}/toggle-active', [PharmacyRegistrationController::class, 'toggleActive']);
        Route::patch('/{uuid}/approve', [PharmacyRegistrationController::class, 'approve'])->name('approve');

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
        Route::get('/medication-types/{medicationTypeId}/medications', [\App\Http\Controllers\Api\Pharmacy\MedicationController::class, 'getByMedicationType']);

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
        Route::get('plans/hospital', [PlanController::class, 'hospitalPlan'])->name('plans.hospital');
        Route::get('plans/pharmacy', [PlanController::class, 'pharmacyPlan'])->name('plans.pharmacy');

        Route::prefix('plan/{plan_id}/features')->as('plan.features.')->group(function () {
            Route::post('/create', [PlanFeatureController::class, 'create'])->name('create');
            Route::put('/update', [PlanFeatureController::class, 'update'])->name('update');
            Route::get('/list', [PlanFeatureController::class, 'list'])->name('list');
            Route::get('/{feature_id}/details', [PlanFeatureController::class, 'getFeature'])->name('details');
        });

        Route::prefix('subscriptions')->as('subscriptions.')->group(function () {
            Route::post('/initialize', [SubscriptionController::class, 'initialize'])->name('initialize');
            Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
            Route::get('/{subscription_code}/details', [SubscriptionController::class, 'getSubscription'])->name('details');
            Route::put('/{subscription_code}/update', [SubscriptionController::class, 'update'])->name('update');
            Route::delete('/{subscription_code}/delete', [SubscriptionController::class, 'delete'])->name('delete');
            Route::get('/list', [SubscriptionController::class, 'list'])->name('list');
            Route::get('/current', [SubscriptionController::class, 'current'])->name('current');
        });
        // Route::get('/callback', [SubscriptionController::class, 'handleCallback'])->name('callback');
        Route::post('/webhook/paystack', [WebhookController::class, 'handle'])->name('webhook.paystack');
    });
});

Route::prefix('hospital')->as('hospital.')->group(function () {
    Route::post('/register', [HospitalRegistrationController::class, 'registerHospital'])->name('register');
});
Route::prefix('pharmacy')->as('pharmacy.')->group(function () {
    Route::post('/register', [PharmacyRegistrationController::class, 'registerPharmacy'])->name('register');
});
