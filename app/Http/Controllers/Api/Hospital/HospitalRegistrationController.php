<?php

namespace App\Http\Controllers\Api\Hospital;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\HospitalRegistrationResource;
use App\Models\Hospital\Hospital;
use App\Services\Auth\SanctumService;
use App\Services\Billing\Subscription\SubscriptionService;
use App\Services\Hospital\HospitalService;
use App\Services\User\UserService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class HospitalRegistrationController extends Controller
{
    public $hospital_service;
    public $user;
    public $subscription_service;
    public function __construct()
    {
        $this->hospital_service = new HospitalService;
        $this->user = new UserService;
        $this->subscription_service = new SubscriptionService;
    }

    public function list(Request $request)
    {
        try {
            // $filters = $request->only(['status', 'type', 'search']);
            $hospitals = $this->hospital_service->listHospitals($request->all());
            return ApiHelper::validResponse("Hospital retrieved successfully", HospitalRegistrationResource::collection($hospitals));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function getHospital()
    {
        try {
            $hospitals = $this->hospital_service->getHospital();
            return ApiHelper::validResponse("Hospital retrieved successfully", HospitalRegistrationResource::make($hospitals));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Hospital with the specified identifier was not found in the system", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function registerHospital(Request $request)
    {
        DB::beginTransaction();

        try {
            // Create User
            $userData = $this->requestedUserDataduringHospitalRegistration($request);
            $user = $this->user->create($userData);

            // Create Hospital
            $hospitalData = $request->except([
                'user_name',
                'user_email',
                'user_phone',
                'portal',
                'password',
                'generated_password',
                'billing_email',
                'payment_method',
                'plan_id',
                'platform'
            ]);

            $hospitalData['user_id'] = $user->id;
            $hospital = $this->hospital_service->createHospital($hospitalData);

            // Generate Auth Token
            $authToken = $user->createToken('hospital_onboarding_token')->plainTextToken;

            $responseData = [
                'hospital' => new HospitalRegistrationResource($hospital),
                'auth_token' => $authToken,
            ];

            // Handle Free Trial
            if ($request->payment_method === 'free_trial') {
                $subscription = $this->subscription_service->createSubscription(
                    $user,
                    $request->input('plan_id'),
                    [],
                    true // trial mode
                );

                if (!$subscription) {
                    throw new Exception("Free trial subscription creation failed.");
                }

                $responseData['free_trial'] = true;
                $message = "Hospital created successfully (Free Trial)";
            }
            // Handle Paid Subscription
            else {
                $subscriptionData = $this->requestedSubscriptionDataDuringHospitalRegistration($request);
                $init = $this->subscription_service->initializePayment($user, $subscriptionData);

                // Validate response before commit
                if (empty($init['authorization_url']) || empty($init['reference'])) {
                    throw new Exception("Payment initialization failed.");
                }

                $responseData = array_merge($responseData, [
                    'free_trial' => false,
                    'authorization_url' => $init['authorization_url'],
                    'access_code' => $init['access_code'] ?? null,
                    'reference' => $init['reference'],
                ]);

                $message = "Hospital created successfully";
            }

            // Commit only when ALL succeeds
            DB::commit();
            return ApiHelper::validResponse($message, $responseData);
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiHelper::inputErrorResponse($e->getMessage(), ApiConstants::VALIDATION_ERR_CODE);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("An error occurred during hospital registration.", 500, null, $e);
        }
    }





    public function updateHospital(Request $request, $id)
    {
        try {
            $hospital = $this->hospital_service->updateHospital($request->all(), $id);

            return ApiHelper::validResponse("Hospital updated successfully", HospitalRegistrationResource::make($hospital));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->serverErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    private function requestedUserDataduringHospitalRegistration(Request $request)
    {
        $generated_password = $this->generateRandomPasswordDuringHospitalRegistration();

        $request->merge(['generated_password' => $generated_password]);
        return [
            'email'       => $request->input('user_email'),
            'phone' => $request->input('user_phone'),
            'portal'      => $request->input('portal'),
            'role'        => $request->input('role'),
            'password'    => $generated_password,
            'name'        => $request->input('user_name'),
        ];
    }

    private function requestedSubscriptionDataDuringHospitalRegistration(Request $request)
    {
        return [
            'billing_email' => $request->input('billing_email'),
            'payment_method' => $request->input('payment_method'),
            'plan_id' => $request->input('plan_id'),
            'portal' => $request->input('portal', 'pharmacy'),
            'platform' => $request->input('platform', 'web'),
        ];
    }

    private function generateRandomPasswordDuringHospitalRegistration(int $length = 10): string
    {
        return Str::random($length);
    }

    public function approve(string $uuid)
    {
        DB::beginTransaction();
        try {
            $hospital = Hospital::where('uuid', $uuid)->firstOrFail();

            $approvedHospital = $this->hospital_service->approveHospital($hospital);

            DB::commit();

            return ApiHelper::validResponse(
                "Hospital approved successfully",
                new HospitalRegistrationResource($approvedHospital)
            );
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse(
                "Hospital not found",
                ApiConstants::NOT_FOUND_ERR_CODE,
                null,
                $e
            );
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse(
                $this->serverErrorMessage,
                ApiConstants::SERVER_ERR_CODE,
                null,
                $e
            );
        }
    }

    public function reject(string $uuid)
    {
        DB::beginTransaction();
        try {
            $hospital = Hospital::where('uuid', $uuid)->firstOrFail();

            $rejectedHospital = $this->hospital_service->approveHospital($hospital);

            DB::commit();

            return ApiHelper::validResponse(
                "Hospital rejected successfully",
                new HospitalRegistrationResource($rejectedHospital)
            );
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse(
                "Hospital not found",
                ApiConstants::NOT_FOUND_ERR_CODE,
                null,
                $e
            );
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse(
                $this->serverErrorMessage,
                ApiConstants::SERVER_ERR_CODE,
                null,
                $e
            );
        }
    }
}
