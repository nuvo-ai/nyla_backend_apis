<?php

namespace App\Http\Controllers\Api\Hospital\Frontdesk;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\FrontdeskResource;
use App\Services\Hospital\FrontdeskService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;

class FrontdeskController extends Controller
{
    protected $frontdesk_service;
    protected $user_service;


    public function __construct()
    {
        $this->frontdesk_service = new FrontDeskService;
        $this->user_service = new UserService;
    }

    public function index(Request $request)
    {
        try {
            $frontdesks = $this->frontdesk_service->list($request->all());
            return ApiHelper::validResponse("Frontdesk list retrieved successfully", FrontdeskResource::collection($frontdesks));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    private function requestedFrontdeskDataDuringCreation(): array
    {
        return [
            'department',
            'shift',
            'hospital_id',
            'years_of_experience',
        ];
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // User creation data
            $userData = $request->only(['name', 'first_name', 'last_name', 'phone_number', 'email', 'password', 'hospital_id']);
            $userData['portal'] = 'Hospital';
            // dd($request, $userData);
            $user = $this->user_service->create($userData);
            $hospitalUser = $user->hospitalUser;

            // Frontdesk creation data
            $frontdeskData = $request->only($this->requestedFrontdeskDataDuringCreation());
            $frontdeskPayload = array_merge($frontdeskData, [
                'user_id' => $user->id,
                'hospital_id' => $hospitalUser?->hospital_id,
                'hospital_user_id' => $hospitalUser?->id,
            ]);

            $frontdesk = $this->frontdesk_service->save($frontdeskPayload);
           (new Helper)->sendLoginDetails($frontdesk->user, $frontdesk->user->plain_password);
            DB::commit();
            return ApiHelper::validResponse("Frontdesk created successfully", FrontdeskResource::make($frontdesk));
        } catch (ValidationException $e) {
            DB::rollBack();
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $frontdesk)
    {
        DB::beginTransaction();
        try {
            $frontdesk = $this->frontdesk_service->save($request->all(), $frontdesk);
            return ApiHelper::validResponse("Frontdesk updated successfully", FrontdeskResource::make($frontdesk));
            DB::commit();
        } catch (ValidationException $e) {
            DB::rollBack();
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Frontdesk not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
