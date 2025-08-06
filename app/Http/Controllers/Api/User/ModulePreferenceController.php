<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\ModulePreferenceResource;
use App\Services\User\ModulePreferenceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ModulePreferenceController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new ModulePreferenceService;
    }

    public function index()
    {
        try {
            $preferences = $this->service->listPreferences();
            return ApiHelper::validResponse("Module preferences retrieved", ModulePreferenceResource::collection($preferences));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to load module preferences", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function show($userId)
    {
        try {
            $preferences = $this->service->getUserPreferences($userId);
            return ApiHelper::validResponse("User preferences retrieved", ModulePreferenceResource::collection($preferences)->collection->keyBy('name'));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to load user preferences", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function save(Request $request, $userId)
    {
        try {
            $validated = $request->validate([
                'preference_ids' => 'required|array',
                'preference_ids.*' => 'exists:module_preferences,id',
            ]);

            $preferences = $this->service->addPreferences($userId, $validated['preference_ids']);

            return ApiHelper::validResponse("Preferences assigned successfully", ModulePreferenceResource::collection($preferences));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse("Validation failed", ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to assign preferences", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function destroy($userId, $preferenceId)
    {
        try {
            $preferences = $this->service->removePreference($userId, $preferenceId);
            return ApiHelper::validResponse("Preference removed", ModulePreferenceResource::collection($preferences));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to remove preference", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
