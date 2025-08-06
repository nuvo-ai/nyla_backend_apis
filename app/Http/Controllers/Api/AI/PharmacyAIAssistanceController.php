<?php

namespace App\Http\Controllers\Api\AI;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AI\V1\PatientAIAssistanceResource;
use App\Http\Resources\User\UserResource;
use App\Models\General\Conversation;
use App\Models\User\User;
use App\Services\AI\PharmacyAIAssistanceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PharmacyAIAssistanceController extends Controller
{
    public $pharmacy_ai_assistance_service;
    public function __construct()
    {
        $this->pharmacy_ai_assistance_service = new PharmacyAIAssistanceService();
    }
    public function ask(Request $request)
    {
        try {
            $result = $this->pharmacy_ai_assistance_service->createConversation($request);

            $conversation = $result['conversation'];
            $user = $conversation->user;
            $chats = PatientAIAssistanceResource::collection($conversation->chats()->get());

            return ApiHelper::validResponse('Conversation created successfully', [
                'user' => new UserResource($user),
                'prompt' => $result['prompt'],
                'response' => $result['response'],
                'chats' => $chats,
            ]);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function getPharmacyConversation()
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
