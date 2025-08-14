<?php

namespace App\Http\Controllers\Api\Hospital\Patient;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\PatientResource;
use App\Mail\SendUserLoginDetailsMail;
use App\Models\User\User;
use App\Services\Hospital\Patient\PatientService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    protected $patient_service;
    public $user;

    public function __construct()
    {
        $this->patient_service = new PatientService;
        $this->user = new UserService;
    }

    public function index(Request $request)
    {
        try {
            $patients = $this->patient_service->listPatients($request->all());
            return ApiHelper::validResponse("Patient list retrieved successfully", PatientResource::collection($patients));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $userData = $this->requestedUserDataDuringPatientRegistration($request);
            $user = $this->user->create($userData); // Now returns User directly

            $this->sendLoginDetails($user->id, $userData);

            $patientData = $request->except(array_keys($userData));
            $patientData['user_id'] = $user->id;

            $patient = $this->patient_service->save($patientData);

            return ApiHelper::validResponse("Patient created successfully", PatientResource::make($patient));
        } catch (ValidationException $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }


    public function show($patient)
    {
        try {
            $patient = $this->patient_service::getById($patient);
            return ApiHelper::validResponse("Patient retrieved successfully", PatientResource::make($patient));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Patient not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $patient)
    {
        try {
            $existingPatient = $this->patient_service->getById($patient);
            $data = $request->all();
            $data['user_id'] = $existingPatient->user_id;

            $updatedPatient = $this->patient_service->save($data, $patient);
            return ApiHelper::validResponse("Patient updated successfully", PatientResource::make($updatedPatient));
        } catch (ValidationException $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Patient not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }



    public function destroy($patient)
    {
        try {
            $patient = $this->patient_service::getById($patient);
            $patient->delete();
            return ApiHelper::validResponse("Patient deleted successfully");
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Patient not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function discharge(Request $request, $patient)
    {
        try {
            $data = $this->patient_service->discharge($request, $patient);
            return ApiHelper::validResponse("Patient discharged successfully", $data);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Patient not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
    public function assign(Request $request, $patient)
    {
        try {
            $data = $this->patient_service->assign($request, $patient);
            $get_patient = $this->patient_service->getById($patient);
            $patient_name = $get_patient->user->full_name;
            return ApiHelper::validResponse("Doctor assigned to " . $patient_name .   " successfully", $data);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Doctor not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
    public function stat()
    {
        try {
            $patients = $this->patient_service->stat();
            return ApiHelper::validResponse("Patients stat retrieved successfully", $patients);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
    private function requestedUserDataDuringPatientRegistration(Request $request): array
    {
        $generated_password = $this->generateRandomPasswordDuringHospitalRegistration();

        $request->merge(['generated_password' => $generated_password]);

        return [
            'email'       => $request->input('email'),
            'phone'       => $request->input('phone'),
            'password'    => $generated_password,
            'name'        => $request->input('name') ?? $request->input('first_name') . ' ' . $request->input('last_name'),
            'first_name'        => $request->input('first_name'),
            'last_name'        => $request->input('last_name'),
            'gender'      => $request->input('gender'),
            'date_of_birth' => $request->input('date_of_birth'),
        ];
    }


    private function generateRandomPasswordDuringHospitalRegistration(int $length = 10): string
    {
        return Str::random($length);
    }

    private function sendLoginDetails($user_id, array $data)
    {
        try {
            $user = User::findOrFail($user_id);
            $random_password = $data['password'] ?? Str::random(10);
            $user->password = Hash::make($random_password);
            $user->save();
            Mail::to($user->email)->send(new SendUserLoginDetailsMail($user, $random_password));
            return $user->toArray();
        } catch (\Exception $e) {
            return ['error_message' => 'An error occurred while sending login details to user.'];
        }
    }
}
