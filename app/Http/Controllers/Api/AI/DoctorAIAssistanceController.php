<?php

namespace App\Http\Controllers\Api\AI;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AI\V1\DoctorAIAssistanceResource;
use App\Http\Resources\AI\V1\PatientAIAssistanceResource;
use App\Http\Resources\User\UserResource;
use App\Models\General\Conversation;
use App\Models\Hospital\Doctor;
use App\Models\User\User;
use App\Services\AI\DoctorAIAssistanceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DoctorAIAssistanceController extends Controller
{
    public $doctor_ai_assistance_service;
    public function __construct()
    {
        $this->doctor_ai_assistance_service = new DoctorAIAssistanceService();
    }
    public function ask(Request $request)
    {
        try {
            $result = $this->doctor_ai_assistance_service->createConversation($request);

            $conversation = $result['conversation'];
            $chats = DoctorAIAssistanceResource::collection($conversation->chats()->get());

            return ApiHelper::validResponse('Conversation created successfully', [
                'prompt' => $result['prompt'],
                'response' => $result['response'],
                'chats' => $chats,
            ]);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            $message = $this->serverErrorMessage;
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function getDoctorConversation()
    {
        try {
            $user = User::getAuthenticatedUser();
            $conversations = Conversation::with('chats')
                ->where('user_id', $user->id)->latest()
                ->get();

            if ($conversations->isEmpty()) {
                return [];
            }
            return ApiHelper::validResponse('User conversations fetched successfully', [
                'conversations' => $conversations,
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, null, $e);
        }

    }

     public function getConversationWithChats($uuid)
    {
        try {
            $user = User::getAuthenticatedUser();
            $doctor = $user->doctor;
            if (!$doctor) {
                throw new Exception('Could not get conversation chats for this user.');
            }
            $conversation = Conversation::with('chats')
                ->where('user_id', $user->id)
                ->where('uuid', $uuid)
                ->firstOrFail();

            return ApiHelper::validResponse('Conversation details fetched successfully', [
                'conversation' => $conversation,
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
